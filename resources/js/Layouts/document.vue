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
                            <a :href="text('shared.249b1b58bb9a')">
                                {{ text("document.c655cbabd139") }}
                            </a>
                        </li>
                        <li>
                            {{ text("document.6ae283fbb756") }}
                        </li>
                    </ul>
                </div>
                <div class="document-page__main">
                    <div class="document-page__left">
                        <ul>
                            <li>
                                <a
                                    :href="text('shared.37b9f9a43c2b')"
                                    class="active"
                                >
                                    {{ text("document.6ae283fbb756") }}
                                </a>
                            </li>
                            <li>
                                <a :href="text('shared.efa45781a8fc')">
                                    {{ text("document.7fc1975bd4ef") }}
                                </a>
                            </li>
                            <li>
                                <a :href="text('shared.b2a8e429f508')">
                                    {{ text("document.4dff06ebe73a") }}
                                </a>
                            </li>
                            <li>
                                <a :href="text('shared.092d529bbcbc')">
                                    {{ text("document.4bdc71fa5c01") }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="document-page__right">
                        <h1>
                            {{ text("document.6ae283fbb756") }}
                        </h1>
                        <h2 style="color: #621617">
                            {{ text("document.88be24aaa7f5") }}
                        </h2>
                        <ul>
                            <li>{{ text("document.79004a9e22d7") }} </li>
                            <li>
                                {{ text("document.e4cc99e5246a") }}
                            </li>
                            <li>
                                {{ text("document.1f081688f529") }}
                            </li>
                        </ul>
                        <p>
                            {{ text("document.3b903e374b5a") }}
                        </p>
                        <p>
                            {{ text("document.2713cf3b90cf") }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
