import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import "swiper/css/bundle";
import "lenis/dist/lenis.css";
import "../css/layout.css";
import "@fontsource-variable/manrope";
import "@fontsource/prosto-one/400.css";
import "../css/store.css";
createInertiaApp({
    title: (title) => (title ? `${title} — Kamali` : "Kamali"),
    resolve: () => import("./Pages/Store.vue"),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: { color: "#621617" },
});
