<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <section class="article">
        <div class="container">
            <div class="article__wrap">
                <div class="breadcrumb">
                    <ul>
                        <li>
                            <a :href="text('shared.91102ce72030')">
                                {{ text("article.c655cbabd139") }}
                            </a>
                        </li>
                        <li>
                            <a :href="text('shared.ab4909edccb8')">
                                {{ text("article.fcf0951d4295") }}
                            </a>
                        </li>
                        <li>
                            {{ text("article.b015241792d8") }}
                        </li>
                    </ul>
                </div>
                <div class="article__main">
                    <div class="article__container">
                        <h1 class="article__title">
                            {{ text("article.4d9055263008") }}
                        </h1>
                        <div class="article__content">
                            <div class="article__text">
                                <p>
                                    {{ text("article.8b2ed5083ea4") }}
                                </p>
                            </div>
                            <div class="article__img">
                                <img
                                    :src="text('shared.ff9347031341')"
                                    alt=""
                                />
                            </div>
                            <div class="article__text">
                                <h2>
                                    {{ text("article.8aa1f76f78ee") }}
                                </h2>
                                <p>
                                    {{ text("article.5cb047e474d7") }}
                                </p>
                            </div>
                            <div class="article__text">
                                <h2>
                                    {{ text("article.8ef0bb83baec") }}
                                </h2>
                                <p>
                                    {{ text("article.0b5ed8273318") }}
                                </p>
                                <p>
                                    {{ text("article.aeb80553fbc4") }}
                                </p>
                                <p>
                                    {{ text("article.5cb047e474d7") }}
                                </p>
                            </div>
                            <div class="article__slider">
                                <div class="swiper article__swiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img
                                                :src="
                                                    text('shared.1253cda8bdb4')
                                                "
                                                alt=""
                                            />
                                        </div>
                                        <div class="swiper-slide">
                                            <img
                                                :src="
                                                    text('shared.9eb9d67c078b')
                                                "
                                                alt=""
                                            />
                                        </div>
                                        <div class="swiper-slide">
                                            <img
                                                :src="
                                                    text('shared.e0e6c8508d99')
                                                "
                                                alt=""
                                            />
                                        </div>
                                    </div>
                                    <button class="article__swiper__prev">
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
                                    <button class="article__swiper__next">
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
                                        class="article__swiper__pagination"
                                    ></div>
                                </div>
                            </div>
                            <p class="article__date">
                                {{ text("article.c59b29fa408c") }}
                                <span>{{ text("article.d0f9409eb01c") }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="article-more-list">
        <div class="container">
            <div class="article-more-list__wrap">
                <h3 class="article-more-list__title">
                    {{ text("article.056cff4ee741") }}
                </h3>
                <div class="article-more-list__main">
                    <a
                        :href="text('shared.b4b422dc22fd')"
                        class="blog-list__item"
                    >
                        <div class="blog-list__item__img">
                            <img :src="text('shared.09ce25644a02')" alt="" />
                        </div>
                        <div class="blog-list__item__info">
                            <div class="blog-list__item__left">
                                <p class="blog-list__item__name">
                                    {{ text("article.b7305f5e7efd") }}
                                </p>
                                <div class="blog-list__item__text">
                                    {{ text("article.937454bb256d") }}
                                </div>
                            </div>
                            <p class="blog-list__item__date">
                                {{ text("article.d0f9409eb01c") }}
                            </p>
                        </div>
                    </a>
                    <a
                        :href="text('shared.65910841f918')"
                        class="blog-list__item"
                    >
                        <div class="blog-list__item__img">
                            <img :src="text('shared.50d2e633e522')" alt="" />
                        </div>
                        <div class="blog-list__item__info">
                            <div class="blog-list__item__left">
                                <p class="blog-list__item__name">
                                    {{ text("article.b7305f5e7efd") }}
                                </p>
                                <div class="blog-list__item__text">
                                    {{ text("article.937454bb256d") }}
                                </div>
                            </div>
                            <p class="blog-list__item__date">
                                {{ text("article.d0f9409eb01c") }}
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>
