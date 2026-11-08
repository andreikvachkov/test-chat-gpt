<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
import { ref } from "vue";
const unavailable = ref(false);
</script>
<template>
    <section class="gift-hero">
        <div class="container">
            <div class="gift-hero__wrap">
                <h1 class="gift-hero__title">
                    {{ text("gift.9348a54d75ce") }}
                </h1>
            </div>
        </div>
    </section>
    <section class="gif-info">
        <div class="container">
            <div class="gif-info__wrap">
                <div class="gif-info__item">
                    <img
                        :src="text('shared.0f9011733cb1')"
                        alt=""
                        class="gif-info__ico"
                    />
                    <h3 class="gif-info__name">
                        {{ text("gift.e8243b654fad") }}
                    </h3>
                    <div class="gif-info__text">
                        {{ text("gift.3892b2d03858") }}
                    </div>
                </div>
                <div class="gif-info__item">
                    <img
                        :src="text('shared.bbc9f38ce9ab')"
                        alt=""
                        class="gif-info__ico"
                    />
                    <h3 class="gif-info__name">
                        {{ text("gift.27479a09e4fb") }}
                    </h3>
                    <div class="gif-info__text">
                        {{ text("gift.ccda5b1ffe62") }}
                    </div>
                </div>
                <div class="gif-info__item">
                    <img
                        :src="text('shared.e4cc7baca17b')"
                        alt=""
                        class="gif-info__ico"
                    />
                    <h3 class="gif-info__name">
                        {{ text("gift.a3db4c713632") }}
                    </h3>
                    <div class="gif-info__text">
                        {{ text("gift.65bf6a6acb5c") }}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="gift-card">
        <div class="container">
            <div class="gift-card__wrap">
                <div class="gift-card__left">
                    <div class="gift-card__left__main" id="gift-stage-1">
                        <div class="gift-card__count-stage">
                            {{ text("gift.78a0eb199158") }}
                        </div>
                        <h3 class="gift-card__title">
                            {{ text("gift.d3f559daa5db") }}
                        </h3>
                        <div class="gift-card__content">
                            <div class="gift-card__sum">
                                <div class="gift-card__sum__btn">
                                    <button>
                                        {{ text("gift.f6453833c579") }}
                                    </button>
                                    <button>
                                        {{ text("gift.e015eea21b07") }}
                                    </button>
                                    <button>
                                        {{ text("gift.92446e2d81b9") }}
                                    </button>
                                    <button>
                                        {{ text("gift.7862ac6e6af9") }}
                                    </button>
                                </div>
                                <div
                                    class="gift-card__sum__input gift-card__input"
                                >
                                    <input
                                        :placeholder="text('gift.86f0f28b37ab')"
                                        class="gift-card__sum__input"
                                        type="text"
                                    />
                                    <p class="error">
                                        {{ text("gift.6e186543b43d") }}
                                    </p>
                                </div>
                            </div>
                            <div class="gift-card__content__bottom">
                                <button
                                    class="gift-card__next"
                                    type="button"
                                    @click="unavailable = true"
                                >
                                    {{ text("gift.8b84e10b8613") }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div
                        class="gift-card__left__main"
                        id="gift-stage-2"
                        v-show="false"
                    >
                        <div class="gift-card__count-stage">
                            {{ text("gift.cfd5ffbb4515") }}
                        </div>
                        <h3 class="gift-card__title">
                            {{ text("gift.161950e6cc6e") }}
                        </h3>
                        <div class="gift-card__content">
                            <div class="gift-card__contact">
                                <div class="gift-card__row">
                                    <div class="gift-card__input">
                                        <input
                                            :placeholder="
                                                text('gift.eb74187c63f0')
                                            "
                                            type="text"
                                        />
                                        <p class="error">
                                            {{ text("gift.71cc0fd186d4") }}
                                        </p>
                                    </div>
                                    <div class="gift-card__input">
                                        <input
                                            :placeholder="
                                                text('gift.ad1311021fa6')
                                            "
                                            type="text"
                                        />
                                        <p class="error">
                                            {{ text("gift.71cc0fd186d4") }}
                                        </p>
                                    </div>
                                </div>
                                <div class="gift-card__input">
                                    <input
                                        :placeholder="text('gift.63e6182bc5d7')"
                                        type="text"
                                    />
                                    <p class="error">
                                        {{ text("gift.71cc0fd186d4") }}
                                    </p>
                                </div>
                                <div class="gift-card__contact__text">
                                    {{ text("gift.8d92125d3105") }} <br />
                                    {{ text("gift.72661306f92f") }}
                                </div>
                                <div class="gift-card__textarea">
                                    <div class="gift-card__textarea__wrap">
                                        <textarea
                                            :placeholder="
                                                text('gift.1964d29b1246')
                                            "
                                            id="giftMessage"
                                            name=""
                                        ></textarea>
                                        <p
                                            class="char-count"
                                            id="gift-card-charCount"
                                        >
                                            {{ text("gift.696a8b3c9a46") }}
                                        </p>
                                    </div>
                                    <p class="error">
                                        {{ text("gift.71cc0fd186d4") }}
                                    </p>
                                </div>
                            </div>
                            <div class="gift-card__content__bottom">
                                <button class="gift-card__prev">
                                    {{ text("gift.dfbd4291d6f0") }}
                                </button>
                                <button class="gift-card__next">
                                    {{ text("gift.8b84e10b8613") }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div
                        class="gift-card__left__main"
                        id="gift-stage-3"
                        v-show="false"
                    >
                        <div class="gift-card__count-stage">
                            {{ text("gift.8414a154ae00") }}
                        </div>
                        <h3 class="gift-card__title">
                            {{ text("gift.6f5926d7ccba") }}
                        </h3>
                        <div class="gift-card__content">
                            <div class="gift-card__delivery">
                                <div class="gift-card__delivery__variant">
                                    <p class="name">
                                        {{ text("gift.8bde7623f6bd") }}
                                    </p>
                                    <div class="content">
                                        <div class="radio-item">
                                            <input
                                                class="radio-input"
                                                id="gift-card__delivery-1"
                                                name="gift-card__delivery"
                                                type="radio"
                                                value="Доставить сразу"
                                            />
                                            <label
                                                class="radio-label"
                                                for="gift-card__delivery-1"
                                                >{{
                                                    text("gift.13bdd6738e68")
                                                }}</label
                                            >
                                        </div>
                                        <div class="radio-item">
                                            <input
                                                class="radio-input"
                                                id="gift-card__delivery-2"
                                                name="gift-card__delivery"
                                                type="radio"
                                                value="Выбрать дату и время"
                                            />
                                            <label
                                                class="radio-label"
                                                for="gift-card__delivery-2"
                                                >{{
                                                    text("gift.151522c14cee")
                                                }}</label
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div class="gift-card__delivery__date-time">
                                    <div class="gift-card__delivery__date">
                                        <button
                                            class="gift-card__delivery__date__btn"
                                            type="button"
                                        >
                                            {{ text("gift.2b1d15dc33c4") }}
                                        </button>
                                        <div
                                            class="gift-card__delivery__date__content"
                                        ></div>
                                    </div>
                                    <div class="gift-card__delivery__time">
                                        <button
                                            class="gift-card__delivery__time__btn"
                                            type="button"
                                        >
                                            {{ text("gift.7c357e7fe87f") }}
                                        </button>
                                        <div
                                            class="gift-card__delivery__time__content active"
                                        >
                                            <div
                                                class="gift-card__delivery__time__content__wrap scrollable"
                                            >
                                                <div class="radio-item">
                                                    <input
                                                        class="radio-input"
                                                        id="gift-time-1"
                                                        name="gift-time"
                                                        type="radio"
                                                        value="00:00"
                                                    />
                                                    <label
                                                        class="radio-label"
                                                        for="gift-time-1"
                                                        >{{
                                                            text(
                                                                "gift.df914b106256",
                                                            )
                                                        }}</label
                                                    >
                                                </div>
                                                <div class="radio-item">
                                                    <input
                                                        class="radio-input"
                                                        id="gift-time-2"
                                                        name="gift-time"
                                                        type="radio"
                                                        value="01:00"
                                                    />
                                                    <label
                                                        class="radio-label"
                                                        for="gift-time-2"
                                                        >{{
                                                            text(
                                                                "gift.01822cd76052",
                                                            )
                                                        }}</label
                                                    >
                                                </div>
                                                <div class="radio-item">
                                                    <input
                                                        class="radio-input"
                                                        id="gift-time-3"
                                                        name="gift-time"
                                                        type="radio"
                                                        value="02:00"
                                                    />
                                                    <label
                                                        class="radio-label"
                                                        for="gift-time-3"
                                                        >{{
                                                            text(
                                                                "gift.f761b066a097",
                                                            )
                                                        }}</label
                                                    >
                                                </div>
                                                <div class="radio-item">
                                                    <input
                                                        class="radio-input"
                                                        id="gift-time-4"
                                                        name="gift-time"
                                                        type="radio"
                                                        value="02:00"
                                                    />
                                                    <label
                                                        class="radio-label"
                                                        for="gift-time-4"
                                                        >{{
                                                            text(
                                                                "gift.9b17f885d74d",
                                                            )
                                                        }}</label
                                                    >
                                                </div>
                                                <div class="radio-item">
                                                    <input
                                                        class="radio-input"
                                                        id="gift-time-5"
                                                        name="gift-time"
                                                        type="radio"
                                                        value="02:00"
                                                    />
                                                    <label
                                                        class="radio-label"
                                                        for="gift-time-5"
                                                        >{{
                                                            text(
                                                                "gift.762686f7d8db",
                                                            )
                                                        }}</label
                                                    >
                                                </div>
                                                <div class="radio-item">
                                                    <input
                                                        class="radio-input"
                                                        id="gift-time-6"
                                                        name="gift-time"
                                                        type="radio"
                                                        value="02:00"
                                                    />
                                                    <label
                                                        class="radio-label"
                                                        for="gift-time-6"
                                                        >{{
                                                            text(
                                                                "gift.80a657b43165",
                                                            )
                                                        }}</label
                                                    >
                                                </div>
                                                <div class="radio-item">
                                                    <input
                                                        class="radio-input"
                                                        id="gift-time-7"
                                                        name="gift-time"
                                                        type="radio"
                                                        value="02:00"
                                                    />
                                                    <label
                                                        class="radio-label"
                                                        for="gift-time-7"
                                                        >{{
                                                            text(
                                                                "gift.46d8aeec1c42",
                                                            )
                                                        }}</label
                                                    >
                                                </div>
                                                <div class="radio-item">
                                                    <input
                                                        class="radio-input"
                                                        id="gift-time-8"
                                                        name="gift-time"
                                                        type="radio"
                                                        value="02:00"
                                                    />
                                                    <label
                                                        class="radio-label"
                                                        for="gift-time-8"
                                                        >{{
                                                            text(
                                                                "gift.4b33ff21f136",
                                                            )
                                                        }}</label
                                                    >
                                                </div>
                                                <div class="radio-item">
                                                    <input
                                                        class="radio-input"
                                                        id="gift-time-9"
                                                        name="gift-time"
                                                        type="radio"
                                                        value="02:00"
                                                    />
                                                    <label
                                                        class="radio-label"
                                                        for="gift-time-9"
                                                        >{{
                                                            text(
                                                                "gift.17f8f251d9a1",
                                                            )
                                                        }}</label
                                                    >
                                                </div>
                                                <div class="radio-item">
                                                    <input
                                                        class="radio-input"
                                                        id="gift-time-10"
                                                        name="gift-time"
                                                        type="radio"
                                                        value="02:00"
                                                    />
                                                    <label
                                                        class="radio-label"
                                                        for="gift-time-10"
                                                        >{{
                                                            text(
                                                                "gift.b2f944011ca4",
                                                            )
                                                        }}</label
                                                    >
                                                </div>
                                                <div class="radio-item">
                                                    <input
                                                        class="radio-input"
                                                        id="gift-time-11"
                                                        name="gift-time"
                                                        type="radio"
                                                        value="02:00"
                                                    />
                                                    <label
                                                        class="radio-label"
                                                        for="gift-time-11"
                                                        >{{
                                                            text(
                                                                "gift.b6ac7d6e4c0e",
                                                            )
                                                        }}</label
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="gift-card__content__bottom">
                                <button class="gift-card__prev">
                                    {{ text("gift.dfbd4291d6f0") }}
                                </button>
                                <button class="gift-card__next">
                                    {{ text("gift.8b84e10b8613") }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div
                        class="gift-card__left__main gift-card__left__final"
                        id="gift-stage-4"
                        v-show="false"
                    >
                        <div class="gift-card__count-stage">
                            {{ text("gift.fe9f420ccb6d") }}
                        </div>
                        <h3 class="gift-card__title">
                            {{ text("gift.aa35556640cf") }}
                        </h3>
                        <div class="gift-card__content">
                            <div class="gift-card__final">
                                <div class="row">
                                    <p>
                                        {{ text("gift.d338e2e932bb") }}
                                        <span>{{
                                            text("gift.768f036a9efe")
                                        }}</span>
                                    </p>
                                </div>
                                <div class="row">
                                    <p>
                                        {{ text("gift.25929e789314") }}
                                        <span>{{
                                            text("gift.265b75a7b840")
                                        }}</span>
                                    </p>
                                </div>
                                <div class="row">
                                    <p>
                                        {{ text("gift.e43bf179e0f7") }}
                                        <span>{{
                                            text("gift.ee8bace232a3")
                                        }}</span>
                                    </p>
                                </div>
                                <div class="row-text">
                                    <p>
                                        {{ text("gift.aa2b0788d698") }}
                                    </p>
                                    <div class="text">
                                        {{ text("gift.db9356b304fb") }}
                                    </div>
                                </div>
                            </div>
                            <a
                                :href="text('shared.13934bde708a')"
                                class="gift-card__policy"
                            >
                                {{ text("gift.0c463501f62c") }}
                            </a>
                            <div class="gift-card__content__bottom">
                                <button class="gift-card__prev">
                                    {{ text("gift.dfbd4291d6f0") }}
                                </button>
                                <button class="gift-card__submit">
                                    {{ text("gift.23624d4c9c80") }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-if="unavailable" class="store-status" role="status">
                    {{ text("shared.90ef0b6f8fc0") }}
                </p>
                <div class="gift-card__right">
                    <div class="gift-card__right__text">
                        {{ text("gift.494f4dbe0f3d") }}
                    </div>
                    <img :src="text('shared.90711662c93a')" alt="" />
                </div>
            </div>
        </div>
    </section>
</template>
