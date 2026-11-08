<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <section class="about-hero">
        <div class="container">
            <div class="about-hero__wrap">
                <h1 class="about-hero__title">
                    {{ text("about.11376ba3d0d9") }}
                </h1>
                <div class="about-hero__text">
                    {{ text("about.42afdb1633fc") }}
                </div>
            </div>
        </div>
    </section>
    <section class="about-page-text">
        <div class="container">
            <div class="about-page-text__wrap">
                <div class="about-page-text__subtitle">
                    {{ text("about.f5750b1585f6") }}
                </div>
                <div class="about-page-text__text">
                    {{ text("about.d0f7f9f4c67a") }}
                </div>
            </div>
        </div>
    </section>
    <section class="about-video">
        <div class="container">
            <div class="about-video__wrap">
                <img
                    :src="text('shared.c411ae71e91c')"
                    alt=""
                    class="about-video__background"
                />
            </div>
        </div>
    </section>
    <section class="about-page-textv2">
        <div class="container">
            <div class="about-page-textv2__wrap">
                <h3 class="about-page-textv2__title">
                    <span>
                        {{ text("about.25fb1b4dff41") }}
                    </span>
                    {{ text("about.0d05e39eeab1") }}
                </h3>
            </div>
        </div>
    </section>
    <section class="about-slider-section">
        <div class="container">
            <div class="about-slider-section__wrap">
                <div class="about-slider-section__left">
                    <div class="swiper about-slider-section__swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.497782674935')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.546b08443045')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.597ba29211a3')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.152879d8f6fe')"
                                    alt=""
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-slider-section__right">
                    <div class="about-slider-section__right__content">
                        <div
                            class="about-slider-section__content active"
                            data-content="0"
                        >
                            <div class="about-slider-section__text">
                                <p>
                                    {{ text("about.46b591237e5f") }}
                                </p>
                                <br />
                                <p>
                                    {{ text("about.0fc2525cd30e") }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="about-slider-section__content"
                            data-content="1"
                        >
                            <div class="about-slider-section__text">
                                <p>
                                    {{ text("about.3a0c22140e8b") }}
                                </p>
                            </div>
                            <a
                                :href="text('shared.34b705fee058')"
                                class="about-slider-section__btn"
                            >
                                {{ text("about.2328a29e50b7") }}
                            </a>
                        </div>
                        <div
                            class="about-slider-section__content"
                            data-content="2"
                        >
                            <div class="about-slider-section__text">
                                <p>
                                    {{ text("about.6ddf9b13f26e") }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="about-slider-section__content"
                            data-content="3"
                        >
                            <div class="about-slider-section__text">
                                <p>
                                    {{ text("about.132d7c2f239c") }}
                                </p>
                            </div>
                        </div>
                        <div class="about-slider-section__bottom">
                            <ul>
                                <li class="active" data-index="0">
                                    <a :href="text('shared.0edb05180ee7')">{{
                                        text("about.715e86994296")
                                    }}</a>
                                </li>
                                <li data-index="1">
                                    <a :href="text('shared.aed82d77ada3')">{{
                                        text("about.48cf0bfe692d")
                                    }}</a>
                                </li>
                                <li data-index="2">
                                    <a :href="text('shared.1a291973aae8')">{{
                                        text("about.810bea0d2d24")
                                    }}</a>
                                </li>
                                <li data-index="3">
                                    <a :href="text('shared.8a2e8fbfd167')">{{
                                        text("about.bf312953740e")
                                    }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="about-ingredients">
        <div class="container">
            <div class="about-ingredients__wrap">
                <div class="swiper about-ingredients__swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="about-ingredients__item">
                                <div class="about-ingredients__item__img">
                                    <img
                                        :src="text('shared.317b76b0101e')"
                                        alt=""
                                    />
                                </div>
                                <div class="about-ingredients__item__info">
                                    <p class="about-ingredients__item__name">
                                        {{ text("about.c9777d346849") }}
                                    </p>
                                    <div class="about-ingredients__item__text">
                                        {{ text("about.e94d05154605") }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="about-ingredients__item">
                                <div class="about-ingredients__item__img">
                                    <img
                                        :src="text('shared.ae852abe84db')"
                                        alt=""
                                    />
                                </div>
                                <div class="about-ingredients__item__info">
                                    <p class="about-ingredients__item__name">
                                        {{ text("about.e90077f8102d") }}
                                    </p>
                                    <div class="about-ingredients__item__text">
                                        {{ text("about.fff97c725874") }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="about-ingredients__item">
                                <div class="about-ingredients__item__img">
                                    <img
                                        :src="text('shared.e9827ee454b0')"
                                        alt=""
                                    />
                                </div>
                                <div class="about-ingredients__item__info">
                                    <p class="about-ingredients__item__name">
                                        {{ text("about.d34c4f82fd02") }}
                                    </p>
                                    <div class="about-ingredients__item__text">
                                        {{ text("about.4006a0af0562") }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="about-ingredients__item">
                                <div class="about-ingredients__item__img">
                                    <img
                                        :src="text('shared.365f3402f10a')"
                                        alt=""
                                    />
                                </div>
                                <div class="about-ingredients__item__info">
                                    <p class="about-ingredients__item__name">
                                        {{ text("about.c9777d346849") }}
                                    </p>
                                    <div class="about-ingredients__item__text">
                                        {{ text("about.e94d05154605") }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="about-ingredients__swiper__prev">
                        <svg
                            fill="none"
                            height="24"
                            viewBox="0 0 24 24"
                            width="24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M19.5 13L7.914 13L12.414 17.5L11 18.914L4.086 12L11 5.086L12.414 6.5L7.914 11L19.5 11V13Z"
                                fill="#4A4A4A"
                            ></path>
                        </svg>
                    </button>
                    <button class="about-ingredients__swiper__next">
                        <svg
                            fill="none"
                            height="24"
                            viewBox="0 0 24 24"
                            width="24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M4.5 11L16.086 11L11.586 6.5L13 5.086L19.914 12L13 18.914L11.586 17.5L16.086 13L4.5 13V11Z"
                                fill="#4A4A4A"
                            ></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </section>
    <section class="about-page-textv3">
        {{ text("about.363e775eca64") }}
        <div class="container">
            <div class="about-page-textv3__wrap">
                <div class="about-page-textv3__text">
                    {{ text("about.c5ad41118bf3") }}
                </div>
                <a
                    :href="text('shared.3b61fe8bb2a6')"
                    class="about-page-textv3__btn"
                >
                    {{ text("about.45293057df48") }}
                </a>
            </div>
        </div>
    </section>
</template>
