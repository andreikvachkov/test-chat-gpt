<script setup>
import { usePage } from "@inertiajs/vue3";
const page = usePage();
import { ref } from "vue";
import { searchOpen } from "../composables/shop";
const menuOpen = ref(false);
import { useContent } from "../composables/content";
const { text } = useContent();
</script>
<template>
    <header
        :class="{
            'header-active': !['index', 'about', 'lk-login'].includes(
                page.props.template,
            ),
        }"
    >
        <div class="container">
            <div class="header__wrap">
                <div class="col col-nav">
                    <nav :class="{ 'store-mobile-nav-open': menuOpen }">
                        <ul>
                            <li>
                                <a :href="text('shared.75cfca31bf46')">
                                    {{ text("shared.e28737802536") }}
                                </a>
                            </li>
                            <li>
                                <a :href="text('shared.16670487af91')">
                                    {{ text("shared.dcfff3730b8f") }}
                                </a>
                            </li>
                            <li>
                                <a :href="text('shared.067a858cdef2')">
                                    {{ text("shared.fcf0951d4295") }}
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <button
                        class="header__menu-btn"
                        :class="{ open: menuOpen }"
                        aria-label="Меню"
                        :aria-expanded="menuOpen"
                        @click="menuOpen = !menuOpen"
                    ></button>
                    <button
                        class="header__search-mobile"
                        aria-label="Поиск"
                        @click="searchOpen = true"
                    >
                        <svg
                            fill="none"
                            height="18"
                            viewBox="0 0 18 18"
                            width="18"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M7.79861 13.7222C11.1737 13.7222 13.9097 10.9862 13.9097 7.61111C13.9097 4.23604 11.1737 1.5 7.79861 1.5C4.42354 1.5 1.6875 4.23604 1.6875 7.61111C1.6875 10.9862 4.42354 13.7222 7.79861 13.7222Z"
                                stroke="white"
                            ></path>
                            <path
                                d="M12.243 12.0557L16.6875 16.5001"
                                stroke="white"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.4"
                            ></path>
                        </svg>
                    </button>
                </div>
                <div class="col col-logo">
                    <a :href="text('shared.d77d7fc75041')" class="header__logo">
                        <img :src="text('shared.32d357703a70')" alt="" />
                    </a>
                </div>
                <div class="col col-button">
                    <button class="header__search" @click="searchOpen = true">
                        {{ text("shared.30a10289d11a") }}
                    </button>
                    <a
                        :href="text('shared.ef489648b59e')"
                        class="header__basket"
                    >
                        <span class="ico">
                            <svg
                                fill="none"
                                height="18"
                                viewBox="0 0 18 18"
                                width="18"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M7.27671 16.5C8.02983 16.5 8.64034 15.8894 8.64034 15.1363C8.64034 14.3832 8.02983 13.7727 7.27671 13.7727C6.5236 13.7727 5.91309 14.3832 5.91309 15.1363C5.91309 15.8894 6.5236 16.5 7.27671 16.5Z"
                                    stroke="white"
                                ></path>
                                <path
                                    d="M13.6402 16.5C14.3934 16.5 15.0039 15.8894 15.0039 15.1363C15.0039 14.3832 14.3934 13.7727 13.6402 13.7727C12.8871 13.7727 12.2766 14.3832 12.2766 15.1363C12.2766 15.8894 12.8871 16.5 13.6402 16.5Z"
                                    stroke="white"
                                    stroke-width="1.08107"
                                ></path>
                                <path
                                    d="M1.36768 1.5H3.18585L6.37128 11.4999H13.6403"
                                    stroke="white"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.08107"
                                ></path>
                                <path
                                    d="M5.66217 9.22722L3.91309 3.77271H15.283C15.3551 3.77262 15.4261 3.78966 15.4903 3.82243C15.5545 3.85521 15.61 3.90277 15.6521 3.9612C15.6943 4.01963 15.722 4.08725 15.7329 4.15848C15.7438 4.22971 15.7376 4.30251 15.7148 4.37088L14.1994 8.91631C14.1693 9.00679 14.1114 9.08549 14.0341 9.14129C13.9568 9.19708 13.8638 9.22715 13.7685 9.22722H5.66217Z"
                                    stroke="white"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.08107"
                                ></path>
                            </svg>
                        </span>
                        <span class="name">
                            {{ text("shared.683e455e1785") }}
                        </span>
                        <span class="count">{{ page.props.cart.count }}</span>
                    </a>
                    <a
                        :href="text('shared.9e2f48532525')"
                        class="header__wishlist"
                    >
                        <svg
                            fill="none"
                            height="24"
                            viewBox="0 0 24 24"
                            width="24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M8.30982 19.3384L8.77293 18.7485L8.30982 19.3384ZM11.5 5.31675L10.9607 5.83798C11.102 5.98418 11.2967 6.06675 11.5 6.06675C11.7033 6.06675 11.898 5.98418 12.0393 5.83798L11.5 5.31675ZM14.6902 19.3384L15.1533 19.9284L14.6902 19.3384ZM8.30982 19.3384L8.77293 18.7485C7.18364 17.5009 5.43941 16.2779 4.05612 14.7268C2.69856 13.2046 1.75 11.4262 1.75 9.11901H1H0.25C0.25 11.8964 1.41164 14.0152 2.93663 15.7252C4.4359 17.4063 6.34658 18.7507 7.84671 19.9284L8.30982 19.3384ZM1 9.11901H1.75C1.75 6.85904 3.03246 4.96012 4.7889 4.16062C6.49891 3.38225 8.79058 3.59273 10.9607 5.83798L11.5 5.31675L12.0393 4.79551C9.48459 2.15243 6.52626 1.72171 4.16747 2.7954C1.85512 3.84795 0.25 6.29433 0.25 9.11901H1ZM8.30982 19.3384L7.84671 19.9284C8.38492 20.3509 8.96028 20.7994 9.54277 21.1384C10.1251 21.4772 10.7854 21.75 11.5 21.75V21V20.25C11.1646 20.25 10.7749 20.1199 10.2972 19.8419C9.81963 19.564 9.3249 19.1818 8.77293 18.7485L8.30982 19.3384ZM14.6902 19.3384L15.1533 19.9284C16.6534 18.7507 18.5641 17.4063 20.0634 15.7252C21.5884 14.0152 22.75 11.8964 22.75 9.11901H22H21.25C21.25 11.4262 20.3014 13.2046 18.9439 14.7268C17.5606 16.2779 15.8164 17.5009 14.2271 18.7485L14.6902 19.3384ZM22 9.11901H22.75C22.75 6.29433 21.1449 3.84795 18.8325 2.7954C16.4737 1.72171 13.5154 2.15243 10.9607 4.79551L11.5 5.31675L12.0393 5.83798C14.2094 3.59273 16.5011 3.38225 18.2111 4.16062C19.9675 4.96012 21.25 6.85904 21.25 9.11901H22ZM14.6902 19.3384L14.2271 18.7485C13.6751 19.1818 13.1804 19.564 12.7028 19.8419C12.2251 20.1199 11.8354 20.25 11.5 20.25V21V21.75C12.2146 21.75 12.8749 21.4772 13.4572 21.1384C14.0397 20.7994 14.6151 20.3509 15.1533 19.9284L14.6902 19.3384Z"
                                fill="white"
                            ></path>
                        </svg>
                        <span class="count">{{
                            page.props.favourites.length
                        }}</span>
                    </a>
                    <a :href="text('shared.ab52b2a65434')" class="header__lk">
                        <svg
                            fill="none"
                            height="24"
                            viewBox="0 0 24 24"
                            width="24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <circle
                                cx="12"
                                cy="9"
                                r="3"
                                stroke="white"
                                stroke-width="1.2"
                            ></circle>
                            <circle
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="white"
                                stroke-width="1.2"
                            ></circle>
                            <path
                                d="M17.9691 20C17.81 17.1085 16.9247 15 11.9999 15C7.07521 15 6.18991 17.1085 6.03076 20"
                                stroke="white"
                                stroke-linecap="round"
                                stroke-width="1.2"
                            ></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>
</template>
