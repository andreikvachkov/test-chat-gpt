<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <footer>
        <div class="container">
            <div class="footer__wrap">
                <a :href="text('shared.8cdd35ba17a3')" class="footer__logo">
                    <img :src="text('shared.07d458a31ecc')" alt="" />
                </a>
                <div class="footer__main">
                    <div class="footer__socies">
                        <p class="name">
                            {{ text("shared.49caf44e2b83") }}
                        </p>
                        <div class="content">
                            <a :href="text('shared.a465baaa9b0d')" class="vk">
                                {{ text("shared.567c3f2131ed") }}
                            </a>
                            <a
                                :href="text('shared.a52ea319ccc5')"
                                class="telegram"
                            >
                                {{ text("shared.413e4d2fd9fb") }}
                            </a>
                        </div>
                    </div>
                    <div class="footer__main__right">
                        <div class="footer__nav">
                            <p class="name">
                                {{ text("shared.8175c63cb191") }}
                            </p>
                            <nav>
                                <ul>
                                    <li>
                                        <a :href="text('shared.b3d8fbab2869')">
                                            {{ text("shared.e28737802536") }}
                                        </a>
                                    </li>
                                    <li>
                                        <a :href="text('shared.d7642631dff1')">
                                            {{ text("shared.dcfff3730b8f") }}
                                        </a>
                                    </li>
                                    <li>
                                        <a :href="text('shared.12a028c25086')">
                                            {{ text("shared.79b2db004a2a") }}
                                        </a>
                                    </li>
                                    <li>
                                        <a :href="text('shared.282e65c58534')">
                                            {{ text("shared.fcf0951d4295") }}
                                        </a>
                                    </li>
                                    <li>
                                        <a :href="text('shared.de40682f1dff')">
                                            {{ text("shared.71b6c7b5271e") }}
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <div class="footer__contact">
                            <p class="name">
                                {{ text("shared.8175c63cb191") }}
                            </p>
                            <div class="content">
                                <a :href="text('shared.36a74c067ff0')">
                                    {{ text("shared.8f9535458fd6") }}
                                </a>
                                <a :href="text('shared.1a77649ed5fd')">
                                    {{ text("shared.21885abfb6f6") }}
                                </a>
                                <a :href="text('shared.f7101b0fff5d')">
                                    {{ text("shared.eba532c8c705") }}
                                </a>
                            </div>
                        </div>
                        <div class="footer__payments">
                            <p class="name">
                                {{ text("shared.cf102af43d39") }}
                            </p>
                            <div class="content">
                                <img
                                    :src="text('shared.3052da01bde7')"
                                    alt=""
                                />
                                <img
                                    :src="text('shared.1f0488ac3465')"
                                    alt=""
                                />
                                <img
                                    :src="text('shared.2b2a33c6000b')"
                                    alt=""
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer__bottom">
                    <div class="footer__bottom__left">
                        <a :href="text('shared.4b709de0ae4f')">
                            {{ text("shared.5b90782977bc") }}
                        </a>
                        <a :href="text('shared.d81b889829f9')">
                            {{ text("shared.4492fd01f6f9") }}
                        </a>
                        <a :href="text('shared.81a2a12440f8')">
                            {{ text("shared.e8b7bc7ad82c") }}
                        </a>
                        <a :href="text('shared.1d2cb4118602')">
                            {{ text("shared.0faeeecc0871") }}
                        </a>
                    </div>
                    <div class="footer__payments__mobile">
                        <p class="name">
                            {{ text("shared.cf102af43d39") }}
                        </p>
                        <div class="content">
                            <img :src="text('shared.7421aba41a61')" alt="" />
                            <img :src="text('shared.5451af1720bc')" alt="" />
                            <img :src="text('shared.1050af76e66c')" alt="" />
                        </div>
                    </div>
                    <div class="footer__bottom__right">
                        <p class="footer__copyright">
                            {{ text("shared.47abad9af679") }}
                        </p>
                        <p>
                            {{ text("shared.e2b73435c31c") }}
                            <a :href="text('shared.c9af13c33911')">{{
                                text("shared.300e75dbac23")
                            }}</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
