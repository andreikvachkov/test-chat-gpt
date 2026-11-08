<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <section class="document-v2">
        <div class="container">
            <div class="document-v2__wrap">
                <div class="breadcrumb">
                    <ul>
                        <li>
                            <a :href="text('shared.5e221e4b833a')">
                                {{ text("document2.c655cbabd139") }}
                            </a>
                        </li>
                        <li>
                            {{ text("document2.0faeeecc0871") }}
                        </li>
                    </ul>
                </div>
                <div class="document-v2__main">
                    <div class="document-v2__content">
                        <h1>
                            {{ text("document2.6ae283fbb756") }}
                        </h1>
                        <h2 style="color: #621617">
                            {{ text("document2.88be24aaa7f5") }}
                        </h2>
                        <ul>
                            <li>{{ text("document2.79004a9e22d7") }} </li>
                            <li>
                                {{ text("document2.e4cc99e5246a") }}
                            </li>
                            <li>
                                {{ text("document2.1f081688f529") }}
                            </li>
                        </ul>
                        <p>
                            {{ text("document2.3b903e374b5a") }}
                        </p>
                        <p>
                            {{ text("document2.2713cf3b90cf") }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
