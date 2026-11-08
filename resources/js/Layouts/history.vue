<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <section class="history-hero">
        <div class="container">
            <div class="history-hero__wrap">
                <h1 class="history-hero__title">
                    {{ text("history.98e35d41c1aa") }}
                </h1>
            </div>
        </div>
    </section>
    <section class="history-textv1">
        <div class="container">
            <div class="history-textv1__wrap">
                <div class="history-textv1__left">
                    <div class="history-textv1__text">
                        {{ text("history.6e7366fa4948") }}
                    </div>
                </div>
                <div class="history-textv1__right">
                    <img :src="text('shared.d8c8e4ae7635')" alt="" />
                </div>
            </div>
        </div>
    </section>
    <section class="history-textv2">
        <div class="container">
            <div class="history-textv2__wrap">
                <div class="history-textv2__left">
                    <img :src="text('shared.b9dcb0ff9cf8')" alt="" />
                </div>
                <div class="history-textv2__right">
                    <div class="history-textv2__text">
                        {{ text("history.29a7b466804c") }}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="history-page-textv3">
        <div class="container">
            <div class="history-page-textv3__wrap">
                <div class="history-page-textv3__subtitle">
                    {{ text("history.1a984a7ad281") }}
                </div>
                <div class="history-page-textv3__text">
                    {{ text("history.36e0ddbf88d0") }}
                </div>
            </div>
        </div>
    </section>
    <section class="history-textv4">
        <div class="container">
            <div class="history-textv4__wrap">
                <div class="history-textv4__left">
                    <div class="history-textv4__text">
                        <p>
                            {{ text("history.270faa82fd52") }}
                        </p>
                        <br />
                        <p>
                            {{ text("history.1db862e4e4c9") }}
                        </p>
                    </div>
                </div>
                <div class="history-textv4__right">
                    <img :src="text('shared.bc18d89cdd59')" alt="" />
                </div>
            </div>
        </div>
    </section>
    <section class="history-page-textv5">
        <div class="container">
            <div class="history-page-textv5__wrap">
                <div class="history-page-textv5__subtitle">
                    {{ text("history.f33bd656c7fa") }}
                </div>
            </div>
        </div>
    </section>
    <section class="history-video section-video">
        <div class="container">
            <div class="history-video__wrap">
                <video
                    class="history-video__video section-video__video"
                    loop=""
                    muted=""
                >
                    <source
                        :src="text('shared.db60773d8525')"
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
    <section class="history-page-textv5">
        <div class="container">
            <div class="history-page-textv5__wrap">
                <div class="history-page-textv5__subtitle">
                    {{ text("history.38e0dd4eb3ba") }}
                </div>
                <a
                    :href="text('shared.4498fa739b83')"
                    class="history-page-textv5__btn"
                >
                    {{ text("history.ffde1bb24608") }}
                </a>
            </div>
        </div>
    </section>
</template>
