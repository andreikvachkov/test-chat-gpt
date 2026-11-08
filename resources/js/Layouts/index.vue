<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <section class="hero">
        <div class="container">
            <div class="hero__wrap">
                <h1 class="hero__title">
                    {{ text("index.0f7aca598cdf") }}
                </h1>
                <a :href="text('shared.2cdcf4ab0a25')" class="hero__btn">
                    {{ text("index.565b1ac22a44") }}
                </a>
            </div>
        </div>
    </section>
    <section class="category-section">
        <div class="container">
            <div class="category-section__wrap">
                <div class="swiper category-section__swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="category-section__item">
                                <img
                                    :src="text('shared.8eed921b2af6')"
                                    alt=""
                                    class="default"
                                />
                                <img
                                    :src="text('shared.8768327d2914')"
                                    alt=""
                                    class="hover"
                                />
                                <a
                                    :href="text('shared.fc7cc9ce744a')"
                                    class="category-section__item__btn"
                                >
                                    {{ text("index.f998052616c4") }}
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="category-section__item">
                                <img
                                    :src="text('shared.6bf68d18243f')"
                                    alt=""
                                    class="default"
                                />
                                <img
                                    :src="text('shared.3f1e49816ba7')"
                                    alt=""
                                    class="hover"
                                />
                                <a
                                    :href="text('shared.366b46ba3590')"
                                    class="category-section__item__btn"
                                >
                                    {{ text("index.92a9d65bf539") }}
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="category-section__item">
                                <img
                                    :src="text('shared.7d3442fec100')"
                                    alt=""
                                    class="default"
                                />
                                <img
                                    :src="text('shared.bbf6774c6742')"
                                    alt=""
                                    class="hover"
                                />
                                <a
                                    :href="text('shared.7baf550c87da')"
                                    class="category-section__item__btn"
                                >
                                    {{ text("index.943875716d85") }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <button class="category-section__swiper__prev">
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
                    <button class="category-section__swiper__next">
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
    <section class="promo-section">
        <div class="container">
            <div class="promo-section__wrap">
                <h2 class="promo-section__title">
                    {{ text("index.b36a1c4249fb") }}
                </h2>
                <div class="promo-section__text">
                    {{ text("index.0ca09b5e1a4b") }}
                </div>
                <button class="promo-section__btn">
                    {{ text("index.690dc749d5ef") }}
                </button>
            </div>
        </div>
    </section>
    <section class="product-slider">
        <div class="container">
            <div class="product-slider__wrap">
                <div class="product-slider__left">
                    <div class="swiper product-slider__img__swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.107b97ecc8d8')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.77b86939e268')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.471f73084566')"
                                    alt=""
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-slider__right">
                    <div class="swiper product-slider__main__swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="product-slider__main__item">
                                    <img
                                        :src="text('shared.996f100aee0b')"
                                        alt=""
                                        class="product-slider__main__item__img"
                                    />
                                    <div
                                        class="product-slider__main__item__top"
                                    >
                                        <p
                                            class="product-slider__main__item__top__text"
                                        >
                                            {{ text("index.ff1216e74b52") }}
                                        </p>
                                        <p
                                            class="product-slider__main__item__top__bold-text"
                                        >
                                            {{ text("index.7d7addb2ac6c") }}
                                        </p>
                                    </div>
                                    <div
                                        class="product-slider__main__item__bottom"
                                    >
                                        <h3
                                            class="product-slider__main__item__name"
                                        >
                                            {{ text("index.bcd5bc131498") }}
                                        </h3>
                                        <div
                                            class="product-slider__main__item__text"
                                        >
                                            {{ text("index.20fe0033421f") }}
                                        </div>
                                        <a
                                            :href="text('shared.8a545d97a1ef')"
                                            class="product-slider__main__item__btn"
                                        >
                                            {{ text("index.85d4c84bd60a") }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-slider__main__item">
                                    <img
                                        :src="text('shared.601095191a1e')"
                                        alt=""
                                        class="product-slider__main__item__img"
                                    />
                                    <div
                                        class="product-slider__main__item__top"
                                    >
                                        <p
                                            class="product-slider__main__item__top__text"
                                        >
                                            {{ text("index.ff1216e74b52") }}
                                        </p>
                                        <p
                                            class="product-slider__main__item__top__bold-text"
                                        >
                                            {{ text("index.7d7addb2ac6c") }}
                                        </p>
                                    </div>
                                    <div
                                        class="product-slider__main__item__bottom"
                                    >
                                        <h3
                                            class="product-slider__main__item__name"
                                        >
                                            {{ text("index.bcd5bc131498") }}
                                        </h3>
                                        <div
                                            class="product-slider__main__item__text"
                                        >
                                            {{ text("index.20fe0033421f") }}
                                        </div>
                                        <a
                                            :href="text('shared.c4a58cc400b2')"
                                            class="product-slider__main__item__btn"
                                        >
                                            {{ text("index.85d4c84bd60a") }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-slider__main__item">
                                    <img
                                        :src="text('shared.b0edb58ac1a0')"
                                        alt=""
                                        class="product-slider__main__item__img"
                                    />
                                    <div
                                        class="product-slider__main__item__top"
                                    >
                                        <p
                                            class="product-slider__main__item__top__text"
                                        >
                                            {{ text("index.ff1216e74b52") }}
                                        </p>
                                        <p
                                            class="product-slider__main__item__top__bold-text"
                                        >
                                            {{ text("index.7d7addb2ac6c") }}
                                        </p>
                                    </div>
                                    <div
                                        class="product-slider__main__item__bottom"
                                    >
                                        <h3
                                            class="product-slider__main__item__name"
                                        >
                                            {{ text("index.bcd5bc131498") }}
                                        </h3>
                                        <div
                                            class="product-slider__main__item__text"
                                        >
                                            {{ text("index.20fe0033421f") }}
                                        </div>
                                        <a
                                            :href="text('shared.a247cf3a004e')"
                                            class="product-slider__main__item__btn"
                                        >
                                            {{ text("index.85d4c84bd60a") }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="product-slider__main__swiper__prev">
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
                        <button class="product-slider__main__swiper__next">
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
                        <div class="product-slider__main__swiper__navigation">
                            <span></span> {{ text("index.e6410bc0d308") }}
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="text-section">
        <div class="container">
            <div class="text-section__wrap">
                <div class="text-section__left">
                    <h3 class="text-section__title">
                        {{ text("index.4c4404abe3d8") }}
                    </h3>
                    <img
                        :src="text('shared.ae072a509d2e')"
                        alt=""
                        class="text-section__left__img"
                    />
                    <div class="text-section__content">
                        <div class="text-section__text">
                            <p>
                                {{ text("index.d7ad65b1ce63") }}
                            </p>
                            <br />
                            <p>
                                {{ text("index.c98c53445e9a") }}
                            </p>
                        </div>
                        <a
                            :href="text('shared.08fea1cff80e')"
                            class="text-section__btn"
                        >
                            {{ text("index.9e8cc041441a") }}
                        </a>
                    </div>
                </div>
                <div class="text-section__right">
                    <img
                        :src="text('shared.087718d9390b')"
                        alt=""
                        class="text-section__right__img"
                    />
                </div>
            </div>
        </div>
    </section>
    <section class="textv2-section">
        <div class="container">
            <div class="textv2-section__wrap">
                <div class="textv2-section__content">
                    <h2 class="textv2-section__title">
                        {{ text("index.633485bf7671") }}
                    </h2>
                    <div class="textv2-section__text">
                        {{ text("index.a6006c0ff36a") }}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="textv3-section">
        <div class="container">
            <div class="textv3-section__wrap">
                <div class="textv3-section__left">
                    <img
                        :src="text('shared.fa0003c5ba9b')"
                        alt=""
                        class="textv3-section__left__img"
                    />
                </div>
                <div class="textv3-section__right">
                    <h3 class="textv3-section__title">
                        {{ text("index.fae201bc9c45") }}
                    </h3>
                    <img
                        :src="text('shared.a672bd9ae284')"
                        alt=""
                        class="textv3-section__right__img"
                    />
                    <div class="textv3-section__content">
                        <div class="textv3-section__text">
                            <p>
                                {{ text("index.90483199846d") }}
                            </p>
                        </div>
                        <a
                            :href="text('shared.0f3d67b154ad')"
                            class="textv3-section__btn"
                        >
                            {{ text("index.534ac8e37de1") }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="textv4-section">
        <div class="container">
            <div class="textv4-section__wrap">
                <div class="textv4-section__left">
                    <h3 class="textv4-section__title">
                        {{ text("index.42b856439a57") }}
                    </h3>
                    <div class="textv4-section__content">
                        <div class="textv4-section__text">
                            <p>
                                {{ text("index.aa36cae6ac36") }}
                            </p>
                        </div>
                        <a
                            :href="text('shared.72f2c03d8498')"
                            class="textv4-section__btn"
                        >
                            {{ text("index.b4e64408dc9d") }}
                        </a>
                    </div>
                </div>
                <div class="textv4-section__right">
                    <img
                        :src="text('shared.713594a805df')"
                        alt=""
                        class="textv4-section__right__img"
                    />
                </div>
            </div>
        </div>
    </section>
</template>
