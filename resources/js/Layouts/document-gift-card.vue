<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <section class="document-page">
        <div class="container">
            <div class="document-page__wrap">
                <div class="breadcrumb">
                    <ul>
                        <li>
                            <a :href="text('shared.04c39434f0b6')">
                                {{ text("document-gift-card.c655cbabd139") }}
                            </a>
                        </li>
                        <li>
                            {{ text("document-gift-card.9f8763ef5082") }}
                        </li>
                    </ul>
                </div>
                <div class="document-page__main">
                    <div class="document-page__left">
                        <ul>
                            <li>
                                <a
                                    :href="text('shared.b4f13bfcacda')"
                                    class="active"
                                >
                                    {{
                                        text("document-gift-card.9f8763ef5082")
                                    }}
                                </a>
                            </li>
                            <li>
                                <a :href="text('shared.04bbf4a0bad8')">
                                    {{
                                        text("document-gift-card.c4f0e7b808a1")
                                    }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="document-page__right">
                        <h1>
                            {{ text("document-gift-card.9f8763ef5082") }}
                        </h1>
                        <h2 style="color: #621617">
                            {{ text("document-gift-card.b9cd64e7c0f9") }}
                        </h2>
                        <p>
                            {{ text("document-gift-card.73fdd0e3c05a") }}
                        </p>
                        <p>
                            {{ text("document-gift-card.e4f495222a13") }}
                        </p>
                        <h2 style="color: #621617">
                            {{ text("document-gift-card.261d35cec24b") }}
                        </h2>
                        <p>
                            {{ text("document-gift-card.a0b8be42882c") }}
                        </p>
                        <p>
                            {{ text("document-gift-card.df2957ee914c") }}
                        </p>
                        <p>
                            {{ text("document-gift-card.fcbaecf68567") }}
                        </p>
                        <h2 style="color: #621617">
                            {{ text("document-gift-card.4c7a7561ca82") }}
                        </h2>
                        <ul>
                            <li>
                                {{ text("document-gift-card.1cbe195d715e") }}
                            </li>
                            <li>
                                {{ text("document-gift-card.d18d960cfa82") }}
                            </li>
                            <li>
                                {{ text("document-gift-card.d18d960cfa82") }}
                            </li>
                        </ul>
                        <p>
                            {{ text("document-gift-card.f45c5a1d235c") }}
                        </p>
                        <ul>
                            <li>
                                {{ text("document-gift-card.9cf8ccd9f8fc") }}
                            </li>
                            <li>
                                {{ text("document-gift-card.b07483ed2a99") }}
                            </li>
                        </ul>
                        <p>
                            {{ text("document-gift-card.badabf02651e") }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
