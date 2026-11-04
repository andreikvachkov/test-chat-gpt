

const openPopup = (popup) => {
    popup.classList.add('active');
    document.querySelector('.popup-background').classList.add('active');
    document.body.classList.add('no-scroll');
    lenis.stop();
};

const closePopup = (popup) => {
    popup.classList.remove('active');
    document.querySelector('.popup-background').classList.remove('active');
    document.body.classList.remove('no-scroll');
    lenis.start();
};

document.querySelectorAll('.header__search, .header__search-mobile').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        openPopup(document.querySelector('.search-popup'));
    });
});

document.querySelector('.search-popup__close')?.addEventListener('click', (e) => {
    e.preventDefault();
    closePopup(document.querySelector('.search-popup'));
});

document.querySelectorAll('.account__bonuses-page__cart__info-btn, .account__giftcards__popup-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        openPopup(document.querySelector('.account__popup-info'));
    });
});

document.querySelector('.account__popup-info__close')?.addEventListener('click', (e) => {
    e.preventDefault();
    closePopup(document.querySelector('.account__popup-info'));
});

document.querySelectorAll('.account__order-page-return-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        openPopup(document.querySelector('.account__return-popup'));
    });
});

document.querySelector('.account__return-popup__close')?.addEventListener('click', (e) => {
    e.preventDefault();
    closePopup(document.querySelector('.account__return-popup'));
});


document.querySelectorAll('.account__order-page-item__review-btn, .account__order-page-item__review-btn_mobile').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        openPopup(document.querySelector('.review-popup'));
    });
});

document.querySelector('.review-popup__close')?.addEventListener('click', (e) => {
    e.preventDefault();
    closePopup(document.querySelector('.review-popup'));
});

document.querySelectorAll('.order-registration__bonus__block').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        openPopup(document.querySelector('.bonus-popup'));
    });
});

document.querySelector('.bonus-popup__close')?.addEventListener('click', (e) => {
    e.preventDefault();
    closePopup(document.querySelector('.bonus-popup'));
});


document.querySelectorAll('.header__basket').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        openPopup(document.querySelector('.popup-cart'));
    });
});

document.querySelector('.popup-cart__close')?.addEventListener('click', (e) => {
    e.preventDefault();
    closePopup(document.querySelector('.popup-cart'));
});


document.querySelectorAll('.scrollable').forEach(el => {
    el.addEventListener('wheel', (e) => {
        const delta = e.deltaY;
        const up = delta < 0;
        const scrollTop = el.scrollTop;
        const scrollHeight = el.scrollHeight;
        const offsetHeight = el.offsetHeight;
        const atTop = scrollTop === 0;
        const atBottom = scrollTop + offsetHeight >= scrollHeight - 1;

        if ((up && !atTop) || (!up && !atBottom)) {
            e.stopPropagation();
        }
    }, { passive: false });
});

const scrollables = document.querySelectorAll('.horizontal-scrolling');

scrollables.forEach(scrollable => {
    scrollable.addEventListener('wheel', function (e) {
        const delta = e.deltaY;

        const atStart = scrollable.scrollLeft === 0;
        const atEnd = scrollable.scrollLeft + scrollable.offsetWidth >= scrollable.scrollWidth;

        const scrollingRight = delta > 0;
        const scrollingLeft = delta < 0;

        const canScrollRight = !atEnd && scrollingRight;
        const canScrollLeft = !atStart && scrollingLeft;

        if (canScrollRight || canScrollLeft) {
            e.preventDefault();
            e.stopPropagation();
            scrollable.scrollLeft += delta;
        }
    }, { passive: false });
});


const pairs = [
    { textareaId: "giftMessage", countId: "gift-card-charCount" },
    { textareaId: "cdek-courierMessage", countId: "cdek-courier-charCount" },
    { textareaId: "courierMessage", countId: "courier-charCount" },
    { textareaId: "return-reasonMessage", countId: "return-reason-charCount" },
    { textareaId: "review-popupMessage", countId: "review-popup-charCount" }
];

pairs.forEach(pair => {
    const textarea = document.getElementById(pair.textareaId);
    const charCount = document.getElementById(pair.countId);

    if (textarea && charCount) {
        textarea.addEventListener("input", function () {
            const currentLength = textarea.value.length;
            charCount.textContent = `${currentLength} из 300`;
        });
    }
});




const deliveryDateBlock = document.querySelector('.gift-card__delivery__date-time');
const radio1 = document.getElementById('gift-card__delivery-1');
const radio2 = document.getElementById('gift-card__delivery-2');

if (deliveryDateBlock && radio1 && radio2) {
    radio1.addEventListener('change', () => {
        if (radio1.checked) deliveryDateBlock.style.display = 'none';
    });

    radio2.addEventListener('change', () => {
        if (radio2.checked) deliveryDateBlock.style.display = 'grid';
    });
}
const btnCookies = document.querySelectorAll('.cookie-popup__accept-btn, .cookie-popup__decline-btn');
btnCookies.forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        document.querySelector('.cookie-popup').classList.remove('active');
    });
});

const btn = document.querySelector('.catalog-page__filter__type-btn');
const btnClose = document.querySelector('.catalog-page__filter__type-container__close');
const menu = document.querySelector('.catalog-page__filter__type-container');

const sortBtn = document.querySelector('.catalog-page__filter__sort-btn');
const sortMenu = document.querySelector('.catalog-page__filter__sort-container');
const sortRadios = document.querySelectorAll('input[name="product-sort"]');

if (btn) {
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        btn.classList.toggle('active');
        menu.classList.toggle('active');
    });
}

if (btnClose) {
    btnClose.addEventListener('click', function (e) {
        e.stopPropagation();
        btn.classList.remove('active');
        menu.classList.remove('active');
    });
}

if (sortBtn) {
    sortBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        sortBtn.classList.toggle('active');
        sortMenu.classList.toggle('active');

        if (isMobile) {
            document.querySelector('.popup-background').classList.toggle('active');
        }
    });
}

document.addEventListener('click', function (e) {
    if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
        btn.classList.remove('active');
        menu.classList.remove('active');
    }

    if (sortBtn && sortMenu && !sortBtn.contains(e.target) && !sortMenu.contains(e.target)) {
        sortBtn.classList.remove('active');
        sortMenu.classList.remove('active');

        if (isMobile) {
            const popupBg = document.querySelector('.popup-background');
            if (popupBg) {
                popupBg.classList.remove('active');
            }
        }
    }
});

sortRadios.forEach(radio => {
    radio.addEventListener('change', () => {
        sortBtn.textContent = radio.value;
        sortBtn.classList.remove('active');
        sortMenu.classList.remove('active');

        if (isMobile) {
            document.querySelector('.popup-background').classList.remove('active');
        }
    });
});


let thankyouSwiper = null;

function initThankyouSwiper() {
    const isMobile = window.innerWidth <= 768;

    if (isMobile && !thankyouSwiper) {
        thankyouSwiper = new Swiper('.thankyou-page__order__swiper', {
            slidesPerView: 'auto',
            loop: false,
            spaceBetween: 16,
            speed: 600,
        });
    } else if (!isMobile && thankyouSwiper) {
        thankyouSwiper.destroy(true, true);
        thankyouSwiper = null;
    }
}

initThankyouSwiper();

window.addEventListener('resize', initThankyouSwiper);


const category__swiper = new Swiper('.category-section__swiper', {
    slidesPerView: 'auto',
    loop: false,
    spaceBetween: 10,

    speed: 600,

    navigation: {
        nextEl: '.category-section__swiper__next',
        prevEl: '.category-section__swiper__prev',
    },

    breakpoints: {
        768: {
            slidesPerView: 3,
            spaceBetween: 20,
        },
    }

});
const article__swiper = new Swiper('.article__swiper', {
    slidesPerView: 1,
    loop: true,
    spaceBetween: 10,

    speed: 600,

    navigation: {
        nextEl: '.article__swiper__next',
        prevEl: '.article__swiper__prev',
    },

    pagination: {
        el: '.article__swiper__pagination',
        clickable: true,
    },

});

const about_ingredients__swiper = new Swiper('.about-ingredients__swiper', {
    slidesPerView: 'auto',
    loop: true,
    spaceBetween: 10,

    speed: 600,

    navigation: {
        nextEl: '.about-ingredients__swiper__next',
        prevEl: '.about-ingredients__swiper__prev',
    },

    breakpoints: {
        768: {
            slidesPerView: 3,
            spaceBetween: 20,
        },
    }

});

const more_products_swiper = new Swiper('.more-products__swiper', {
    slidesPerView: 'auto',
    loop: true,
    spaceBetween: 16,

    speed: 600,

    breakpoints: {
        768: {
            slidesPerView: 3,
            spaceBetween: 20,
        },
    }

});

const aboutSlider = new Swiper('.about-slider-section__swiper', {
    direction: 'vertical',
    slidesPerView: 1,
    loop: true,
    speed: 1000,
});



function initLinkedSliders(options) {
    const {
        imgSwiperSelector,
        mainSwiperSelector,
        prevBtnSelector,
        nextBtnSelector,
        navSelector
    } = options;

    const imgSwiper = new Swiper(imgSwiperSelector, {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: false,
        speed: 600,
    });

    const mainSwiper = new Swiper(mainSwiperSelector, {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: false,
        speed: 600,
        autoHeight: true,

        breakpoints: {
            0: {
                autoHeight: true,
            },
            768: {
                autoHeight: false,
            },
        },

        navigation: {
            nextEl: nextBtnSelector,
            prevEl: prevBtnSelector,
        },

        on: {
            init: function () {
                const navEl = this.el.querySelector(navSelector);
                updateMainSliderNav(this, navEl);
            },
            slideChange: function () {
                const navEl = this.el.querySelector(navSelector);
                updateMainSliderNav(this, navEl);
            }
        }
    });

    imgSwiper.controller.control = mainSwiper;
    mainSwiper.controller.control = imgSwiper;
}

function updateMainSliderNav(swiper, navElement) {
    const current = swiper.realIndex + 1;
    const total = swiper.slides.length;

    if (navElement) {
        navElement.innerHTML = `<span>${current}</span> / <span>${total}</span>`;
    }
}

initLinkedSliders({
    imgSwiperSelector: '.product-slider__img__swiper',
    mainSwiperSelector: '.product-slider__main__swiper',
    prevBtnSelector: '.product-slider__main__swiper__prev',
    nextBtnSelector: '.product-slider__main__swiper__next',
    navSelector: '.product-slider__main__swiper__navigation'
});

initLinkedSliders({
    imgSwiperSelector: '.product-page-slider__img__swiper',
    mainSwiperSelector: '.product-page-slider__main__swiper',
    prevBtnSelector: '.product-page-slider__main__swiper__prev',
    nextBtnSelector: '.product-page-slider__main__swiper__next',
    navSelector: '.product-page-slider__main__swiper__navigation'
});



const thumbsProductSwiper = new Swiper('.product-hero__thumbs-slider', {
    direction: 'vertical',
    spaceBetween: 12,
    slidesPerView: 'auto',
    freeMode: true,
    watchSlidesProgress: true,
});

const mainProductSwiper = new Swiper('.product-hero__main-slider', {
    slidesPerView: 1,
    spaceBetween: 20,
    speed: 600,
    pagination: {
        el: '.product-hero__pagination',
        clickable: true,
        type: 'bullets',
    },
    thumbs: {
        swiper: thumbsProductSwiper,
    },
    breakpoints: {
        0: {
            allowTouchMove: true,
            simulateTouch: true,
            thumbs: { swiper: null },
        },
        769: {
            allowTouchMove: false,
            simulateTouch: false,
            thumbs: { swiper: thumbsProductSwiper },
        }
    },
    on: {
        init: function () {
            const activeSlide = this.slides[this.activeIndex];
            const video = activeSlide.querySelector('video');
            if (video) {
                video.play();
            }
        },

        slideChange: function () {
            const videos = document.querySelectorAll('.product-hero__main-slider video');
            videos.forEach(video => {
                video.pause();
                video.currentTime = 0;
            });

            const activeSlide = this.slides[this.activeIndex];
            const video = activeSlide.querySelector('video');
            if (video) {
                video.play();
            }
        }
    }
});

mainProductSwiper.init();

document.querySelectorAll('.product-hero__thumbs-slider .swiper-slide').forEach((thumbSlide, index) => {
    thumbSlide.addEventListener('mouseenter', () => {
        mainProductSwiper.slideTo(index);
    });
});

const product_circle_slider_main = new Swiper('.product-circle-slider__main__swiper', {
    slidesPerView: 1,
    spaceBetween: 20,
    allowTouchMove: false,
    simulateTouch: false,
    autoHeight: true,
    speed: 700,
    breakpoints: {
        768: {
            autoHeight: false,
        },
    }
});
const product_circle_slider_img = new Swiper('.product-circle-slider__img__swiper', {
    slidesPerView: 1,
    spaceBetween: 20,
    allowTouchMove: false,
    simulateTouch: false,
    speed: 700
});

product_circle_slider_main.controller.control = product_circle_slider_img;
product_circle_slider_img.controller.control = product_circle_slider_main;

const product_circle_buttons = document.querySelectorAll('.product-circle-slider__control button');

document.addEventListener('DOMContentLoaded', () => {
    product_circle_buttons[product_circle_slider_main.activeIndex].classList.add('active');
});

product_circle_buttons.forEach((btn, index) => {
    btn.addEventListener('mouseenter', () => {
        product_circle_slider_main.slideTo(index);
    });
});

product_circle_slider_main.on('slideChange', () => {
    product_circle_buttons.forEach(b => b.classList.remove('active'));
    product_circle_buttons[product_circle_slider_main.activeIndex].classList.add('active');
});



const reviewsSwiper = new Swiper('.product-reviews__swiper', {
    slidesPerView: 'auto',
    spaceBetween: 20,
    speed: 700,
    loop: true,
    allowTouchMove: true,
    navigation: {
        nextEl: '.product-reviews__swiper__next',
        prevEl: '.product-reviews__swiper__prev'
    }
});

document.querySelectorAll('.product-reviews__item__swiper').forEach(swiperEl => {
    const innerSwiper = new Swiper(swiperEl, {
        slidesPerView: 1,
        spaceBetween: 10,
        speed: 700,
        loop: true,
        simulateTouch: true,
        pagination: {
            el: swiperEl.querySelector('.product-reviews__item__swiper__pagination'),
            clickable: true
        }
    });

    swiperEl.addEventListener('touchstart', () => {
        reviewsSwiper.allowTouchMove = false;
    });

    swiperEl.addEventListener('touchend', () => {
        reviewsSwiper.allowTouchMove = true;
    });

    swiperEl.addEventListener('mouseenter', () => {
        reviewsSwiper.allowTouchMove = false;
    });
    swiperEl.addEventListener('mouseleave', () => {
        reviewsSwiper.allowTouchMove = true;
    });
});


document.querySelectorAll('.section-video').forEach(wrap => {
    const video = wrap.querySelector('.section-video__video');
    const playButton = wrap.querySelector('.section-video__play-btn');

    if (!video || !playButton) return;

    playButton.addEventListener('click', (e) => {
        e.stopPropagation();
        if (video.paused) {
            video.play();
            video.classList.add('pointer');
            playButton.classList.add('playing');
        } else {
            video.pause();
            video.classList.remove('pointer');
            playButton.classList.remove('playing');
        }
    });

    video.addEventListener('click', () => {
        if (!video.paused) {
            video.pause();
            video.classList.remove('pointer');
            playButton.classList.remove('playing');
        }
    });
});


document.querySelectorAll('.product-hero__count').forEach(counter => {
    const minusBtn = counter.querySelector('.minus');
    const plusBtn = counter.querySelector('.plus');
    const countDisplay = counter.querySelector('.count');

    if (!minusBtn || !plusBtn || !countDisplay) return;

    let count = 1;

    const updateDisplay = () => {
        countDisplay.textContent = `добавлена ${count} шт.`;
    };

    plusBtn.addEventListener('click', () => {
        count++;
        updateDisplay();
    });

    minusBtn.addEventListener('click', () => {
        if (count > 1) {
            count--;
            updateDisplay();
        }
    });
});



document.querySelectorAll('.product-slider-section').forEach((section, i) => {
    const swiperContainer = section.querySelector('.product-slider-section__swiper');
    const navLinks = section.querySelectorAll('.product-slider-section__bottom li');

    const swiper = new Swiper(swiperContainer, {
        direction: 'vertical',
        slidesPerView: 1,
        loop: false,
        speed: 1000,
        allowTouchMove: false,
        simulateTouch: false,
        on: {
            slideChange: () => {
                updateActiveLink(swiper.activeIndex);
            }
        }
    });

    function updateActiveLink(index) {
        navLinks.forEach(link => link.classList.remove('active'));
        const activeLink = section.querySelector(`.product-slider-section__bottom li[data-index="${index}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }

    navLinks.forEach((item) => {
        const index = parseInt(item.dataset.index);

        item.addEventListener('mouseenter', () => {
            if (window.innerWidth > 768) {
                swiper.slideTo(index);
                updateActiveLink(index);
            }
        });

        item.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                swiper.slideTo(index);
                updateActiveLink(index);
            }
        });
    });
});


const buttons = document.querySelectorAll(".product-tab-section__tabs-btn button");
const texts = document.querySelectorAll(".product-tab-section__text");
const images = document.querySelectorAll(".product-tab-section__img");

buttons.forEach(btn => {
    btn.addEventListener("click", () => {
        const tab = btn.dataset.tab;

        buttons.forEach(b => b.classList.remove("active"));
        texts.forEach(t => t.classList.remove("active"));
        images.forEach(i => i.classList.remove("active"));

        btn.classList.add("active");
        document.querySelector(`.product-tab-section__text[data-tab="${tab}"]`)?.classList.add("active");
        document.querySelector(`.product-tab-section__img[data-tab="${tab}"]`)?.classList.add("active");
    });
});

const orderCityBtn = document.querySelector('.order-registration__delivery__city__btn');
const orderCityContent = document.querySelector('.order-registration__delivery__city__content');
const orderCityInputs = document.querySelectorAll('.order-registration__delivery__city__content .radio-input');
const orderCityBtnText = orderCityBtn ? orderCityBtn.querySelector('.btn-text') : null;

if (orderCityBtn && orderCityContent && orderCityInputs.length) {
    orderCityBtn.addEventListener('click', (e) => {
        e.preventDefault();
        orderCityBtn.classList.toggle('active');
        orderCityContent.classList.toggle('active');
    });

    orderCityInputs.forEach(input => {
        input.addEventListener('change', () => {
            const label = input.closest('.radio-item').querySelector('.radio-label').textContent;

            if (orderCityBtnText) {
                orderCityBtnText.textContent = label;
            } else {
                orderCityBtn.textContent = label;
            }

            orderCityContent.classList.remove('active');
            orderCityBtn.classList.remove('active');

            if (input.checked) {
                orderCityBtn.classList.add('colorOrderCity');
            } else {
                orderCityBtn.classList.remove('colorOrderCity');
            }
        });
    });
}



const deliveryRadios = document.querySelectorAll('input[name="delivery"]');
const deliveryBlocks = document.querySelectorAll(
    '.order-registration__delivery__pochta, ' +
    '.order-registration__delivery__cdek-sam, ' +
    '.order-registration__delivery__cdek-courier, ' +
    '.order-registration__delivery__courier'
);

deliveryRadios.forEach(radio => {
    radio.addEventListener('change', () => {
        deliveryBlocks.forEach(block => block.classList.remove('active'));

        const targetId = radio.dataset.target;
        const targetBlock = document.getElementById(targetId);
        if (targetBlock) {
            targetBlock.classList.add('active');
        }
    });
});

const checkedRadio = document.querySelector('input[name="delivery"]:checked');
if (checkedRadio) {
    checkedRadio.dispatchEvent(new Event('change'));
}

const switchBtn = document.querySelector('.order-registration__gift-card .switch-btn');
const giftCardContent = document.querySelector('.order-registration__gift-card__content');

if (switchBtn && giftCardContent) {
    switchBtn.addEventListener('click', function () {
        this.classList.toggle('switch-on');

        if (this.classList.contains('switch-on')) {
            giftCardContent.classList.add('show');
        } else {
            giftCardContent.classList.remove('show');
        }
    });
}
const switchBtnBonus = document.querySelector('.order-registration__bonus .switch-btn');
const giftCardContentBonus = document.querySelector('.order-registration__bonus__content');

if (switchBtnBonus && giftCardContentBonus) {
    switchBtnBonus.addEventListener('click', function () {
        this.classList.toggle('switch-on');

        if (this.classList.contains('switch-on')) {
            giftCardContentBonus.classList.add('show');
        } else {
            giftCardContentBonus.classList.remove('show');
        }
    });
}





const lkOrdersTabs = document.querySelectorAll('.account__orders-tab');
const lkOrdersLists = document.querySelectorAll('.account__orders-list');

lkOrdersTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        lkOrdersTabs.forEach(t => t.classList.remove('is-active'));
        tab.classList.add('is-active');

        lkOrdersLists.forEach(list => list.classList.remove('is-active'));

        const targetId = tab.dataset.target;
        const targetList = document.getElementById(targetId);
        if (targetList) targetList.classList.add('is-active');
    });
});


const birthdateInput = document.getElementById('birthdate');

if (birthdateInput) {
    birthdateInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '');

        if (v.length >= 5) {
            this.value = v.slice(0, 2) + '/' + v.slice(2, 4) + '/' + v.slice(4, 8);
        } else if (v.length >= 3) {
            this.value = v.slice(0, 2) + '/' + v.slice(2, 4);
        } else {
            this.value = v;
        }
    });
}

document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.previousElementSibling;
        if (input.type === "password") {
            input.type = "text";
            btn.classList.add("active");
        } else {
            input.type = "password";
            btn.classList.remove("active");
        }
    });
});


const codeInputs = document.querySelectorAll('.account-login__step_phone-code__input');

codeInputs.forEach((input, index) => {
    input.addEventListener('input', () => {
        if (input.value.length === 1 && index < codeInputs.length - 1) {
            codeInputs[index + 1].focus();
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === "Backspace" && input.value === '' && index > 0) {
            codeInputs[index - 1].focus();
        }
    });
});




const reasonBlock = document.querySelector(".account__return-popup__return-reason");
if (reasonBlock) {
    const reasonBtn = reasonBlock.querySelector(".return-reason__btn");
    const reasonContent = reasonBlock.querySelector(".return-reason__content");

    reasonBtn.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (reasonContent.classList.contains("open")) {
            reasonBtn.classList.remove("active");
            reasonContent.style.height = reasonContent.scrollHeight + "px";
            requestAnimationFrame(() => {
                reasonContent.style.height = "0px";
            });
            reasonContent.addEventListener("transitionend", function handler() {
                reasonContent.style.display = "none";
                reasonContent.classList.remove("open");
                reasonContent.removeEventListener("transitionend", handler);
            });
        } else {
            reasonBtn.classList.add("active");
            reasonContent.style.display = "block";
            let h = reasonContent.scrollHeight;
            reasonContent.style.height = "0px";

            requestAnimationFrame(() => {
                reasonContent.style.height = h + "px";
            });

            reasonContent.classList.add("open");

            reasonContent.addEventListener("transitionend", function handler() {
                if (reasonContent.classList.contains("open")) {
                    reasonContent.style.height = "auto";
                }
                reasonContent.removeEventListener("transitionend", handler);
            });
        }
    });

    reasonBlock.querySelectorAll(".radio-input").forEach((input) => {
        input.addEventListener("change", () => {
            reasonBtn.textContent = input.value;
            reasonBtn.classList.remove("active");

            reasonContent.style.height = reasonContent.scrollHeight + "px";
            requestAnimationFrame(() => {
                reasonContent.style.height = "0px";
            });
            reasonContent.addEventListener("transitionend", function handler() {
                reasonContent.style.display = "none";
                reasonContent.classList.remove("open");
                reasonContent.removeEventListener("transitionend", handler);
            });
        });
    });

    document.addEventListener("click", (e) => {
        if (!reasonBlock.contains(e.target) && reasonContent.classList.contains("open")) {
            reasonBtn.classList.remove("active");
            reasonContent.style.height = reasonContent.scrollHeight + "px";
            requestAnimationFrame(() => {
                reasonContent.style.height = "0px";
            });
            reasonContent.addEventListener("transitionend", function handler() {
                reasonContent.style.display = "none";
                reasonContent.classList.remove("open");
                reasonContent.removeEventListener("transitionend", handler);
            });
        }
    });
}



const giftCardBlocks = document.querySelectorAll(".account__giftcards__add-card");

giftCardBlocks.forEach((giftCardBlock) => {
    const giftCardAddBtn = giftCardBlock.querySelector(".account__giftcards__add-card__add-btn");
    const giftCardBackBtn = giftCardBlock.querySelector(".account__giftcards__add-card__card__back-btn");
    const giftCardContent = giftCardBlock.querySelector(".account__giftcards__add-card__content");

    giftCardAddBtn.addEventListener("click", (e) => {
        e.preventDefault();
        giftCardContent.classList.add("active");
    });

    giftCardBackBtn.addEventListener("click", (e) => {
        e.preventDefault();
        giftCardContent.classList.remove("active");
    });
});


document.querySelectorAll(".popup-cart__product").forEach(product => {
    const minusBtn = product.querySelector(".popup-cart__product__minus");
    const plusBtn = product.querySelector(".popup-cart__product__plus");
    const valueEl = product.querySelector(".value");

    let count = parseInt(valueEl.textContent.trim(), 10) || 1;

    minusBtn.addEventListener("click", () => {
        if (count > 1) {
            count--;
            valueEl.textContent = count;
        }
    });

    plusBtn.addEventListener("click", () => {
        count++;
        valueEl.textContent = count;
    });
});



const addMoreBtn = document.getElementById("addMoreBtn");
const container = document.getElementById("reviewUploadActions");
const firstInput = document.getElementById("reviewImage");
const uploadField = document.getElementById("reviewUploadField");

function handleFiles(files, triggerField) {
    let hasError = false;

    Array.from(files).forEach(file => {
        if (file.size > 10 * 1024 * 1024 || !['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
            hasError = true;
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            if (!container) return;

            const wrapper = document.createElement("div");
            wrapper.classList.add("review-popup__img-item");

            const img = document.createElement("img");
            img.src = e.target.result;

            const btn = document.createElement("button");
            btn.type = "button";
            btn.classList.add("review-popup__img-remove");
            btn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M12.6673 3.33398L3.33398 12.6673M3.33398 3.33398L12.6673 12.6673" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            `;

            btn.addEventListener("click", () => {
                wrapper.remove();

                if (!container.querySelector(".review-popup__img-item") && uploadField) {
                    uploadField.classList.remove("hidden");
                    container.classList.add("hidden");
                }
            });

            wrapper.appendChild(img);
            wrapper.appendChild(btn);

            container.insertBefore(wrapper, addMoreBtn ? addMoreBtn.nextSibling : null);
        };
        reader.readAsDataURL(file);
    });

    if (hasError && triggerField) {
        const errorMsg = triggerField.querySelector(".error");
        if (errorMsg) {
            errorMsg.style.display = "block";
            setTimeout(() => errorMsg.style.display = "none", 4000);
        }
    }

    if (container && uploadField) {
        container.classList.remove("hidden");
        uploadField.classList.add("hidden");
    }
}

// если есть первый input
if (firstInput) {
    firstInput.addEventListener("change", () => {
        handleFiles(firstInput.files, uploadField);
        firstInput.value = "";
    });
}

// создаём доп. input, только если есть кнопка
if (addMoreBtn) {
    const fileInput = document.createElement("input");
    fileInput.type = "file";
    fileInput.accept = ".jpg,.jpeg,.png";
    fileInput.multiple = true;
    fileInput.hidden = true;
    document.body.appendChild(fileInput);

    addMoreBtn.addEventListener("click", () => fileInput.click());

    fileInput.addEventListener("change", () => {
        handleFiles(fileInput.files, container);
        fileInput.value = "";
    });
}

document.querySelectorAll(".order-registration__gift-card__drodown").forEach(dropdown => {
    const btn = dropdown.querySelector("button");
    const content = dropdown.querySelector(".content");
    const radios = content.querySelectorAll(".radio-input");

    btn.addEventListener("click", () => {
        btn.classList.toggle("active");

        if (content.style.maxHeight) {
            content.style.maxHeight = null;
            content.style.transitionDuration = (content.scrollHeight / 1000) + "s";
        } else {
            const fullHeight = content.scrollHeight;
            content.style.transitionDuration = (fullHeight / 1000) + "s";
            content.style.maxHeight = fullHeight + "px";
        }
    });

    radios.forEach(radio => {
        radio.addEventListener("change", () => {
            const label = radio.nextElementSibling;
            if (!label) return;

            const numberEl = label.querySelector(".number");
            if (numberEl) {
                btn.textContent = numberEl.textContent.trim();
                btn.classList.add("selected");
            }

            btn.classList.remove("active");
            content.style.maxHeight = null;
        });
    });
});

document.querySelectorAll(".order-registration__delivery__cdek-sam__drodown").forEach(dropdown => {
    const btn = dropdown.querySelector("button");
    const content = dropdown.querySelector(".content");
    const radios = content.querySelectorAll(".radio-input");
    const maxHeightRem = 18.75;
    const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
    const maxHeightPx = maxHeightRem * rootFontSize;

    btn.addEventListener("click", () => {
        btn.classList.toggle("active");

        if (content.style.maxHeight && content.style.maxHeight !== "0px") {
            content.style.maxHeight = "0px";
        } else {
            content.style.maxHeight = maxHeightPx + "px";
        }
    });

    radios.forEach(radio => {
        radio.addEventListener("change", () => {
            const label = radio.nextElementSibling;
            if (!label) return;

            const numberEl = label.querySelector(".name");
            if (numberEl) {
                btn.textContent = numberEl.textContent.trim();
                btn.classList.add("selected");
            }

            btn.classList.remove("active");
            content.style.maxHeight = "0px";
        });
    });
});



const deliveryBlock = document.getElementById('delivery-cdek-sam');
if (deliveryBlock) {
    const buttons = deliveryBlock.querySelectorAll('.buttons button');
    const list = deliveryBlock.querySelector('.order-registration__delivery__cdek-sam__list');
    const map = deliveryBlock.querySelector('.order-registration__delivery__cdek-sam__map');

    buttons.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            // убираем актив у всех кнопок
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // показываем нужный блок
            if (index === 0) { // Список
                list.style.display = 'block';
                map.style.display = 'none';
            } else { // Карта
                list.style.display = 'none';
                map.style.display = 'block';
            }
        });
    });

    // по умолчанию показываем список
    list.style.display = 'block';
    map.style.display = 'none';
}