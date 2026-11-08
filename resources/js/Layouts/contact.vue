<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <section class="contact-page">
        <div class="container">
            <div class="contact-page__wrap">
                <div class="contact-page__left">
                    <h1 class="contact-page__title">
                        {{ text("contact.f277cfa80cb6") }}
                    </h1>
                    <div class="contact-page__contact-list">
                        <a :href="text('shared.9080af7964fd')" class="tel">
                            {{ text("contact.c8ea0dad440d") }}
                        </a>
                        <a :href="text('shared.05c00c66c05c')" class="mail">
                            {{ text("contact.21885abfb6f6") }}
                        </a>
                        <a :href="text('shared.75021f7c4e9c')" class="telegram">
                            {{ text("contact.eba532c8c705") }}
                        </a>
                        <a :href="text('shared.978c670b7733')" class="vk">
                            {{ text("contact.f5032e266762") }}
                        </a>
                    </div>
                    <div class="contact-page__text">
                        <p>
                            {{ text("contact.a704b0db2698") }}
                        </p>
                        <br />
                        <p>
                            {{ text("contact.209d9580b950") }} <br />
                            {{ text("contact.ea3a8302b55a") }}
                        </p>
                    </div>
                </div>
                <div class="contact-page__right">
                    <img :src="text('shared.4718a5a0f669')" alt="" />
                </div>
            </div>
        </div>
    </section>
</template>
