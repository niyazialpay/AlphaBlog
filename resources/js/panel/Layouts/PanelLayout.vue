<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useTheme } from '../composables/useTheme';
import { __ } from '../composables/useLang';
import CommandPalette from '../components/CommandPalette.vue';
import NotificationBell from '../components/NotificationBell.vue';
import FlashToast from '../components/FlashToast.vue';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import { pageHeader } from '../composables/usePageHeader';
import { pushToast } from '../composables/useToast';

const page = usePage();
const { theme, toggleTheme } = useTheme();
const cacheClearing = ref(false);
const logoutForm = ref(null);
const logoutConfirm = ref(null);

/*
 * Cikis onay ister: dugme kullanici blogunun hemen yaninda ve yanlislikla
 * tiklanabiliyordu.
 *
 * Onaydan sonra GERCEK form gonderilir, `router.post` DEGIL: `admin.logout`
 * duz Blade olan /login'e 302 doner, Inertia o yoniendirmeyi izleyip HTML alir
 * ve "plain HTML response" hatasi verir.
 */
async function confirmLogout() {
  const approved = await logoutConfirm.value?.ask({
    title: __('user.logout'),
    body: __('user.logout_confirm'),
    icon: 'fa-solid fa-right-from-bracket',
    confirmLabel: __('user.logout'),
  });

  if (approved) {
    logoutForm.value?.submit();
  }
}

const paletteOpen = ref(false);
const languageOpen = ref(false);
const activeSection = ref(null);
const mobileNavOpen = ref(false);

// Çıkış gerçek bir form POST'u; token kabuktaki meta etiketinden okunur.
const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content || '';

/*
 * Menü SUNUCUDAN gelir (App\Support\Panel\PanelMenu): çekirdek bölümler
 * config/panel_menu.php'den, modül bölümleri her modülün kendi yayınından.
 * Böylece yetkiler tek yerde (@can'lerle birebir) değerlendirilir ve modül
 * menüleri istemciye gömülü liste olmadan görünür.
 */
const sections = computed(() => page.props.menu || []);

/*
 * Panel temasina gore logo. Site iki logo tasiyor (aydinlik/karanlik); sunucu
 * ikisini de gonderiyor, secim burada yapiliyor cunku tema istemcide degisiyor.
 * Yalnizca biri yuklenmisse o kullanilir.
 */
const brandLogo = computed(() => {
  const logo = page.props.siteLogo || {};

  return (theme.value === 'dark' ? logo.dark || logo.light : logo.light || logo.dark) || null;
});

const current = computed(
  () =>
    sections.value.find((s) => s.key === activeSection.value) ||
    sections.value.find((s) => s.items.some((i) => i.active)) ||
    sections.value[0] || { key: null, label: '', items: [] },
);

/** Sidebar içindeki alt başlıklar (ör. Cloudflare) için gruplanmış liste. */
const grouped = computed(() => {
  const out = [];

  for (const item of current.value.items) {
    const last = out[out.length - 1];

    if (item.group && (!last || last.group !== item.group)) {
      out.push({ group: item.group, items: [item] });
    } else if (item.group && last) {
      last.items.push(item);
    } else {
      out.push({ group: null, items: [item] });
    }
  }

  return out;
});

function open(item) {
  mobileNavOpen.value = false;

  if (item.action === 'clear-cache') {
    return clearCache();
  }

  if (!item.url) {
    return;
  }

  /*
   * `inertia: false` => ekran hâlâ eski AdminLTE kabuğunda. Inertia ziyareti
   * yapılamaz: yanıtta X-Inertia başlığı yok, client hata verir. Tam sayfa yükle.
   */
  if (item.inertia) {
    router.visit(item.url);
  } else {
    window.location.href = item.url;
  }
}

/**
 * Önbellek temizleme JSON döndürüyor (backend değişmiyor), bu yüzden Inertia
 * ziyareti değil axios çağrısı yapılır.
 *
 * POST alias'ı kullanılır: eski uç GET ve durum değiştiriyor; Inertia v2
 * prefetch'i bir gezinme bağlantısında hover'da tetikleyebilirdi.
 */
function clearCache() {
  if (cacheClearing.value) {
    return;
  }

  cacheClearing.value = true;

  axios
    .post(route('admin.clear_cache.post'))
    .then(({ data }) => {
      /*
       * Uc JSON donuyor ve oturuma HICBIR flash yazmiyor; onceki hali
       * `router.reload({ only: ['flash'] })` yapiyordu, yani bos bir flash
       * okuyup hicbir sey gostermiyordu. Eski ekran burada SweetAlert
       * basiyordu. Yaniti dogrudan toast'a veriyoruz.
       */
      pushToast(
        data?.message || __('cache.cache_cleared'),
        data?.status === 'error' ? 'error' : 'success',
      );
    })
    .catch((error) => {
      pushToast(
        error?.response?.data?.message || __('cache.cache_not_cleared'),
        'error',
      );
    })
    .finally(() => {
      cacheClearing.value = false;
    });
}

/**
 * Breadcrumb bağlantısının URL'i.
 *
 * İki şey yapar:
 *  1. `crumb.params` destekler. Bazı panel route'ları ZORUNLU segment taşıyor
 *     (ör. `admin.posts` → `{panel}/{type}`); parametresiz `route()` çağrısı
 *     ziggy-js'te "'type' parameter is required" fırlatır.
 *  2. Fırlatmayı yutar. Breadcrumb render'ı sırasında atılan bir hata Vue'nun
 *     renderComponentRoot'u tarafından yakalanır ve TÜM PanelLayout alt ağacı
 *     boş bir Comment düğümüne düşer — yani tek bir eksik parametre koca ekranı
 *     öldürür. Çözülemeyen crumb bağlantı yerine düz metin olur.
 */
function crumbHref(crumb) {
  if (!crumb.route) {
    return null;
  }

  try {
    return route(crumb.route, crumb.params ?? undefined);
  } catch (error) {
    console.error('[panel] breadcrumb route cozulemedi:', crumb.route, error);

    return null;
  }
}

function onKey(e) {
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    paletteOpen.value = !paletteOpen.value;
  }

  if (e.key === 'Escape') {
    languageOpen.value = false;
    paletteOpen.value = false;
    mobileNavOpen.value = false;
  }
}

onMounted(() => window.addEventListener('keydown', onKey));
onUnmounted(() => window.removeEventListener('keydown', onKey));
</script>

<template>
  <div class="flex min-h-screen bg-p-bg">
    <!-- İkon rail -->
    <nav
      class="sticky top-0 z-20 flex h-screen w-[68px] shrink-0 flex-col items-center gap-1.5 bg-p-rail py-3.5"
    >
      <!--
        Ray'da marka isareti YOK: bolum ikonlari en uste alindi. Logo yandaki
        genis kolonda, tema moduna gore.
      -->
      <button
        v-for="s in sections"
        :key="s.key"
        :title="s.label"
        class="grid h-11 w-11 place-items-center rounded-xl border-0 text-base transition-colors"
        :class="
          current.key === s.key
            ? 'bg-white/10 text-white'
            : 'bg-transparent text-p-railink hover:bg-white/10'
        "
        @click="
          activeSection = s.key;
          mobileNavOpen = true;
        "
      >
        <i :class="s.icon"></i>
      </button>

      <div class="flex-1"></div>

      <button
        :title="__('general.theme')"
        class="grid h-11 w-11 place-items-center rounded-xl border-0 bg-transparent text-p-railink hover:bg-white/10 hover:text-white"
        @click="toggleTheme"
      >
        <i :class="theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
      </button>
      <button
        title="⌘K"
        class="grid h-11 w-11 place-items-center rounded-xl border-0 bg-transparent text-p-railink hover:bg-white/10 hover:text-white"
        @click="paletteOpen = true"
      >
        <i class="fa-solid fa-terminal"></i>
      </button>
    </nav>

    <!-- Bağlam paneli -->
    <aside
      class="sticky top-0 z-20 h-screen w-[252px] shrink-0 flex-col border-r border-p-line bg-p-panel"
      :class="mobileNavOpen ? 'fixed left-[68px] flex shadow-pop' : 'hidden lg:flex'"
    >
      <Link
        :href="route('admin.index')"
        class="block border-b border-p-line2 px-[18px] pb-3.5 pt-[18px] no-underline"
      >
        <!-- Logo varsa site adi YAZILMAZ; ayni markayi iki kez gostermis olur. -->
        <img
          v-if="brandLogo"
          :src="brandLogo"
          :alt="$page.props.siteName"
          class="h-8 max-w-full object-contain object-left"
        />
        <div v-else class="truncate font-display text-[15px] font-extrabold text-p-ink">
          {{ $page.props.siteName }}
        </div>
        <div class="mt-1 truncate text-[11.5px] text-p-ink3">{{ $page.props.siteDomain }}</div>
      </Link>

      <div
        class="px-3.5 pb-1 pt-3 text-[10.5px] font-bold uppercase tracking-[.09em] text-p-ink3"
      >
        {{ current.label }}
      </div>

      <div class="flex flex-1 flex-col gap-px overflow-auto px-2.5 pb-4">
        <template v-for="(block, index) in grouped" :key="index">
          <div
            v-if="block.group"
            class="px-2.5 pb-1 pt-3 text-[10.5px] font-bold uppercase tracking-[.09em] text-p-ink3"
          >
            {{ block.group }}
          </div>
          <a
            v-for="item in block.items"
            :key="item.label + (item.url || item.action)"
            :href="item.url || 'javascript:void(0)'"
            class="flex items-center gap-2.5 rounded-[9px] px-2.5 py-2 text-[12.8px] no-underline"
            :class="
              item.active ? 'bg-p-soft font-semibold text-p-accent' : 'text-p-ink2 hover:bg-p-panel2'
            "
            @click.prevent="open(item)"
          >
            <i :class="item.icon" class="w-[17px] text-center text-[13.5px] opacity-85"></i>
            <span class="flex-1">{{ item.label }}</span>
            <span
              v-if="item.badge"
              class="grid h-[18px] min-w-[19px] place-items-center rounded-[9px] bg-p-danger px-1.5 text-[10px] font-bold text-white"
            >
              {{ item.badge > 99 ? '99+' : item.badge }}
            </span>
          </a>
        </template>
      </div>

      <div class="flex items-center gap-2.5 border-t border-p-line2 px-3.5 py-3">
        <Link
          :href="route('admin.profile.index')"
          class="flex min-w-0 flex-1 items-center gap-2.5 rounded-[9px] no-underline"
        >
          <img
            v-if="$page.props.auth.user?.profileImage"
            :src="$page.props.auth.user.profileImage"
            :alt="$page.props.auth.user?.nickname"
            class="h-8 w-8 shrink-0 rounded-full object-cover"
          />
          <div
            v-else
            class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-p-soft text-xs font-bold text-p-accent"
          >
            {{ $page.props.auth.user?.initials }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="truncate text-[12.5px] font-semibold text-p-ink">
              {{ $page.props.auth.user?.nickname }}
            </div>
            <div class="text-[11px] capitalize text-p-ink3">{{ $page.props.auth.user?.role }}</div>
          </div>
        </Link>
        <!--
          Gerçek form: admin.logout /login'e (düz Blade sayfası) yönlendiriyor.
          Inertia POST'u bu 302'yi izleyip HTML alır ve hata verir.
        -->
        <form ref="logoutForm" :action="route('admin.logout')" method="post">
          <input type="hidden" name="_token" :value="csrfToken" />
          <button
            type="button"
            :title="__('user.logout')"
            class="border-0 bg-transparent text-[13px] text-p-ink3 hover:text-p-ink"
            @click="confirmLogout"
          >
            <i class="fa-solid fa-right-from-bracket"></i>
          </button>
        </form>
      </div>
    </aside>

    <!-- İçerik -->
    <main class="flex min-w-0 flex-1 flex-col">
      <header
        class="sticky top-0 z-[5] flex min-h-[58px] flex-wrap items-center gap-3.5 border-b border-p-line bg-p-panel px-5 py-2"
      >
        <button
          class="p-icon-btn lg:hidden"
          :title="current.label"
          @click="mobileNavOpen = !mobileNavOpen"
        >
          <i class="fa-solid fa-bars"></i>
        </button>

        <div class="whitespace-nowrap font-display text-[15px] font-bold">
          {{ pageHeader.title }}
        </div>
        <div class="hidden truncate whitespace-nowrap text-xs text-p-ink3 md:block">
          <template v-for="(crumb, index) in pageHeader.crumbs" :key="index">
            <Link v-if="crumbHref(crumb)" :href="crumbHref(crumb)" class="text-p-ink3 hover:text-p-accent">{{ crumb.label }}</Link>
            <span v-else>{{ crumb.label }}</span>
            <span v-if="index < pageHeader.crumbs.length - 1" class="px-1.5">/</span>
          </template>
        </div>
        <div class="flex-1"></div>

        <button
          class="hidden h-[34px] items-center gap-2 rounded-[9px] border border-p-line bg-p-panel2 pl-3 pr-2.5 text-[12.5px] text-p-ink3 hover:border-p-accent hover:text-p-ink sm:flex"
          @click="paletteOpen = true"
        >
          <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
          <span class="whitespace-nowrap">{{ __('general.search') }}</span>
          <kbd class="rounded-[5px] border border-p-line bg-p-panel px-1.5 text-[10.5px]">⌘K</kbd>
        </button>

        <!--
          Panel içerik dili.

          Eski AdminLTE navbar'ındaki seçicinin karşılığı. BİLEREK düz `<a>`:
            - `admin.change_language` oturumu değiştiriyor ve `window.__panelLang`
              kök blade'de basıldığı için tam sayfa yüklemesi ŞART;
            - `<Link>` olsaydı Inertia v2 prefetch'i hover'da dili değiştirebilirdi.
        -->
        <div v-if="($page.props.languages || []).length > 1" class="relative">
          <button
            class="flex h-[34px] items-center gap-1.5 rounded-[9px] border border-p-line bg-p-panel2 px-2.5 text-[12px] text-p-ink2 hover:border-p-accent hover:text-p-ink"
            :title="__('general.language')"
            @click="languageOpen = !languageOpen"
          >
            <i class="fa-solid fa-language text-[12px]"></i>
            <span class="uppercase">{{ $page.props.currentLanguage?.code }}</span>
          </button>

          <div
            v-if="languageOpen"
            class="absolute right-0 top-[38px] z-50 min-w-[150px] overflow-hidden rounded-xl border border-p-line bg-p-panel shadow-pop"
          >
            <a
              v-for="language in $page.props.languages"
              :key="language.code"
              :href="route('admin.change_language', { language: language.code })"
              class="flex items-center gap-2 px-3 py-2 text-[12.5px] text-p-ink2 no-underline hover:bg-p-panel2 hover:text-p-ink"
              :class="language.code === $page.props.currentLanguage?.code && 'bg-p-soft text-p-accent'"
            >
              <span class="uppercase text-[10.5px] text-p-ink3">{{ language.code }}</span>
              {{ language.name }}
            </a>
          </div>
        </div>

        <!--
          Onbellek temizleme ust barda da: sol menude Ayarlar bolumunun icinde
          gizliydi. Route `can:admin` ile korunuyor, dugme de ayni kapiyi
          kullanir - yoksa yetkisiz kullaniciya 403 veren bir dugme gosterilirdi.
        -->
        <button
          v-if="$page.props.can?.admin"
          class="grid h-[34px] w-[34px] place-items-center rounded-[9px] border border-p-line bg-p-panel2 text-[12px] text-p-ink2 transition-colors hover:bg-p-chip hover:text-p-ink disabled:opacity-50"
          :title="__('cache.clear_cache')"
          :disabled="cacheClearing"
          @click="clearCache"
        >
          <i :class="cacheClearing ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-broom'"></i>
        </button>

        <NotificationBell />

        <Link
          v-if="$page.props.can?.createPost"
          :href="route('admin.post.create', { type: 'blogs' })"
          class="p-btn-primary"
        >
          <i class="fa-solid fa-feather text-xs"></i>
          <span class="hidden sm:inline">{{ __('general.new') }}</span>
        </Link>
      </header>

      <slot />
    </main>

    <CommandPalette v-model:open="paletteOpen" :sections="sections" @navigate="open" />
    <FlashToast />
    <ConfirmDialog ref="logoutConfirm" />
  </div>
</template>
