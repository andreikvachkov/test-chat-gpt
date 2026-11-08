<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <div class="running_line sale_popup__open">
        <div class="running_line__row">
            <div class="running_line__row__items marquee">
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
            </div>
            <div aria-hidden="true" class="running_line__row__items marquee">
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
                <div class="running_line__item">
                    {{ text("shared.7f606532cbc5") }}
                </div>
            </div>
        </div>
    </div>
</template>
