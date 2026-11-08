<script setup>
import UiIcon from "./UiIcon.vue";
import { useContent } from "../composables/content";
const { text } = useContent();
import { computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import {
    cartOpen,
    setQuantity,
    money,
    interactionError,
    favourite,
} from "../composables/shop";
const page = usePage();
const cart = computed(() => page.props.cart);
</script>
<template>
    <div
        class="popup-background"
        :class="{ active: cartOpen }"
        @click="cartOpen = false"
    ></div>
    <aside
        class="popup-cart"
        :class="{ active: cartOpen }"
        role="dialog"
        aria-modal="true"
        aria-label="Корзина"
        v-if="cartOpen"
        @keydown.esc="cartOpen = false"
        tabindex="-1"
    >
        <button
            class="popup-cart__close"
            aria-label="Закрыть корзину"
            @click="cartOpen = false"
        >
            <UiIcon name="close" />
        </button>
        <div v-if="!cart.items.length" class="popup-cart__empty">
            <p class="popup-cart__empty__title">
                {{ text("shared.055f3ef8d195") }}
            </p>
            <p class="popup-cart__empty__text">
                {{ text("shared.28ecfa53a5b2") }}
            </p>
            <Link
                class="popup-cart__empty__btn"
                :href="text('shared.0ddfaf376efa')"
                @click="cartOpen = false"
                >{{ text("shared.ffde1bb24608") }}</Link
            >
        </div>
        <div v-else class="popup-cart__content">
            <p class="popup-cart__content__title">
                {{ text("shared.e94df17ac262") }} {{ cart.count }}
            </p>
            <div class="popup-cart__product-list scrollable">
                <div
                    v-for="item in cart.items"
                    :key="item.id"
                    class="popup-cart__product"
                >
                    <div class="popup-cart__product__left">
                        <img :src="item.image" :alt="item.name" />
                    </div>
                    <div class="popup-cart__product__right">
                        <div class="popup-cart__product__buttons">
                            <button
                                class="popup-cart__product__add-wishlist"
                                :aria-pressed="
                                    page.props.favourites.includes(
                                        item.product_id,
                                    )
                                "
                                aria-label="В избранное"
                                @click="favourite({ id: item.product_id })"
                            >
                                <UiIcon name="heart" />
                            </button>
                            <button
                                class="popup-cart__product__delete"
                                @click="setQuantity(item.id, 0)"
                                aria-label="Удалить товар"
                            >
                                <UiIcon name="close" />
                            </button>
                        </div>
                        <Link
                            :href="'/products/' + item.product_slug"
                            class="popup-cart__product__name"
                            @click="cartOpen = false"
                            >{{ item.name }}</Link
                        >
                        <div class="popup-cart__product__text">
                            {{ item.variant }}
                        </div>
                        <p v-if="!item.available" class="store-errors">
                            {{ text("shared.d43140447c08") }}
                        </p>
                        <div class="popup-cart__product__bottom">
                            <div class="popup-cart__product__count">
                                <button
                                    class="popup-cart__product__minus"
                                    aria-label="Уменьшить количество"
                                    @click="
                                        setQuantity(item.id, item.quantity - 1)
                                    "
                                >
                                    <UiIcon name="minus" /></button
                                ><span class="value">{{ item.quantity }}</span
                                ><button
                                    class="popup-cart__product__plus"
                                    aria-label="Увеличить количество"
                                    @click="
                                        setQuantity(item.id, item.quantity + 1)
                                    "
                                >
                                    <UiIcon name="plus" />
                                </button>
                            </div>
                            <div class="popup-cart__product__price">
                                {{ money(item.price_minor * item.quantity) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <p v-if="interactionError" class="store-errors" role="alert">
                {{ interactionError }}
            </p>
            <div class="popup-cart__product__result">
                <div class="row">
                    <p>
                        {{ text("shared.85240aac7a0b") }}
                    </p>
                    <p>{{ money(cart.subtotal_minor) }}</p>
                </div>
                <Link
                    class="popup-cart__product__submit-btn"
                    :href="text('shared.6de2be47f6e2')"
                    @click="cartOpen = false"
                    >{{ text("shared.659fb3e60c32") }}</Link
                >
            </div>
        </div>
    </aside>
</template>
