<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
import { computed } from "vue";
import { useForm, usePage, Link } from "@inertiajs/vue3";
const page = usePage();
const mode = computed(() => page.props.authMode);
const form = useForm({
    name: "",
    email: page.props.email ?? "",
    password: "",
    password_confirmation: "",
    remember: false,
    consent: false,
    token: page.props.token ?? "",
});
const title = computed(
    () =>
        ({
            login: "Войти в личный кабинет",
            register: "Зарегистрироваться",
            forgot: "Восстановить пароль",
            reset: "Новый пароль",
        })[mode.value],
);
function submit() {
    form.post(
        {
            login: "/login",
            register: "/register",
            forgot: "/forgot-password",
            reset: "/reset-password",
        }[mode.value],
        {
            onFinish: () => {
                form.reset("password", "password_confirmation");
            },
        },
    );
}
</script>
<template>
    <section class="account-login">
        <div class="container">
            <div class="account-login__wrap">
                <div class="account-login__content">
                    <form
                        class="account-login__form_phone"
                        @submit.prevent="submit"
                    >
                        <div class="account-login__step_email">
                            <div
                                class="account-login__step_phone-number__header"
                            >
                                <h1
                                    class="account-login__step_phone-number__title"
                                >
                                    {{ title }}
                                </h1>
                            </div>
                            <div class="account-login__step_email__group">
                                <div class="row" v-if="mode === 'register'">
                                    <input
                                        v-model="form.name"
                                        :placeholder="
                                            text('shared.fed90667c79e')
                                        "
                                        autocomplete="name"
                                        aria-label="Ваше имя"
                                        required
                                        maxlength="120"
                                    />
                                </div>
                                <div class="row">
                                    <input
                                        type="email"
                                        v-model="form.email"
                                        :placeholder="
                                            text('shared.2870d90a7b01')
                                        "
                                        autocomplete="email"
                                        aria-label="Электронная почта"
                                        required
                                        maxlength="255"
                                    />
                                </div>
                                <div class="row" v-if="mode !== 'forgot'">
                                    <div
                                        class="account-login__step_email__field"
                                    >
                                        <input
                                            class="account__input"
                                            type="password"
                                            v-model="form.password"
                                            :placeholder="
                                                text('shared.b1c5962ff07a')
                                            "
                                            :autocomplete="
                                                mode === 'login'
                                                    ? 'current-password'
                                                    : 'new-password'
                                            "
                                            aria-label="Пароль"
                                            required
                                        />
                                    </div>
                                </div>
                                <div
                                    class="row"
                                    v-if="['register', 'reset'].includes(mode)"
                                >
                                    <input
                                        class="account__input"
                                        type="password"
                                        v-model="form.password_confirmation"
                                        :placeholder="
                                            text('shared.45b246d13611')
                                        "
                                        autocomplete="new-password"
                                        aria-label="Повторите пароль"
                                        required
                                    />
                                    <p>{{ text("shared.968e09de5601") }}</p>
                                </div>
                                <div
                                    class="account-login__step_email__group__bottom"
                                    v-if="mode === 'login'"
                                >
                                    <label
                                        ><input
                                            type="checkbox"
                                            v-model="form.remember"
                                        />
                                        {{ text("shared.dcfcac4ab282") }}</label
                                    ><Link
                                        :href="text('shared.164c6c7e29ea')"
                                        >{{ text("shared.8b85eecac8f7") }}</Link
                                    >
                                </div>
                                <label v-if="mode === 'register'"
                                    ><input
                                        type="checkbox"
                                        v-model="form.consent"
                                        required
                                    />
                                    {{ text("shared.b3cdf5bb7137") }}
                                    <Link :href="text('shared.d3efd08079a5')">{{
                                        text("shared.0436f0e829fb")
                                    }}</Link></label
                                >
                                <p class="store-errors" role="alert">
                                    {{ Object.values(form.errors).join("\n") }}
                                </p>
                                <p class="store-status" role="status">
                                    {{ page.props.flash.status }}
                                </p>
                            </div>
                            <div class="account-login__step_email__actions">
                                <button
                                    type="submit"
                                    class="account-login__step_email__submit-button"
                                    :disabled="form.processing"
                                >
                                    {{ title }}
                                </button>
                                <p
                                    class="account-login__step_phone-number__register"
                                >
                                    <Link
                                        v-if="mode === 'login'"
                                        :href="text('shared.95270c13f6b3')"
                                        >{{ text("shared.c9ee903d6492") }}</Link
                                    ><Link
                                        v-else
                                        :href="text('shared.f994bdbd0969')"
                                        >{{ text("shared.45a4c86fba03") }}</Link
                                    >
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>
