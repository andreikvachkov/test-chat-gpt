<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
import { ref } from "vue";
import { usePage, useForm, Link, router } from "@inertiajs/vue3";
import { money } from "../composables/shop";
const page = usePage();
const user = page.props.auth.user;
const tab = ref("orders");
const profile = useForm({
    name: user.name,
    phone: user.phone ?? "",
    last_name: user.last_name ?? "",
    middle_name: user.middle_name ?? "",
    birthdate: user.birthdate ?? "",
});
const password = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});
const statuses = {
    new: "Новый",
    processing: "В работе",
    shipped: "Отправлен",
    completed: "Выполнен",
};
</script>
<template>
    <section class="account">
        <div class="container">
            <div class="account__wrap">
                <aside class="account__sidebar">
                    <div class="account__greeting">
                        <div class="ico">
                            <img :src="text('shared.487d1a8fea96')" alt="" />
                        </div>
                        <p>{{ text("shared.c151c0a51420") }} {{ user.name }}</p>
                    </div>
                    <nav class="account__nav">
                        <ul>
                            <li>
                                <button
                                    :class="{ active: tab === 'orders' }"
                                    @click="tab = 'orders'"
                                >
                                    {{ text("shared.1fe1f23bf2cf") }}
                                </button>
                            </li>
                            <li>
                                <button
                                    :class="{ active: tab === 'profile' }"
                                    @click="tab = 'profile'"
                                >
                                    {{ text("shared.01fada78d89b") }}
                                </button>
                            </li>
                            <li>
                                <button
                                    :class="{ active: tab === 'password' }"
                                    @click="tab = 'password'"
                                >
                                    {{ text("shared.b46c38e6e8b4") }}
                                </button>
                            </li>
                            <li>
                                <Link :href="text('shared.7ef0aa4b5545')">{{
                                    text("shared.d0f8585de17e")
                                }}</Link>
                            </li>
                        </ul>
                    </nav>
                    <button
                        class="account__logout"
                        @click="router.post('/logout')"
                    >
                        {{ text("shared.2416bc4703b3") }}
                    </button>
                </aside>
                <div class="account__content">
                    <template v-if="page.props.order"
                        ><h1 class="store-order">
                            {{ text("shared.f246a492402e") }}
                            {{ page.props.order.number.slice(0, 8) }}
                        </h1>
                        <p class="store-order">
                            {{ statuses[page.props.order.status] }}
                            {{ text("shared.33655c7a824c") }}
                        </p>
                        <div
                            class="store-order"
                            v-for="item in page.props.order.items"
                            :key="item.id"
                        >
                            {{ item.name }} {{ text("shared.eda6211c68b3") }}
                            {{ item.quantity }}
                            {{ text("shared.3973da44c678") }}
                            {{ money(item.price_minor * item.quantity) }}
                            <p v-if="item.backordered">Под заказ</p>
                            <p v-if="item.purchase_note">
                                {{ item.purchase_note }}
                            </p>
                        </div>
                        <p class="store-order">
                            {{ text("shared.85240aac7a0b") }}
                            {{ money(page.props.order.subtotal_minor)
                            }}{{ text("shared.a6417e59b702") }}
                            {{
                                page.props.order.shipping_minor === null
                                    ? "Стоимость доставки согласуем отдельно."
                                    : `Доставка: ${money(page.props.order.shipping_minor)}.`
                            }}
                        </p>
                        <Link
                            class="store-actions"
                            :href="text('shared.4f9ff4570662')"
                            >{{ text("shared.b7d6c5b2a329") }}</Link
                        ></template
                    >
                    <template v-else
                        ><p class="store-status">
                            {{ page.props.flash.status }}
                        </p>
                        <section
                            v-if="tab === 'profile'"
                            class="account__personal-info"
                        >
                            <form
                                class="account__personal-info-form"
                                @submit.prevent="profile.patch('/account')"
                            >
                                <div class="account__personal-info-section">
                                    <h1 class="account__personal-info-title">
                                        {{ text("shared.131eeaf7697a") }}
                                    </h1>
                                    <div
                                        class="account__personal-info-section__conten"
                                    >
                                        <input
                                            v-model="profile.name"
                                            :placeholder="
                                                text('shared.543345f2f3da')
                                            "
                                            aria-label="Имя"
                                            required
                                            maxlength="120"
                                        /><input
                                            v-model="profile.last_name"
                                            :placeholder="
                                                text('shared.00e77369ba95')
                                            "
                                            aria-label="Фамилия"
                                            maxlength="80"
                                        /><input
                                            v-model="profile.middle_name"
                                            :placeholder="
                                                text('shared.08d8bd1c4c95')
                                            "
                                            aria-label="Отчество"
                                            maxlength="80"
                                        /><input
                                            v-model="profile.birthdate"
                                            type="date"
                                            aria-label="Дата рождения"
                                        />
                                    </div>
                                </div>
                                <div class="account__personal-info-section">
                                    <h2 class="account__personal-info-title">
                                        {{ text("shared.5fd3235c24f2") }}
                                    </h2>
                                    <div
                                        class="account__personal-info-section__conten"
                                    >
                                        <input
                                            type="tel"
                                            v-model="profile.phone"
                                            :placeholder="
                                                text('shared.535e4b5c67f7')
                                            "
                                            aria-label="Телефон"
                                            maxlength="30"
                                        /><input
                                            type="email"
                                            :value="user.email"
                                            aria-label="Почта"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <p class="store-errors">
                                    {{
                                        Object.values(profile.errors).join("\n")
                                    }}
                                </p>
                                <div class="account__personal-info-bottom">
                                    <button
                                        class="account__personal-info-save-btn"
                                        type="submit"
                                        :disabled="profile.processing"
                                    >
                                        {{ text("shared.d10117be61d5") }}
                                    </button>
                                </div>
                            </form>
                        </section>
                        <section
                            v-else-if="tab === 'password'"
                            class="account__password"
                        >
                            <h1 class="account__password-title">
                                {{ text("shared.7a961757ed51") }}
                            </h1>
                            <p class="account__password-text">
                                {{ text("shared.a2bc9dd22c04") }}
                            </p>
                            <form
                                class="account__password-form"
                                @submit.prevent="
                                    password.patch('/account/password', {
                                        onSuccess: () => password.reset(),
                                    })
                                "
                            >
                                <div class="account__password-content">
                                    <div class="row">
                                        <div class="account__password__field">
                                            <input
                                                class="account__input"
                                                type="password"
                                                v-model="
                                                    password.current_password
                                                "
                                                aria-label="Текущий пароль"
                                                :placeholder="
                                                    text('shared.dc7ed3559110')
                                                "
                                                autocomplete="current-password"
                                                required
                                            />
                                        </div>
                                        <div class="account__password__field">
                                            <input
                                                class="account__input"
                                                type="password"
                                                v-model="password.password"
                                                aria-label="Новый пароль"
                                                :placeholder="
                                                    text('shared.9c03f65211ad')
                                                "
                                                autocomplete="new-password"
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="account__password__field">
                                            <input
                                                class="account__input"
                                                type="password"
                                                v-model="
                                                    password.password_confirmation
                                                "
                                                aria-label="Повторите пароль"
                                                :placeholder="
                                                    text('shared.faef7a1019b6')
                                                "
                                                autocomplete="new-password"
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>
                                <p class="store-errors">
                                    {{
                                        Object.values(password.errors).join(
                                            "\n",
                                        )
                                    }}
                                </p>
                                <div class="account__password-bottom">
                                    <button
                                        class="account__password-save-btn"
                                        type="submit"
                                        :disabled="password.processing"
                                    >
                                        {{ text("shared.fd03d66cd2b6") }}
                                    </button>
                                </div>
                            </form>
                        </section>
                        <section v-else class="account__orders">
                            <h1 class="account__personal-info-title">
                                {{ text("shared.f95d107538cb") }}
                            </h1>
                            <div
                                v-if="!page.props.orders.length"
                                class="account__orders-empty"
                            >
                                <div class="account__orders-empty__body">
                                    <h2 class="account__orders-empty__title">
                                        {{ text("shared.7482c3f905ec") }}
                                    </h2>
                                    <div class="account__orders-empty__content">
                                        <p class="account__orders-empty__text">
                                            {{ text("shared.d7af7a6cd483") }}
                                        </p>
                                        <div
                                            class="account__orders-empty__links"
                                        >
                                            <Link
                                                class="account__orders-empty__link"
                                                :href="
                                                    text('shared.db4283e7fbee')
                                                "
                                                >{{
                                                    text("shared.9e8cc041441a")
                                                }}</Link
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-for="order in page.props.orders"
                                :key="order.id"
                                class="store-order"
                            >
                                <Link :href="'/account/orders/' + order.number"
                                    >{{ text("shared.f246a492402e") }}
                                    {{ order.number.slice(0, 8) }}
                                    {{ text("shared.1de312f55cff") }}
                                    {{ statuses[order.status] }}
                                    {{ text("shared.1de312f55cff") }}
                                    {{ money(order.subtotal_minor) }}</Link
                                >
                                <div v-for="item in order.items" :key="item.id">
                                    {{ item.name }}
                                    {{ text("shared.eda6211c68b3") }}
                                    {{ item.quantity }}
                                </div>
                            </div>
                        </section>
                    </template>
                </div>
            </div>
        </div>
    </section>
</template>
