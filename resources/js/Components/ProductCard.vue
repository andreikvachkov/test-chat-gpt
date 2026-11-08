<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
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
        <div class="catalog-page__item__hover">
            <img :alt="product.name" :src="product.image" />
            <a
                @click.prevent="buy"
                class="catalog-page__item__btn"
                :href="text('shared.58c5d7f855a7')"
                >{{
                    variant?.stock
                        ? `Купить — ${money(variant.price_minor)}`
                        : "Нет в наличии"
                }}</a
            >
        </div>
        <p class="catalog-page__item__badge" v-if="product.badge">
            {{ product.badge }}
        </p>
        <button
            :aria-pressed="page.props.favourites.includes(product.id)"
            @click="favourite(product)"
            aria-label="В избранное"
            class="catalog-page__item__add-wishlist"
        >
            <svg
                fill="none"
                height="30"
                viewBox="0 0 30 30"
                width="30"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M10.3873 24.173L10.8504 23.5831L10.3873 24.173ZM14.375 6.64593L13.8357 7.16717C13.977 7.31337 14.1717 7.39593 14.375 7.39593C14.5783 7.39593 14.773 7.31337 14.9143 7.16717L14.375 6.64593ZM18.3627 24.173L18.8258 24.763L18.3627 24.173ZM10.3873 24.173L10.8504 23.5831C8.87492 22.0323 6.67382 20.4884 4.93021 18.5333C3.21233 16.607 2 14.3416 2 11.3988H1.25H0.5C0.5 14.8118 1.92542 17.4177 3.81073 19.5317C5.67031 21.6169 8.03786 23.2822 9.92416 24.763L10.3873 24.173ZM1.25 11.3988H2C2 8.50321 3.64341 6.06113 5.90845 5.03012C8.12705 4.02025 11.075 4.31088 13.8357 7.16717L14.375 6.64593L14.9143 6.1247C11.769 2.87057 8.15441 2.35971 5.28702 3.6649C2.46607 4.94896 0.5 7.9385 0.5 11.3988H1.25ZM10.3873 24.173L9.92416 24.763C10.5986 25.2924 11.3078 25.8449 12.0228 26.2609C12.7376 26.6769 13.5291 27 14.375 27V26.25V25.5C13.9084 25.5 13.3874 25.3195 12.7772 24.9644C12.1671 24.6094 11.5386 24.1234 10.8504 23.5831L10.3873 24.173ZM18.3627 24.173L18.8258 24.763C20.7121 23.2822 23.0797 21.6169 24.9393 19.5317C26.8246 17.4177 28.25 14.8118 28.25 11.3988H27.5H26.75C26.75 14.3416 25.5377 16.607 23.8198 18.5333C22.0762 20.4884 19.8751 22.0323 17.8996 23.5831L18.3627 24.173ZM27.5 11.3988H28.25C28.25 7.9385 26.2839 4.94896 23.463 3.6649C20.5956 2.35971 16.981 2.87057 13.8357 6.1247L14.375 6.64593L14.9143 7.16717C17.675 4.31088 20.6229 4.02025 22.8416 5.03012C25.1066 6.06113 26.75 8.50321 26.75 11.3988H27.5ZM18.3627 24.173L17.8996 23.5831C17.2114 24.1234 16.5829 24.6094 15.9728 24.9644C15.3626 25.3195 14.8416 25.5 14.375 25.5V26.25V27C15.2209 27 16.0124 26.6769 16.7272 26.2609C17.4422 25.8449 18.1514 25.2924 18.8258 24.763L18.3627 24.173Z"
                    fill="#621617"
                ></path>
            </svg>
        </button>
        <a :href="'/products/' + product.slug" class="catalog-page__item__img">
            <img :alt="product.name" :src="product.image" />
        </a>
        <div class="catalog-page__item__info">
            <div class="catalog-page__item__left">
                <a
                    :href="'/products/' + product.slug"
                    class="catalog-page__item__name"
                    >{{ product.name }}</a
                >
                <div class="catalog-page__item__text">
                    {{ product.description }}
                </div>
            </div>
            <p class="catalog-page__item__price">
                {{ variant ? money(variant.price_minor) : "" }}
            </p>
        </div>
        <a
            class="catalog-page__item__btn_mobile"
            :href="text('shared.d4bf9055572a')"
            @click.prevent="buy"
            >{{
                variant?.stock
                    ? `Купить — ${money(variant.price_minor)}`
                    : "Нет в наличии"
            }}</a
        >
    </div>
</template>
