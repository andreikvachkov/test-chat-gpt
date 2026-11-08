<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <section class="history-hero">
        <div class="container">
            <div class="history-hero__wrap">
                <h1 class="history-hero__title">
                    {{ text("blog.49f46ddbc16e") }}
                </h1>
            </div>
        </div>
    </section>
    <section class="blog-list">
        <div class="container">
            <div class="blog-list__wrap">
                <div class="blog-list__filter">
                    <div class="radio-item">
                        <input
                            checked=""
                            class="radio-input"
                            id="blog-filter-1"
                            name="blog-filter"
                            type="radio"
                            value="все"
                        />
                        <label class="radio-label" for="blog-filter-1">{{
                            text("blog.353f7bed600b")
                        }}</label>
                    </div>
                    <div class="radio-item">
                        <input
                            class="radio-input"
                            id="blog-filter-2"
                            name="blog-filter"
                            type="radio"
                            value="события"
                        />
                        <label class="radio-label" for="blog-filter-2">{{
                            text("blog.8b266600e16c")
                        }}</label>
                    </div>
                    <div class="radio-item">
                        <input
                            class="radio-input"
                            id="blog-filter-3"
                            name="blog-filter"
                            type="radio"
                            value="Обзоры"
                        />
                        <label class="radio-label" for="blog-filter-3">{{
                            text("blog.3f65fb7af58b")
                        }}</label>
                    </div>
                    <div class="radio-item">
                        <input
                            class="radio-input"
                            id="blog-filter-4"
                            name="blog-filter"
                            type="radio"
                            value="полезные советы"
                        />
                        <label class="radio-label" for="blog-filter-4">{{
                            text("blog.756fa77785b6")
                        }}</label>
                    </div>
                </div>
                <div class="blog-list__main">
                    <a
                        :href="text('shared.06163b3dc949')"
                        class="blog-list__item"
                    >
                        <div class="blog-list__item__img">
                            <img :src="text('shared.70be4bb295a2')" alt="" />
                        </div>
                        <div class="blog-list__item__info">
                            <div class="blog-list__item__left">
                                <p class="blog-list__item__name">
                                    {{ text("blog.b7305f5e7efd") }}
                                </p>
                                <div class="blog-list__item__text">
                                    {{ text("blog.937454bb256d") }}
                                </div>
                            </div>
                            <p class="blog-list__item__date">
                                {{ text("blog.d0f9409eb01c") }}
                            </p>
                        </div>
                    </a>
                    <a
                        :href="text('shared.3f745bf104bc')"
                        class="blog-list__item"
                    >
                        <div class="blog-list__item__img">
                            <img :src="text('shared.46359cc6da96')" alt="" />
                        </div>
                        <div class="blog-list__item__info">
                            <div class="blog-list__item__left">
                                <p class="blog-list__item__name">
                                    {{ text("blog.b7305f5e7efd") }}
                                </p>
                                <div class="blog-list__item__text">
                                    {{ text("blog.937454bb256d") }}
                                </div>
                            </div>
                            <p class="blog-list__item__date">
                                {{ text("blog.d0f9409eb01c") }}
                            </p>
                        </div>
                    </a>
                    <a
                        :href="text('shared.ee952860a63f')"
                        class="blog-list__item"
                    >
                        <div class="blog-list__item__img">
                            <img :src="text('shared.7b5d39364e82')" alt="" />
                        </div>
                        <div class="blog-list__item__info">
                            <div class="blog-list__item__left">
                                <p class="blog-list__item__name">
                                    {{ text("blog.b7305f5e7efd") }}
                                </p>
                                <div class="blog-list__item__text">
                                    {{ text("blog.937454bb256d") }}
                                </div>
                            </div>
                            <p class="blog-list__item__date">
                                {{ text("blog.d0f9409eb01c") }}
                            </p>
                        </div>
                    </a>
                    <a
                        :href="text('shared.698b1f1b63e3')"
                        class="blog-list__item"
                    >
                        <div class="blog-list__item__img">
                            <img :src="text('shared.4a05b93f642a')" alt="" />
                        </div>
                        <div class="blog-list__item__info">
                            <div class="blog-list__item__left">
                                <p class="blog-list__item__name">
                                    {{ text("blog.b7305f5e7efd") }}
                                </p>
                                <div class="blog-list__item__text">
                                    {{ text("blog.937454bb256d") }}
                                </div>
                            </div>
                            <p class="blog-list__item__date">
                                {{ text("blog.d0f9409eb01c") }}
                            </p>
                        </div>
                    </a>
                    <a
                        :href="text('shared.2916ca2d3f70')"
                        class="blog-list__item"
                    >
                        <div class="blog-list__item__img">
                            <img :src="text('shared.8dcbf9e2f213')" alt="" />
                        </div>
                        <div class="blog-list__item__info">
                            <div class="blog-list__item__left">
                                <p class="blog-list__item__name">
                                    {{ text("blog.b7305f5e7efd") }}
                                </p>
                                <div class="blog-list__item__text">
                                    {{ text("blog.937454bb256d") }}
                                </div>
                            </div>
                            <p class="blog-list__item__date">
                                {{ text("blog.d0f9409eb01c") }}
                            </p>
                        </div>
                    </a>
                    <a
                        :href="text('shared.94bdf57a6941')"
                        class="blog-list__item"
                    >
                        <div class="blog-list__item__img">
                            <img :src="text('shared.378091a236a8')" alt="" />
                        </div>
                        <div class="blog-list__item__info">
                            <div class="blog-list__item__left">
                                <p class="blog-list__item__name">
                                    {{ text("blog.b2b3e87e0834") }}
                                </p>
                                <div class="blog-list__item__text">
                                    {{ text("blog.937454bb256d") }}
                                </div>
                            </div>
                            <p class="blog-list__item__date">
                                {{ text("blog.d0f9409eb01c") }}
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <section class="blog-promo">
        <div class="container">
            <div class="blog-promo__wrap">
                <div class="blog-promo__content">
                    <h3 class="blog-promo__title">
                        {{ text("blog.4630724d183a") }}
                    </h3>
                    <div class="blog-promo__text">
                        {{ text("blog.3a298cdc494a") }}
                    </div>
                    <a
                        :href="text('shared.3aacb05a8836')"
                        class="blog-promo__btn"
                    >
                        {{ text("blog.7cd327b2849a") }}
                    </a>
                </div>
                <div class="blog-promo__code">
                    <div class="blog-promo__code__content">
                        <div class="text">
                            {{ text("blog.44f157ebba12") }}
                        </div>
                        <a :href="text('shared.651866e33fbc')" class="code">
                            {{ text("blog.184d313e8155") }}
                        </a>
                        <a :href="text('shared.6a997af1e55b')" class="copy-btn">
                            {{ text("blog.690dc749d5ef") }}
                        </a>
                    </div>
                    <div class="blog-promo__code__code">
                        <p>
                            {{ text("blog.f1c564d483f7") }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
