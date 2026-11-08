<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    watch,
    ref,
} from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import PromoBar from "../Components/PromoBar.vue";
import SiteHeader from "../Components/SiteHeader.vue";
import SiteFooter from "../Components/SiteFooter.vue";
import CookieNotice from "../Components/CookieNotice.vue";
import CartPanel from "../Components/CartPanel.vue";
import SearchPanel from "../Components/SearchPanel.vue";
import Catalog from "./Catalog.vue";
import Auth from "./Auth.vue";
import Checkout from "./Checkout.vue";
import Account from "./Account.vue";
import { cartOpen, searchOpen, interactionError } from "../composables/shop";
import { useContent } from "../composables/content";
import defaults from "../content-defaults.json";
import { mountLayout } from "../layout-runtime";
const layouts = import.meta.glob("../Layouts/*.vue", {
    eager: true,
    import: "default",
});
const page = usePage();
const { text } = useContent();
const backgrounds = computed(() =>
    Object.fromEntries(
        Object.keys(defaults)
            .filter((k) => k.startsWith("shared.bg-"))
            .map((k) => ["--" + k.slice(7), `url(${JSON.stringify(text(k))})`]),
    ),
);
const current = computed(() => page.props.template);
const layout = computed(() => layouts[`../Layouts/${current.value}.vue`]);
const contentKey = computed(() => page.url);
let cleanup = () => {};
async function init() {
    await nextTick();
    cleanup();
    cleanup = mountLayout();
}
onMounted(init);
watch(contentKey, () => {
    cartOpen.value = false;
    searchOpen.value = false;
    interactionError.value = "";
    init();
});
onBeforeUnmount(() => cleanup());
watch([cartOpen, searchOpen], ([cart, search]) =>
    document.body.classList.toggle("no-scroll", cart || search),
);
function navigate(event) {
    const target = event.target.closest("a");
    if (!target) return;
    if (target.classList.contains("header__basket")) {
        event.preventDefault();
        cartOpen.value = true;
        return;
    }
    if (
        target.classList.contains("header__search") ||
        target.classList.contains("header__search-mobile")
    ) {
        event.preventDefault();
        searchOpen.value = true;
        return;
    }
    if (
        event.defaultPrevented ||
        event.button !== 0 ||
        event.metaKey ||
        event.ctrlKey ||
        event.shiftKey ||
        event.altKey ||
        target.target ||
        target.hasAttribute("download")
    )
        return;
    const url = new URL(target.href);
    if (
        url.origin !== location.origin ||
        url.hash ||
        url.pathname.startsWith("/admin") ||
        url.pathname.startsWith("/assets")
    )
        return;
    event.preventDefault();
    router.visit(url.pathname + url.search);
}
</script>
<template>
    <Head :title="page.props.meta.title"
        ><meta name="description" :content="page.props.meta.description || ''"
    /></Head>
    <div
        :key="contentKey"
        :style="backgrounds"
        @click="navigate"
        @keydown.esc="
            cartOpen = false;
            searchOpen = false;
        "
    >
        <PromoBar />
        <div class="wrap">
            <SiteHeader />
            <main style="display: contents">
                <Catalog v-if="current === 'catalog'" />
                <Auth v-else-if="current === 'lk-login'" />
                <Checkout v-else-if="current === 'order'" />
                <Account
                    v-else-if="
                        ['lk-personal-info', 'lk-order-page'].includes(current)
                    "
                />
                <component v-else-if="layout" :is="layout" />
            </main>
            <SiteFooter />
        </div>
        <CookieNotice /><CartPanel /><SearchPanel />
        <p
            v-if="interactionError && !cartOpen"
            class="store-errors"
            role="alert"
        >
            {{ interactionError }}
        </p>
    </div>
</template>
