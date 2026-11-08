<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useContent } from "../composables/content";
import { favourite } from "../composables/shop";
import UiIcon from "../Components/UiIcon.vue";
import ProductPurchase from "../Components/ProductPurchase.vue";
const { text } = useContent();
const page = usePage();
const product = computed(() => page.props.product);
const variantId = ref(product.value.variants[0]?.id);
const selected = computed(() =>
    product.value.variants.find((v) => v.id === variantId.value),
);
const gallery = computed(() =>
    product.value.gallery?.length
        ? product.value.gallery
        : [product.value.image].filter(Boolean),
);
</script>
<template>
    <section class="product-hero">
        <div class="container">
            <div class="product-hero__wrap">
                <div class="swiper product-hero__main-slider">
                    <div class="swiper-wrapper">
                        <div
                            :key="image"
                            class="swiper-slide"
                            v-for="image in gallery"
                        >
                            <img :alt="product.name" :src="image" />
                        </div>
                    </div>
                </div>
                <div class="swiper product-hero__thumbs-slider">
                    <div class="swiper-wrapper">
                        <div
                            :key="image"
                            class="swiper-slide"
                            v-for="image in gallery"
                        >
                            <img :alt="product.name" :src="image" />
                        </div>
                    </div>
                </div>
                <div class="product-hero__tags">
                    <span class="tag">
                        {{ text("product-page.cc8e1307f68b") }}
                    </span>
                    <span class="tag">
                        {{ text("product-page.4687617547b3") }}
                    </span>
                </div>
                <button
                    class="product-hero__add-wishlist_mobile"
                    aria-label="В избранное"
                    :aria-pressed="page.props.favourites.includes(product.id)"
                    @click="favourite(product)"
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
                            fill="white"
                        ></path>
                    </svg>
                </button>
                <div class="product-hero__main">
                    <div class="product-hero__content scrollable">
                        <div class="product-hero__pagination"></div>
                        <div class="product-hero__top">
                            <h1 class="product-hero__title">
                                {{ product.name }}
                            </h1>
                            <p class="product-hero__art">
                                {{ text("product-page.a44e57d774e9") }}
                                <span>{{ selected?.sku }}</span>
                            </p>
                            <div class="product-hero__text">
                                {{ product.description }}
                            </div>
                        </div>
                        <div
                            class="product-hero__variants horizontal-scrolling scrollable"
                        >
                            <div
                                :key="variant.id"
                                class="radio-item"
                                v-for="variant in product.variants"
                            >
                                <label
                                    ><input
                                        :value="variant.id"
                                        class="radio-input"
                                        type="radio"
                                        v-model="variantId"
                                    /><span>{{ variant.name }}</span></label
                                >
                            </div>
                        </div>
                        <div class="product-hero__size">
                            <div
                                class="radio-item"
                                v-for="variant in product.variants"
                                :key="variant.id"
                            >
                                <input
                                    class="radio-input"
                                    type="radio"
                                    :id="'size-' + variant.id"
                                    v-model="variantId"
                                    :value="variant.id"
                                /><label
                                    class="radio-label"
                                    :for="'size-' + variant.id"
                                    >{{ variant.name }}</label
                                >
                            </div>
                        </div>
                        <div class="product-hero__buttons">
                            <ProductPurchase
                                :product="product"
                                v-model="variantId"
                            ></ProductPurchase>
                            <button
                                class="product-hero__add-wishlist"
                                aria-label="В избранное"
                                :aria-pressed="
                                    page.props.favourites.includes(product.id)
                                "
                                @click="favourite(product)"
                            >
                                <UiIcon name="heart" />
                            </button>
                        </div>
                        <div class="product-hero__accordion accordion">
                            <div class="accordion__item">
                                <div class="accordion__header">
                                    {{ text("product-page.f30cac68f185") }}
                                </div>
                                <div class="accordion__body">
                                    <div class="accordion__content">
                                        {{ text("product-page.a4633cae265b") }}
                                    </div>
                                </div>
                            </div>
                            <div class="accordion__item">
                                <div class="accordion__header">
                                    {{ text("product-page.e3a78b42e2cf") }}
                                </div>
                                <div class="accordion__body">
                                    <div class="accordion__content">
                                        {{ text("product-page.a4633cae265b") }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="product-video section-video">
        <div class="container">
            <div class="product-video__wrap">
                <video
                    class="product-video__video section-video__video"
                    loop=""
                    muted=""
                >
                    <source
                        :src="text('shared.0deca228b47d')"
                        type="video/mp4"
                    />
                </video>
                <button class="section-video__play-btn">
                    <svg
                        fill="none"
                        height="24"
                        viewBox="0 0 24 24"
                        width="24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M20.4086 9.35258C22.5305 10.5065 22.5305 13.4935 20.4086 14.6474L7.59662 21.6145C5.53435 22.736 3 21.2763 3 18.9671L3 5.0329C3 2.72368 5.53435 1.26402 7.59661 2.38548L20.4086 9.35258Z"
                            fill="white"
                            stroke="white"
                            stroke-width="1.2"
                        ></path>
                    </svg>
                </button>
            </div>
        </div>
    </section>
    <section class="product-slider-section">
        <div class="container">
            <div class="product-slider-section__wrap">
                <div class="product-slider-section__left">
                    <div class="swiper product-slider-section__swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.c39b2c599fd4')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.5802a86ef73c')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.c5f7596b9d17')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.248c90a9c2af')"
                                    alt=""
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-slider-section__right">
                    <div class="product-slider-section__right__content">
                        <div class="product-slider-section__text">
                            {{ text("product-page.ce1b13758695") }}
                        </div>
                        <div class="product-slider-section__bottom">
                            <ul>
                                <li class="active" data-index="0">
                                    <a :href="text('shared.8501fc962803')">{{
                                        text("product-page.20a3bf5989d8")
                                    }}</a>
                                </li>
                                <li data-index="1">
                                    <a :href="text('shared.8ade40b76209')">{{
                                        text("product-page.44a9a836ce25")
                                    }}</a>
                                </li>
                                <li data-index="2">
                                    <a :href="text('shared.21e80de378d7')">{{
                                        text("product-page.2abee0ded94c")
                                    }}</a>
                                </li>
                                <li data-index="3">
                                    <a :href="text('shared.45da4bfd0213')">{{
                                        text("product-page.0d6a49d936ec")
                                    }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="product-tab-section">
        <div class="container">
            <div class="product-tab-section__wrap">
                <div class="product-tab-section__left">
                    <h3 class="product-tab-section__title">
                        {{ text("product-page.b2e0c82cad4c") }}
                    </h3>
                    <div class="product-tab-section__tabs-btn">
                        <button class="active" data-tab="product-tab-1">
                            {{ text("product-page.a0eed89a3b5b") }}
                        </button>
                        <button data-tab="product-tab-2">
                            {{ text("product-page.9442930c7960") }}
                        </button>
                        <button data-tab="product-tab-3">
                            {{ text("product-page.75602466f333") }}
                        </button>
                    </div>
                    <div
                        class="product-tab-section__text active"
                        data-tab="product-tab-1"
                    >
                        <p>{{ product.description }}</p>
                    </div>
                    <div
                        class="product-tab-section__text"
                        data-tab="product-tab-2"
                    >
                        <p>{{ product.ingredients }}</p>
                    </div>
                    <div
                        class="product-tab-section__text"
                        data-tab="product-tab-3"
                    >
                        <p>{{ product.usage }}</p>
                    </div>
                </div>
                <div class="product-tab-section__right">
                    <div
                        class="product-tab-section__img active"
                        data-tab="product-tab-1"
                    >
                        <img :src="text('shared.06569c6ed1e4')" alt="" />
                        <div class="product-tab-section__img__tag">
                            <span>
                                {{ text("product-page.dd013936db10") }}
                            </span>
                            <span>
                                {{ text("product-page.dbba3271bad5") }}
                            </span>
                            <span>
                                {{ text("product-page.8544d3033651") }}
                            </span>
                        </div>
                    </div>
                    <div
                        class="product-tab-section__img"
                        data-tab="product-tab-2"
                    >
                        <img :src="text('shared.aa3c0f21e776')" alt="" />
                        <div class="product-tab-section__img__tag">
                            <span>
                                {{ text("product-page.e443d0d5b9c0") }}
                            </span>
                            <span>
                                {{ text("product-page.d3fce0df105b") }}
                            </span>
                        </div>
                    </div>
                    <div
                        class="product-tab-section__img"
                        data-tab="product-tab-3"
                    >
                        <img :src="text('shared.637d7a211af3')" alt="" />
                        <div class="product-tab-section__img__tag">
                            <span>
                                {{ text("product-page.45dd7a65025e") }}
                            </span>
                            <span>
                                {{ text("product-page.f961d079cd5c") }}
                            </span>
                            <span>
                                {{ text("product-page.45b0f26ea831") }}
                            </span>
                            <span>
                                {{ text("product-page.860b631d3fa3") }}
                            </span>
                            <span>
                                {{ text("product-page.d47e3f37629f") }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="product-page-slider">
        <div class="container">
            <div class="product-page-slider__wrap">
                <div class="product-page-slider__left">
                    <div class="swiper product-page-slider__img__swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.776385bb1147')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.ae4d0f5c5326')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.f1c0d06cf404')"
                                    alt=""
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-page-slider__right">
                    <div class="swiper product-page-slider__main__swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="product-page-slider__main__item">
                                    <img
                                        :src="text('shared.8525a23c7a17')"
                                        alt=""
                                        class="product-page-slider__main__item__img"
                                    />
                                    <div
                                        class="product-page-slider__main__item__top"
                                    >
                                        <p>
                                            {{
                                                text(
                                                    "product-page.e23f48dc3b03",
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="product-page-slider__main__item__bottom"
                                    >
                                        <h3
                                            class="product-page-slider__main__item__name"
                                        >
                                            {{
                                                text(
                                                    "product-page.e90077f8102d",
                                                )
                                            }}
                                        </h3>
                                        <div
                                            class="product-page-slider__main__item__text"
                                        >
                                            {{
                                                text(
                                                    "product-page.a16cf6a2cfea",
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-page-slider__main__item">
                                    <img
                                        :src="text('shared.3cbddd675064')"
                                        alt=""
                                        class="product-page-slider__main__item__img"
                                    />
                                    <div
                                        class="product-page-slider__main__item__top"
                                    >
                                        <p>
                                            {{
                                                text(
                                                    "product-page.e23f48dc3b03",
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="product-page-slider__main__item__bottom"
                                    >
                                        <h3
                                            class="product-page-slider__main__item__name"
                                        >
                                            {{
                                                text(
                                                    "product-page.6b00924f1573",
                                                )
                                            }}
                                        </h3>
                                        <div
                                            class="product-page-slider__main__item__text"
                                        >
                                            {{
                                                text(
                                                    "product-page.a16cf6a2cfea",
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-page-slider__main__item">
                                    <img
                                        :src="text('shared.70633ae4a799')"
                                        alt=""
                                        class="product-page-slider__main__item__img"
                                    />
                                    <div
                                        class="product-page-slider__main__item__top"
                                    >
                                        <p>
                                            {{
                                                text(
                                                    "product-page.e23f48dc3b03",
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="product-page-slider__main__item__bottom"
                                    >
                                        <h3
                                            class="product-page-slider__main__item__name"
                                        >
                                            {{
                                                text(
                                                    "product-page.7c914bcd254b",
                                                )
                                            }}
                                        </h3>
                                        <div
                                            class="product-page-slider__main__item__text"
                                        >
                                            {{
                                                text(
                                                    "product-page.a16cf6a2cfea",
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="product-page-slider__main__swiper__prev">
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
                        <button class="product-page-slider__main__swiper__next">
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
                        <div
                            class="product-page-slider__main__swiper__navigation"
                        >
                            <span></span>
                            {{ text("product-page.e6410bc0d308") }}
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="product-circle-slider">
        <div class="container">
            <div class="product-circle-slider__wrap">
                <div class="product-circle-slider__left">
                    <div class="swiper product-circle-slider__main__swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="product-circle-slider__main__item">
                                    <h3
                                        class="product-circle-slider__main__item__title"
                                    >
                                        {{ text("product-page.83135fa64454") }}
                                    </h3>
                                    <div
                                        class="product-circle-slider__main__item__text"
                                    >
                                        <p>
                                            {{
                                                text(
                                                    "product-page.47f2bc81b465",
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-circle-slider__main__item">
                                    <h3
                                        class="product-circle-slider__main__item__title"
                                    >
                                        {{ text("product-page.f6e5bbaff774") }}
                                    </h3>
                                    <div
                                        class="product-circle-slider__main__item__text"
                                    >
                                        <p>
                                            {{
                                                text(
                                                    "product-page.b71579ad1499",
                                                )
                                            }}
                                        </p>
                                        <p>
                                            {{
                                                text(
                                                    "product-page.176e3202d59a",
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-circle-slider__main__item">
                                    <h3
                                        class="product-circle-slider__main__item__title"
                                    >
                                        {{ text("product-page.313a4a200db9") }}
                                    </h3>
                                    <div
                                        class="product-circle-slider__main__item__text"
                                    >
                                        <p>
                                            {{
                                                text(
                                                    "product-page.1fc59a0aa374",
                                                )
                                            }}
                                            <br />
                                            {{
                                                text(
                                                    "product-page.3808c1e1befe",
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="product-circle-slider__control">
                        <button class="active">
                            {{ text("product-page.c924e001486b") }}
                        </button>
                        <button>
                            {{ text("product-page.f6e5bbaff774") }}
                        </button>
                        <button>
                            {{ text("product-page.5fac4aec0f0c") }}
                        </button>
                    </div>
                </div>
                <div class="product-circle-slider__right">
                    <div class="swiper product-circle-slider__img__swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.75cb77ced713')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.aa61ce8f6166')"
                                    alt=""
                                />
                            </div>
                            <div class="swiper-slide">
                                <img
                                    :src="text('shared.f151854d7c68')"
                                    alt=""
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="product-reviews">
        <div class="container">
            <div class="product-reviews__wrap">
                <div class="product-reviews__header">
                    <h3 class="product-reviews__title">
                        {{ text("product-page.780bc87bf37f") }}
                    </h3>
                    <div class="product-reviews__btn">
                        <button class="product-reviews__swiper__prev">
                            <svg
                                fill="none"
                                height="24"
                                viewBox="0 0 24 24"
                                width="24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M19.4999 13.0001L7.91394 13.0001L12.4139 17.5001L10.9999 18.9141L4.08594 12.0001L10.9999 5.08606L12.4139 6.50006L7.91394 11.0001L19.4999 11.0001V13.0001Z"
                                    fill="#4A4A4A"
                                ></path>
                            </svg>
                        </button>
                        <button class="product-reviews__swiper__next">
                            <svg
                                fill="none"
                                height="24"
                                viewBox="0 0 24 24"
                                width="24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M4.50006 10.9999L16.0861 10.9999L11.5861 6.49994L13.0001 5.08594L19.9141 11.9999L13.0001 18.9139L11.5861 17.4999L16.0861 12.9999L4.50006 12.9999V10.9999Z"
                                    fill="#4A4A4A"
                                ></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="product-reviews__main">
                    <div class="swiper product-reviews__swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="product-reviews__item">
                                    <div class="product-reviews__item__img">
                                        <div
                                            class="swiper product-reviews__item__swiper"
                                        >
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.ab4723df96a3',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.6d82f33c2d25',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.fb249e15a525',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                            </div>
                                            <div
                                                class="product-reviews__item__swiper__pagination"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="product-reviews__item__info">
                                        <p class="product-reviews__item__date">
                                            {{
                                                text(
                                                    "product-page.e3600fdfc7ca",
                                                )
                                            }}
                                        </p>
                                        <h3 class="product-reviews__item__name">
                                            {{
                                                text(
                                                    "product-page.55591fe8484f",
                                                )
                                            }}
                                        </h3>
                                        <div
                                            class="product-reviews__item__text scrollable"
                                        >
                                            {{
                                                text(
                                                    "product-page.cb56840a788f",
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-reviews__item">
                                    <div class="product-reviews__item__img">
                                        <div
                                            class="swiper product-reviews__item__swiper"
                                        >
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.827638648a25',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.1093e17de119',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.4ab1297fb1d0',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                            </div>
                                            <div
                                                class="product-reviews__item__swiper__pagination"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="product-reviews__item__info">
                                        <p class="product-reviews__item__date">
                                            {{
                                                text(
                                                    "product-page.e3600fdfc7ca",
                                                )
                                            }}
                                        </p>
                                        <h3 class="product-reviews__item__name">
                                            {{
                                                text(
                                                    "product-page.55591fe8484f",
                                                )
                                            }}
                                        </h3>
                                        <div
                                            class="product-reviews__item__text scrollable"
                                        >
                                            {{
                                                text(
                                                    "product-page.d99384e263b4",
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-reviews__item">
                                    <div class="product-reviews__item__img">
                                        <div
                                            class="swiper product-reviews__item__swiper"
                                        >
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.5398ca4de906',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.1a4100f3b353',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.322753d8a3d0',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                            </div>
                                            <div
                                                class="product-reviews__item__swiper__pagination"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="product-reviews__item__info">
                                        <p class="product-reviews__item__date">
                                            {{
                                                text(
                                                    "product-page.e3600fdfc7ca",
                                                )
                                            }}
                                        </p>
                                        <h3 class="product-reviews__item__name">
                                            {{
                                                text(
                                                    "product-page.55591fe8484f",
                                                )
                                            }}
                                        </h3>
                                        <div
                                            class="product-reviews__item__text scrollable"
                                        >
                                            {{
                                                text(
                                                    "product-page.d99384e263b4",
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-reviews__item">
                                    <div class="product-reviews__item__img">
                                        <div
                                            class="swiper product-reviews__item__swiper"
                                        >
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.bac6828bb8f1',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.f21d46080075',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.9c45cdccb1ce',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                            </div>
                                            <div
                                                class="product-reviews__item__swiper__pagination"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="product-reviews__item__info">
                                        <p class="product-reviews__item__date">
                                            {{
                                                text(
                                                    "product-page.e3600fdfc7ca",
                                                )
                                            }}
                                        </p>
                                        <h3 class="product-reviews__item__name">
                                            {{
                                                text(
                                                    "product-page.55591fe8484f",
                                                )
                                            }}
                                        </h3>
                                        <div
                                            class="product-reviews__item__text scrollable"
                                        >
                                            {{
                                                text(
                                                    "product-page.d99384e263b4",
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-reviews__item">
                                    <div class="product-reviews__item__img">
                                        <div
                                            class="swiper product-reviews__item__swiper"
                                        >
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.cc72d72c7893',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.e81e8f8e82b4',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.d4ef3e325e7e',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                            </div>
                                            <div
                                                class="product-reviews__item__swiper__pagination"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="product-reviews__item__info">
                                        <p class="product-reviews__item__date">
                                            {{
                                                text(
                                                    "product-page.e3600fdfc7ca",
                                                )
                                            }}
                                        </p>
                                        <h3 class="product-reviews__item__name">
                                            {{
                                                text(
                                                    "product-page.55591fe8484f",
                                                )
                                            }}
                                        </h3>
                                        <div
                                            class="product-reviews__item__text scrollable"
                                        >
                                            {{
                                                text(
                                                    "product-page.d99384e263b4",
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-reviews__item">
                                    <div class="product-reviews__item__img">
                                        <div
                                            class="swiper product-reviews__item__swiper"
                                        >
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.6fd5d9f36177',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.d47c73a2cba8',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.833f652c70bc',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                            </div>
                                            <div
                                                class="product-reviews__item__swiper__pagination"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="product-reviews__item__info">
                                        <p class="product-reviews__item__date">
                                            {{
                                                text(
                                                    "product-page.e3600fdfc7ca",
                                                )
                                            }}
                                        </p>
                                        <h3 class="product-reviews__item__name">
                                            {{
                                                text(
                                                    "product-page.55591fe8484f",
                                                )
                                            }}
                                        </h3>
                                        <div
                                            class="product-reviews__item__text scrollable"
                                        >
                                            {{
                                                text(
                                                    "product-page.d99384e263b4",
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="product-reviews__item">
                                    <div class="product-reviews__item__img">
                                        <div
                                            class="swiper product-reviews__item__swiper"
                                        >
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.f3bc3af348ae',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.fa897da3ac09',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                                <div class="swiper-slide">
                                                    <img
                                                        :src="
                                                            text(
                                                                'shared.2c687d20100b',
                                                            )
                                                        "
                                                        alt=""
                                                    />
                                                </div>
                                            </div>
                                            <div
                                                class="product-reviews__item__swiper__pagination"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="product-reviews__item__info">
                                        <p class="product-reviews__item__date">
                                            {{
                                                text(
                                                    "product-page.e3600fdfc7ca",
                                                )
                                            }}
                                        </p>
                                        <h3 class="product-reviews__item__name">
                                            {{
                                                text(
                                                    "product-page.55591fe8484f",
                                                )
                                            }}
                                        </h3>
                                        <div
                                            class="product-reviews__item__text scrollable"
                                        >
                                            {{
                                                text(
                                                    "product-page.d99384e263b4",
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="more-products">
        <div class="container">
            <div class="more-products__wrap">
                <h3 class="more-products__title">
                    {{ text("product-page.bd1e217e0a43") }} <br />{{
                        text("product-page.5611eaffb7d7")
                    }}
                </h3>
                <div class="more-products__text">
                    {{ text("product-page.48331f064c50") }}
                </div>
                <div class="more-products__main">
                    <div class="swiper more-products__swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="more-products__item">
                                    <div class="more-products__item__hover">
                                        <img
                                            :src="text('shared.747878ee4791')"
                                            alt=""
                                        />
                                        <a
                                            :href="text('shared.5010a797f287')"
                                            class="more-products__item__btn"
                                        >
                                            {{
                                                text(
                                                    "product-page.746152a7710b",
                                                )
                                            }}
                                        </a>
                                    </div>
                                    <p class="more-products__item__badge">
                                        {{ text("product-page.cc8e1307f68b") }}
                                    </p>
                                    <button
                                        class="more-products__item__add-wishlist"
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
                                    <div class="more-products__item__img">
                                        <img
                                            :src="text('shared.37e38080ec9f')"
                                            alt=""
                                        />
                                    </div>
                                    <div class="more-products__item__info">
                                        <div class="more-products__item__left">
                                            <h3
                                                class="more-products__item__name"
                                            >
                                                {{
                                                    text(
                                                        "product-page.9d2a6732f507",
                                                    )
                                                }}
                                            </h3>
                                            <div
                                                class="more-products__item__text"
                                            >
                                                {{
                                                    text(
                                                        "product-page.85ffa661b3c4",
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <p class="more-products__item__price">
                                            {{
                                                text(
                                                    "product-page.71423855163f",
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <a
                                        :href="text('shared.511662cb912e')"
                                        class="more-products__item__btn_mobile"
                                    >
                                        {{ text("product-page.916e6ee709e9") }}
                                    </a>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="more-products__item">
                                    <div class="more-products__item__hover">
                                        <img
                                            :src="text('shared.aafa4e62faec')"
                                            alt=""
                                        />
                                        <a
                                            :href="text('shared.f254e59210f5')"
                                            class="more-products__item__btn"
                                        >
                                            {{
                                                text(
                                                    "product-page.746152a7710b",
                                                )
                                            }}
                                        </a>
                                    </div>
                                    <p class="more-products__item__badge">
                                        {{ text("product-page.cc8e1307f68b") }}
                                    </p>
                                    <button
                                        class="more-products__item__add-wishlist"
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
                                    <div class="more-products__item__img">
                                        <img
                                            :src="text('shared.cec8b7f93ee5')"
                                            alt=""
                                        />
                                    </div>
                                    <div class="more-products__item__info">
                                        <div class="more-products__item__left">
                                            <h3
                                                class="more-products__item__name"
                                            >
                                                {{
                                                    text(
                                                        "product-page.9d2a6732f507",
                                                    )
                                                }}
                                            </h3>
                                            <div
                                                class="more-products__item__text"
                                            >
                                                {{
                                                    text(
                                                        "product-page.85ffa661b3c4",
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <p class="more-products__item__price">
                                            {{
                                                text(
                                                    "product-page.71423855163f",
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <a
                                        :href="text('shared.e0defce763f5')"
                                        class="more-products__item__btn_mobile"
                                    >
                                        {{ text("product-page.916e6ee709e9") }}
                                    </a>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="more-products__item">
                                    <div class="more-products__item__hover">
                                        <img
                                            :src="text('shared.f76f5676d15e')"
                                            alt=""
                                        />
                                        <a
                                            :href="text('shared.a86e929bfc1a')"
                                            class="more-products__item__btn"
                                        >
                                            {{
                                                text(
                                                    "product-page.746152a7710b",
                                                )
                                            }}
                                        </a>
                                    </div>
                                    <p class="more-products__item__badge">
                                        {{ text("product-page.cc8e1307f68b") }}
                                    </p>
                                    <button
                                        class="more-products__item__add-wishlist"
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
                                    <div class="more-products__item__img">
                                        <img
                                            :src="text('shared.11ced2d17861')"
                                            alt=""
                                        />
                                    </div>
                                    <div class="more-products__item__info">
                                        <div class="more-products__item__left">
                                            <h3
                                                class="more-products__item__name"
                                            >
                                                {{
                                                    text(
                                                        "product-page.9d2a6732f507",
                                                    )
                                                }}
                                            </h3>
                                            <div
                                                class="more-products__item__text"
                                            >
                                                {{
                                                    text(
                                                        "product-page.85ffa661b3c4",
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <p class="more-products__item__price">
                                            {{
                                                text(
                                                    "product-page.71423855163f",
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <a
                                        :href="text('shared.51c03a22c8b4')"
                                        class="more-products__item__btn_mobile"
                                    >
                                        {{ text("product-page.916e6ee709e9") }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
