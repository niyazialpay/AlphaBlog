<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { marked } from 'marked';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { pushToast } from '../../composables/useToast';

/*
 * panel/Chat/index.blade.php karşılığı.
 *
 * `AiChatbotController` zaten temiz bir JSON API; yalnız `index()` Inertia'ya
 * döndü (R1: veri uçları JSON kalır). Bu yüzden konuşma listesi/mesaj gönderimi
 * burada da axios ile çağrılır.
 *
 * KRİTİK: markdown render sırası `marked.parse(escapeHtml(content))` — önce
 * kaçır, sonra ayrıştır. Ters çevrilirse `v-html` üzerinden XSS açılır.
 *
 * Sağlayıcı/model tercihi localStorage'da tutuluyordu; aynı anahtarlar korunur.
 */
const props = defineProps({
  chatProviders: { type: [Object, Array], default: () => ({}) },
  conversations: { type: Array, default: () => [] },
  initialConversation: { type: Object, default: null },
  initialMessages: { type: Array, default: () => [] },
  defaultProvider: { type: String, default: '' },
  defaultModel: { type: String, default: '' },
  hasAvailableProvider: { type: Boolean, default: false },
});

usePageHeader(__('chatbot.title'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('chatbot.chatbot') },
]);

const PROVIDER_KEY = 'panel.chat.provider';
const MODEL_KEY = 'panel.chat.model.';

function readStorage(key) {
  try {
    return window.localStorage.getItem(key);
  } catch {
    return null;
  }
}

function writeStorage(key, value) {
  try {
    window.localStorage.setItem(key, value);
  } catch {
    /* özel sekme / site verisi kapalı: tercih hatırlanmaz, ekran çalışır. */
  }
}

const conversations = ref([...props.conversations]);
const conversation = ref(props.initialConversation);
const messages = ref([...props.initialMessages]);
const draft = ref('');
const sending = ref(false);
const listEl = ref(null);

const provider = ref(readStorage(PROVIDER_KEY) || props.defaultProvider);
const providerEntries = computed(() => Object.entries(props.chatProviders || {}));

if (!props.chatProviders?.[provider.value]) {
  provider.value = props.defaultProvider;
}

const models = computed(() => props.chatProviders?.[provider.value]?.models || []);

const model = ref(
  readStorage(MODEL_KEY + provider.value) || props.defaultModel || models.value[0]?.name || '',
);

watch(provider, (value) => {
  writeStorage(PROVIDER_KEY, value);

  const stored = readStorage(MODEL_KEY + value);
  const available = models.value.map((entry) => entry.name);

  model.value = available.includes(stored) ? stored : available[0] || '';
});

watch(model, (value) => {
  if (value) {
    writeStorage(MODEL_KEY + provider.value, value);
  }
});

function escapeHtml(content) {
  return String(content ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function render(message) {
  if (!message.content) {
    return '';
  }

  if (message.role === 'user') {
    return escapeHtml(message.content).replace(/\n/g, '<br>');
  }

  // Kaçır → ayrıştır. Sıra değişmemeli.
  return marked.parse(escapeHtml(message.content));
}

/* Blade'deki "Yazıyor… / Hesaplanıyor…" döngüsü. */
const pendingLabel = ref('');
let pendingTimer = null;

function startPending() {
  const statuses = [__('chatbot.typing'), __('chatbot.calculating')];
  let index = 0;
  let dots = 0;

  pendingLabel.value = statuses[0];

  pendingTimer = window.setInterval(() => {
    dots = (dots + 1) % 4;

    if (dots === 0) {
      index = (index + 1) % statuses.length;
    }

    pendingLabel.value = statuses[index] + '.'.repeat(dots);
  }, 400);
}

function stopPending() {
  window.clearInterval(pendingTimer);
  pendingTimer = null;
  pendingLabel.value = '';
}

onBeforeUnmount(stopPending);

async function scrollToEnd() {
  await nextTick();

  if (listEl.value) {
    listEl.value.scrollTop = listEl.value.scrollHeight;
  }
}

onMounted(scrollToEnd);

async function open(item) {
  try {
    const { data } = await axios.get(route('chatbot.conversation', item.id));

    conversation.value = data.conversation;
    messages.value = data.messages;
    await scrollToEnd();
  } catch (error) {
    pushToast(error.response?.data?.message || __('chatbot.errors.request_failed'), 'error');
  }
}

function startNew() {
  conversation.value = null;
  messages.value = [];
  draft.value = '';
}

async function send() {
  const content = draft.value.trim();

  if (!content || sending.value || !props.hasAvailableProvider) {
    return;
  }

  sending.value = true;
  draft.value = '';
  messages.value = [...messages.value, { id: `local-${messages.value.length}`, role: 'user', content }];
  await scrollToEnd();
  startPending();

  try {
    const { data } = await axios.post(route('chatbot.message'), {
      message: content,
      provider: provider.value,
      model: model.value,
      conversation_id: conversation.value?.id || null,
    });

    conversation.value = data.conversation;
    messages.value = data.messages;

    const index = conversations.value.findIndex((item) => item.id === data.conversation.id);

    if (index === -1) {
      conversations.value = [data.conversation, ...conversations.value];
    } else {
      conversations.value.splice(index, 1, data.conversation);
      conversations.value = [...conversations.value].sort(
        (a, b) => new Date(b.updated_at) - new Date(a.updated_at),
      );
    }
  } catch (error) {
    draft.value = content;
    messages.value = messages.value.slice(0, -1);
    pushToast(error.response?.data?.message || __('chatbot.errors.request_failed'), 'error');
  } finally {
    stopPending();
    sending.value = false;
    await scrollToEnd();
  }
}

function onKeydown(event) {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault();
    send();
  }
}
</script>

<template>
  <Head :title="__('chatbot.title')" />

  <div class="grid gap-3.5 p-[22px] lg:grid-cols-[280px_minmax(0,1fr)]">
    <div class="p-card flex h-max flex-col overflow-hidden">
      <button class="p-btn-primary m-3 justify-center" @click="startNew">
        <i class="fa-solid fa-plus text-xs"></i> {{ __('chatbot.new_conversation') }}
      </button>

      <div class="border-t border-p-line2 px-3 py-2 text-[11px] uppercase tracking-[.06em] text-p-ink3">
        {{ __('chatbot.previous_conversations') }}
      </div>

      <div class="max-h-[60vh] divide-y divide-p-line2 overflow-auto">
        <button
          v-for="item in conversations"
          :key="item.id"
          class="w-full px-3 py-2 text-left hover:bg-p-panel2"
          :class="conversation?.id === item.id && 'bg-p-soft'"
          @click="open(item)"
        >
          <div class="truncate text-[12.5px] font-semibold">{{ item.title }}</div>
          <div class="text-[10.5px] text-p-ink3">{{ item.updated_at_human }}</div>
        </button>

        <div v-if="!conversations.length" class="px-3 py-6 text-center text-[11.5px] text-p-ink3">
          {{ __('chatbot.select_conversation') }}
        </div>
      </div>
    </div>

    <div class="p-card flex h-[72vh] flex-col overflow-hidden">
      <div class="flex flex-wrap items-center gap-2 border-b border-p-line2 px-3 py-2.5">
        <select v-model="provider" class="p-input w-auto" :disabled="!hasAvailableProvider">
          <option v-for="[key, entry] in providerEntries" :key="key" :value="key">
            {{ entry.label || key }}
          </option>
        </select>
        <select v-model="model" class="p-input w-auto" :disabled="!hasAvailableProvider">
          <option v-for="entry in models" :key="entry.name" :value="entry.name">
            {{ entry.name }}
          </option>
        </select>
      </div>

      <div
        v-if="!hasAvailableProvider"
        class="border-b border-p-line2 bg-p-warn/10 px-3 py-2 text-[12px] text-p-ink2"
      >
        {{ __('chatbot.no_provider_configured') }}
      </div>

      <div ref="listEl" class="flex-1 space-y-3 overflow-auto p-4">
        <div
          v-for="message in messages"
          :key="message.id"
          class="flex"
          :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
        >
          <div
            class="max-w-[88%] rounded-2xl px-3.5 py-2.5 text-[12.5px] leading-relaxed"
            :class="
              message.role === 'user'
                ? 'bg-p-accent text-white'
                : 'border border-p-line bg-p-panel2 text-p-ink'
            "
          >
            <div
              class="mb-1 text-[10.5px]"
              :class="message.role === 'user' ? 'text-white/70' : 'text-p-ink3'"
            >
              {{ message.role === 'user' ? '' : __('chatbot.assistant') }}
              <span v-if="message.model" class="ml-1">· {{ message.model }}</span>
            </div>
            <!-- Kaynak escapeHtml'den geçiyor; marked yalnız kaçırılmış metni ayrıştırıyor. -->
            <div class="prose prose-sm max-w-none dark:prose-invert" v-html="render(message)"></div>
          </div>
        </div>

        <div v-if="pendingLabel" class="flex justify-start">
          <div class="rounded-2xl border border-p-line bg-p-panel2 px-3.5 py-2.5 text-[12.5px]">
            <div class="mb-1 text-[10.5px] text-p-ink3">{{ __('chatbot.assistant') }}</div>
            {{ pendingLabel }}
          </div>
        </div>

        <div v-if="!messages.length && !pendingLabel" class="py-10 text-center text-[12.5px] text-p-ink3">
          {{ __('chatbot.empty_conversation') }}
        </div>
      </div>

      <div class="flex items-end gap-2 border-t border-p-line2 p-3">
        <textarea
          v-model="draft"
          class="p-textarea h-[64px] flex-1 resize-none"
          :placeholder="__('chatbot.type_your_message')"
          :disabled="!hasAvailableProvider"
          @keydown="onKeydown"
        ></textarea>
        <button class="p-btn-primary" :disabled="sending || !hasAvailableProvider" @click="send">
          <i class="fa-solid fa-paper-plane text-xs"></i> {{ __('chatbot.send') }}
        </button>
      </div>
    </div>
  </div>
</template>
