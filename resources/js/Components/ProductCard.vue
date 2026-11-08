<script setup>
import UiIcon from "./UiIcon.vue";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { money, setQuantity, cartOpen, favourite } from "../composables/shop";
const props = defineProps({ product: Object });
const page = usePage();
const variant = computed(() => props.product.variants[0]);
function buy() {
    if (!variant.value?.stock) return;
    const q =
        page.props.cart.items.find((i) => i.id === variant.value.id)
            ?.quantity ?? 0;
    setQuantity(variant.value.id, q + 1);
    cartOpen.value = true;
}
</script>
<template>
    <div class="catalog-page__item">
        <p class="catalog-page__item__badge" v-if="product.badge">
            {{ product.badge }}
        </p>
        <button
            :aria-pressed="page.props.favourites.includes(product.id)"
            @click="favourite(product)"
            aria-label="В избранное"
            class="catalog-page__item__add-wishlist"
        >
            <UiIcon name="heart" />
        </button>
        <a
            :href="'/products/' + product.slug"
            :aria-label="product.name"
            class="catalog-page__item__link"
        >
            <div class="catalog-page__item__hover">
                <img :alt="product.name" :src="product.image" />
            </div>
            <div class="catalog-page__item__img">
                <img :alt="product.name" :src="product.image" />
            </div>
            <div class="catalog-page__item__info">
                <div class="catalog-page__item__left">
                    <h3 class="catalog-page__item__name">{{ product.name }}</h3>
                    <div class="catalog-page__item__text">
                        {{ product.description }}
                    </div>
                </div>
                <p class="catalog-page__item__price">
                    {{ variant ? money(variant.price_minor) : "" }}
                </p>
            </div>
        </a>
        <button
            class="catalog-page__item__btn"
            :disabled="!variant?.stock"
            @click="buy"
        >
            {{
                variant?.stock
                    ? `Купить — ${money(variant.price_minor)}`
                    : "Нет в наличии"
            }}
        </button>
        <button
            class="catalog-page__item__btn_mobile"
            :disabled="!variant?.stock"
            @click="buy"
        >
            {{
                variant?.stock
                    ? `Купить — ${money(variant.price_minor)}`
                    : "Нет в наличии"
            }}
        </button>
    </div>
</template>
