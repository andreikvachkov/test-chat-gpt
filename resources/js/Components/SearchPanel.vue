<script setup>
import UiIcon from "./UiIcon.vue";
import { useContent } from "../composables/content";
const { text } = useContent();
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { searchOpen } from "../composables/shop";
const q = ref("");
function search() {
    searchOpen.value = false;
    router.get("/catalog", { q: q.value });
}
</script>
<template>
    <div
        v-if="searchOpen"
        class="popup-background active"
        @click="searchOpen = false"
    ></div>
    <div
        v-if="searchOpen"
        class="search-popup active"
        role="dialog"
        aria-label="Поиск"
    >
        <button
            class="search-popup__close"
            aria-label="Закрыть поиск"
            @click="searchOpen = false"
        >
            <UiIcon name="close" />
        </button>
        <div class="search-popup__wrap">
            <h2 class="search-popup__title">
                {{ text("shared.30a10289d11a") }}
            </h2>
            <form class="store-form" @submit.prevent="search">
                <input
                    v-model="q"
                    :placeholder="text('shared.ebab38659714')"
                    aria-label="Поиск товаров"
                    required
                    maxlength="100"
                /><button type="submit">
                    {{ text("shared.5fc86f4bb6a0") }}
                </button>
            </form>
        </div>
    </div>
</template>
