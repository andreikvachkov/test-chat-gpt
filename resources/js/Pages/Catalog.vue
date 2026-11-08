<script setup>
import { useContent } from "../composables/content";
const { text } = useContent();
import { usePage, router, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import ProductCard from "../Components/ProductCard.vue";
const page = usePage();
const filters = ref({
    category: "",
    q: "",
    sort: "popular",
    ...page.props.filters,
});
function apply() {
    router.get("/catalog", filters.value, { preserveScroll: true });
}
</script>
<template>
    <section class="catalog-hero">
        <div class="container">
            <div class="catalog-hero__wrap">
                <h1 class="catalog-hero__title">
                    {{
                        page.props.favouritesPage
                            ? "Избранное"
                            : "kamali — красота со вкусом"
                    }}
                </h1>
                <div class="catalog-hero__text">
                    {{ text("shared.3965d19d62b2") }}
                </div>
            </div>
        </div>
    </section>
    <section class="catalog-page">
        <div class="container">
            <div class="catalog-page__wrap">
                <div
                    class="catalog-page__category"
                    v-if="!page.props.favouritesPage"
                >
                    <div class="radio-item">
                        <input
                            class="radio-input"
                            id="category-all"
                            type="radio"
                            value=""
                            v-model="filters.category"
                            @change="apply"
                        /><label for="category-all" class="radio-label">{{
                            text("shared.b03b866af50a")
                        }}</label>
                    </div>
                    <div
                        class="radio-item"
                        v-for="category in page.props.categories"
                        :key="category.id"
                    >
                        <input
                            class="radio-input"
                            :id="'category-' + category.id"
                            type="radio"
                            :value="category.slug"
                            v-model="filters.category"
                            @change="apply"
                        /><label
                            class="radio-label"
                            :for="'category-' + category.id"
                            >{{ category.name }}</label
                        >
                    </div>
                </div>
                <form
                    v-if="!page.props.favouritesPage"
                    class="catalog-page__filter"
                    @submit.prevent="apply"
                >
                    <div class="store-actions">
                        <input
                            aria-label="Поиск"
                            v-model="filters.q"
                            :placeholder="text('shared.9a62cd649e1a')"
                        /><select
                            aria-label="Сортировка"
                            v-model="filters.sort"
                            @change="apply"
                        >
                            <option value="popular">
                                {{ text("shared.d8d0c5062ba9") }}
                            </option>
                            <option value="stock">
                                {{ text("shared.b7401f7ae205") }}
                            </option>
                            <option value="price_asc">
                                {{ text("shared.138821f0601e") }}
                            </option>
                            <option value="price_desc">
                                {{ text("shared.6cfa06dcd59b") }}
                            </option></select
                        ><button type="submit">
                            {{ text("shared.5fc86f4bb6a0") }}
                        </button>
                    </div>
                </form>
                <div class="catalog-page__main">
                    <ProductCard
                        v-for="product in page.props.products.data"
                        :key="product.id"
                        :product="product"
                    />
                </div>
                <p v-if="!page.props.products.data.length" class="store-empty">
                    {{ text("shared.7225525c8847") }}
                </p>
                <nav
                    v-if="page.props.products.last_page > 1"
                    class="store-pagination"
                    aria-label="Страницы каталога"
                >
                    <template
                        v-for="(link, index) in page.props.products.links"
                        :key="index"
                        ><Link
                            v-if="link.url"
                            :href="link.url"
                            :class="{ active: link.active }"
                            >{{
                                index === 0
                                    ? "Назад"
                                    : index ===
                                        page.props.products.links.length - 1
                                      ? "Далее"
                                      : link.label
                            }}</Link
                        ></template
                    >
                </nav>
            </div>
        </div>
    </section>
</template>
