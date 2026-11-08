<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <div class="cookie-popup active">
        <div class="cookie-popup__wrap">
            <div class="cookie-popup__text">
                <p>
                    {{ text("shared.455b102e49c0") }}
                </p>
                <p>
                    {{ text("shared.21460d566113") }}
                    <a :href="text('shared.6c1280b6197e')">{{
                        text("shared.6c4c8f006733")
                    }}</a>
                </p>
            </div>
            <div class="cookie-popup__buttons">
                <button class="cookie-popup__decline-btn">
                    {{ text("shared.41ea1de0a7b9") }}
                </button>
                <button class="cookie-popup__accept-btn">
                    {{ text("shared.315ce197b708") }}
                </button>
            </div>
        </div>
    </div>
</template>
