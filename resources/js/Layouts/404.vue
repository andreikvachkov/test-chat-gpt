<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <section class="notfound">
        <div class="container">
            <div class="notfound__wrap">
                <div class="notfound__ico">
                    <svg
                        fill="none"
                        height="368"
                        viewBox="0 0 362 368"
                        width="362"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M362 283.614H319.264V367.5H246.361V283.614H0V242.196L228.764 0.5H319.264V246.914H362V283.614ZM246.361 246.914V68.6571H241.333L72.9028 246.914H246.361Z"
                            fill="white"
                        ></path>
                    </svg>
                    <div class="notfound__ico__center">
                        <div class="notfound__ico__text">
                            {{ text("404.98feb1329096") }} <br />
                            {{ text("404.e62d5de6efe9") }} <br />
                            {{ text("404.966e499fb2a2") }} <br />
                            {{ text("404.411479f1d897") }}
                        </div>
                        <img
                            :src="text('shared.486995140508')"
                            alt=""
                            class="notfound__ico__img"
                        />
                        <img
                            :src="text('shared.991ef14caf3e')"
                            alt=""
                            class="notfound__ico__img_mob"
                        />
                    </div>
                    <svg
                        fill="none"
                        height="368"
                        viewBox="0 0 362 368"
                        width="362"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M362 283.614H319.264V367.5H246.361V283.614H0V242.196L228.764 0.5H319.264V246.914H362V283.614ZM246.361 246.914V68.6571H241.333L72.9028 246.914H246.361Z"
                            fill="white"
                        ></path>
                    </svg>
                </div>
                <div class="notfound__content">
                    <div class="notfound__text">
                        {{ text("404.2038595aae13") }}
                    </div>
                    <a
                        :href="text('shared.efd0f48b3f51')"
                        class="notfound__btn"
                    >
                        {{ text("404.6cdc81498172") }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>
