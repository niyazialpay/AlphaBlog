<script setup>
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { formatDateTime } from '../../composables/useFormat';
import Pagination from '../../components/Pagination.vue';

/*
 * panel/contact-messages.blade.php karşılığı.
 *
 * Tarihler ISO-8601 geliyor ve istemcide app timezone'una göre biçimlenir
 * (sunucu daha önce 'd M. Y D. H:i:s' ile basıyordu).
 */
defineProps({
  messages: { type: Object, required: true },
});

usePageHeader(__('contact.messages'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('contact.messages') },
]);
</script>

<template>
  <Head :title="__('contact.messages')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="p-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] border-collapse text-[12.5px]">
          <thead>
            <tr class="bg-p-panel2 text-left text-[11px] uppercase tracking-[.06em] text-p-ink3">
              <th class="px-3.5 py-2.5 font-semibold">{{ __('contact.name_surname') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('contact.email') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('contact.subject') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('contact.message') }}</th>
              <th class="whitespace-nowrap px-3.5 py-2.5 font-semibold">
                {{ __('general.created_at') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="message in messages.data"
              :key="message.id"
              class="border-t border-p-line2 align-top hover:bg-p-panel2"
            >
              <td class="px-3.5 py-2.5">
                <div class="font-semibold">{{ message.name }}</div>
                <div v-if="message.language" class="mt-0.5 text-[11px] text-p-ink3">
                  {{ __('language.language') }}: {{ message.language.toUpperCase() }}
                </div>
                <div v-if="message.ip" class="text-[11px] text-p-ink3">
                  {{ __('sessions.ip_address') }}: {{ message.ip }}
                </div>
              </td>
              <td class="px-3.5 py-2.5">
                <a :href="`mailto:${message.email}`">{{ message.email }}</a>
              </td>
              <td class="px-3.5 py-2.5 text-p-ink2">{{ message.subject }}</td>
              <!-- Blade'de nl2br(e(...)) vardi; burada metin olarak basilip
                   satir sonlari CSS ile korunuyor: v-html yok, XSS yuzeyi yok. -->
              <td class="whitespace-pre-wrap px-3.5 py-2.5 text-p-ink2">{{ message.message }}</td>
              <td class="whitespace-nowrap px-3.5 py-2.5 text-p-ink3 tabular-nums">
                {{ formatDateTime(message.createdAt) }}
              </td>
            </tr>
            <tr v-if="!messages.data.length">
              <td colspan="5" class="px-3.5 py-10 text-center text-p-ink3">
                {{ __('contact.no_messages') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination :links="messages.links" :meta="messages" />
  </div>
</template>
