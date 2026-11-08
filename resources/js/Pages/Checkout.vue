<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
import { usePage, useForm, Link } from "@inertiajs/vue3";
import { money, setQuantity } from "../composables/shop";
const page = usePage();
const user = page.props.auth.user;
const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone ?? "",
    address: "",
    surname: user.last_name ?? "",
    middlename: user.middle_name ?? "",
    comment: "",
    delivery_method: "cdek",
    consent: false,
    checkout_token: page.props.checkoutToken,
    expected_subtotal_minor: page.props.cart.subtotal_minor,
});
function submit() {
    form.expected_subtotal_minor = page.props.cart.subtotal_minor;
    form.transform((data) => ({
        ...data,
        name: [data.name, data.surname, data.middlename]
            .filter(Boolean)
            .join(" "),
    })).post("/checkout");
}
</script>

<template>
    <section class="order-registration">
        <div class="container">
            <div class="order-registration__wrap">
                <nav class="breadcrumb">
                    <ul>
                        <li>
                            <Link :href="text('shared.06e02c213def')">{{
                                text("shared.c655cbabd139")
                            }}</Link>
                        </li>
                        <li>
                            <Link :href="text('shared.e423761e27f6')">{{
                                text("shared.e28737802536")
                            }}</Link>
                        </li>
                        <li>{{ text("shared.a97cdef9cef3") }}</li>
                    </ul>
                </nav>
                <p v-if="!page.props.cart.items.length" class="store-empty">
                    {{ text("shared.1ddf2113b7ea") }}
                    <Link :href="text('shared.c5de9407b35b')">{{
                        text("shared.9e8cc041441a")
                    }}</Link>
                </p>
                <form
                    v-else
                    class="order-registration__main"
                    @submit.prevent="submit"
                >
                    <div class="order-registration__left">
                        <div class="order-registration__item">
                            <div class="order-registration__item__num">
                                {{ text("shared.38e9be6401e6") }}
                            </div>
                            <div class="order-registration__item__main">
                                <h1 class="order-registration__item__name">
                                    <span>{{
                                        text("shared.38e9be6401e6")
                                    }}</span
                                    >{{ text("shared.906f90471255") }}
                                </h1>
                                <div class="order-registration__contact-item">
                                    <div class="content">
                                        <div class="row">
                                            <div class="col">
                                                <input
                                                    v-model="form.name"
                                                    :placeholder="
                                                        text(
                                                            'shared.57832c3b8730',
                                                        )
                                                    "
                                                    aria-label="Имя"
                                                    autocomplete="given-name"
                                                    required
                                                    maxlength="120"
                                                />
                                            </div>
                                            <div class="col">
                                                <input
                                                    v-model="form.surname"
                                                    :placeholder="
                                                        text(
                                                            'shared.2192ec68f111',
                                                        )
                                                    "
                                                    aria-label="Фамилия"
                                                    autocomplete="family-name"
                                                    maxlength="80"
                                                />
                                            </div>
                                            <div class="col">
                                                <input
                                                    v-model="form.middlename"
                                                    :placeholder="
                                                        text(
                                                            'shared.3e95a15ad917',
                                                        )
                                                    "
                                                    aria-label="Отчество"
                                                    autocomplete="additional-name"
                                                    maxlength="80"
                                                />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col">
                                                <input
                                                    type="tel"
                                                    v-model="form.phone"
                                                    :placeholder="
                                                        text(
                                                            'shared.0241a7dc6f4d',
                                                        )
                                                    "
                                                    aria-label="Телефон"
                                                    autocomplete="tel"
                                                    required
                                                    maxlength="30"
                                                />
                                            </div>
                                            <div class="col">
                                                <input
                                                    type="email"
                                                    v-model="form.email"
                                                    :placeholder="
                                                        text(
                                                            'shared.293d442b5527',
                                                        )
                                                    "
                                                    aria-label="Электронная почта"
                                                    autocomplete="email"
                                                    required
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="order-registration__contact-item__policy"
                                    >
                                        <div class="checkbox-item">
                                            <input
                                                class="checkbox-input"
                                                type="checkbox"
                                                id="order-policy"
                                                v-model="form.consent"
                                                required
                                            /><label
                                                class="checkbox-label"
                                                for="order-policy"
                                                >{{
                                                    text("shared.81221047ad8b")
                                                }}
                                                <Link
                                                    :href="
                                                        text(
                                                            'shared.d04b3f4784de',
                                                        )
                                                    "
                                                    >{{
                                                        text(
                                                            "shared.9c9877ecb79e",
                                                        )
                                                    }}</Link
                                                >
                                                {{
                                                    text("shared.0e7fe813519a")
                                                }}
                                                <Link
                                                    :href="
                                                        text(
                                                            'shared.ad6773b95aff',
                                                        )
                                                    "
                                                    >{{
                                                        text(
                                                            "shared.7406fbf205b8",
                                                        )
                                                    }}</Link
                                                ></label
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="order-registration__item">
                            <div class="order-registration__item__num">
                                {{ text("shared.8057ce4541a8") }}
                            </div>
                            <div class="order-registration__item__main">
                                <h2 class="order-registration__item__name">
                                    <span>{{
                                        text("shared.8057ce4541a8")
                                    }}</span
                                    >{{ text("shared.94511f31ee64") }}
                                </h2>
                                <div class="order-registration__delivery-item">
                                    <div
                                        class="order-registration__delivery__variation"
                                    >
                                        <div class="radio-item">
                                            <input
                                                type="radio"
                                                class="radio-input"
                                                id="delivery-cdek"
                                                value="cdek"
                                                v-model="form.delivery_method"
                                            /><label
                                                class="radio-label"
                                                for="delivery-cdek"
                                                ><img
                                                    :src="
                                                        text(
                                                            'shared.d413774c23c1',
                                                        )
                                                    "
                                                    :alt="
                                                        text(
                                                            'shared.ae5f510e67a3',
                                                        )
                                                    "
                                                />
                                                <p class="radio-item__text">
                                                    {{
                                                        text(
                                                            "shared.f41c10a28a13",
                                                        )
                                                    }}
                                                </p></label
                                            >
                                        </div>
                                        <div class="radio-item">
                                            <input
                                                type="radio"
                                                class="radio-input"
                                                id="delivery-courier"
                                                value="courier"
                                                v-model="form.delivery_method"
                                            /><label
                                                class="radio-label"
                                                for="delivery-courier"
                                                ><p class="radio-item__text">
                                                    {{
                                                        text(
                                                            "shared.dcaa69222dcc",
                                                        )
                                                    }}
                                                </p></label
                                            >
                                        </div>
                                    </div>
                                    <div
                                        class="order-registration__delivery__courier active"
                                    >
                                        <div
                                            class="order-registration__delivery__courier__content"
                                        >
                                            <div class="row">
                                                <input
                                                    v-model="form.address"
                                                    aria-label="Город, адрес или желаемый пункт СДЭК"
                                                    :placeholder="
                                                        form.delivery_method ===
                                                        'cdek'
                                                            ? 'Город и желаемый пункт СДЭК*'
                                                            : 'Город, улица, дом и квартира*'
                                                    "
                                                    autocomplete="street-address"
                                                    required
                                                    maxlength="1000"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <p class="store-status">
                                        {{ text("shared.04885b877d58") }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="order-registration__item">
                            <div class="order-registration__item__num">
                                {{ text("shared.10e90d86ab98") }}
                            </div>
                            <div class="order-registration__item__main">
                                <h2 class="order-registration__item__name">
                                    <span>{{
                                        text("shared.10e90d86ab98")
                                    }}</span
                                    >{{ text("shared.9d2006f57444") }}
                                </h2>
                                <p class="store-status">
                                    {{ text("shared.f3ed82ea93fa") }}
                                </p>
                                <div
                                    class="order-registration__delivery__courierMessage"
                                >
                                    <textarea
                                        v-model="form.comment"
                                        aria-label="Комментарий"
                                        :placeholder="
                                            text('shared.79603d5e05f0')
                                        "
                                        maxlength="2000"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="order-registration__right">
                        <div class="order-registration__right__content">
                            <h2 class="order-registration__right__title">
                                {{ text("shared.97d62936a8bc") }}
                            </h2>
                            <div
                                class="order-registration__product-list horizontal-scrolling scrollable"
                            >
                                <Link
                                    v-for="item in page.props.cart.items"
                                    :key="item.id"
                                    :href="'/products/' + item.product_slug"
                                    class="order-registration__product-item"
                                    ><img
                                        :src="item.image"
                                        :alt="item.name"
                                        :title="
                                            item.name + ' × ' + item.quantity
                                        "
                                /></Link>
                            </div>
                            <div class="order-registration__right__info">
                                <div class="row">
                                    <p class="name">
                                        {{ text("shared.76a52edfe169")
                                        }}{{ page.props.cart.count
                                        }}{{ text("shared.e9e096fbe64e") }}
                                    </p>
                                    <p class="dots"></p>
                                    <p class="value">
                                        {{
                                            money(
                                                page.props.cart.subtotal_minor,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div class="row">
                                    <p class="name">
                                        {{ text("shared.94511f31ee64") }}
                                    </p>
                                    <p class="dots"></p>
                                    <p class="value">
                                        {{ text("shared.7efd9056ed43") }}
                                    </p>
                                </div>
                                <div class="row-result">
                                    <p class="name">
                                        {{ text("shared.a13ff9a6d2c6") }}
                                    </p>
                                    <p class="value">
                                        {{
                                            money(
                                                page.props.cart.subtotal_minor,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <p class="store-status">
                                {{ text("shared.6493ed8f713d") }}
                            </p>
                            <p class="store-errors" role="alert">
                                {{ Object.values(form.errors).join("\n") }}
                            </p>
                            <button
                                class="order-registration__right__submit-btn"
                                type="submit"
                                :disabled="
                                    form.processing ||
                                    page.props.cart.items.some(
                                        (i) => !i.available,
                                    )
                                "
                            >
                                {{ text("shared.1441d918a809") }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>
