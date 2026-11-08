import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
export const cartOpen = ref(false);
export const searchOpen = ref(false);
export const interactionError = ref("");
export const money = (value) =>
    new Intl.NumberFormat("ru-RU", {
        style: "currency",
        currency: "RUB",
        minimumFractionDigits: value % 100 === 0 ? 0 : 2,
        maximumFractionDigits: 2,
    }).format(value / 100);
export function setQuantity(id, quantity) {
    interactionError.value = "";
    router.post(
        "/cart",
        { variant_id: id, quantity },
        {
            preserveScroll: true,
            onError: (errors) =>
                (interactionError.value = Object.values(errors).join("\n")),
        },
    );
}
export function favourite(product) {
    if (!usePage().props.auth.user) {
        router.visit("/login");
        return;
    }
    router.post("/favourites/" + product.id, {}, { preserveScroll: true });
}
