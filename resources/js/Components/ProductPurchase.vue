<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { setQuantity, cartOpen, money } from "../composables/shop";
const props = defineProps({ product: Object });
const variantId = defineModel();
const page = usePage();
const variant = computed(() =>
    props.product.variants.find((v) => v.id === variantId.value),
);
const quantity = computed(
    () =>
        page.props.cart.items.find((i) => i.id === variantId.value)?.quantity ??
        0,
);
function add() {
    if (!variant.value) return;
    setQuantity(variant.value.id, quantity.value + 1);
    cartOpen.value = true;
}
</script>
<template>
    <button
        class="product-hero__add-basket"
        :disabled="!variant || !variant.stock || quantity >= variant.stock"
        @click="add"
    >
        {{
            !variant
                ? "Выберите вариант"
                : variant.stock
                  ? `Купить — ${money(variant.price_minor)}`
                  : "Нет в наличии"
        }}
    </button>
</template>
