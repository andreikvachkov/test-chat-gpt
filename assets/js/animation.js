const isMobile = window.innerWidth < 769;

const lenis = new Lenis({
    smooth: true,
    lerp: 0.08,
    wheelMultiplier: 1,
    gestureTarget: window
});
function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
}
requestAnimationFrame(raf);



let lastScrollTop = 0;
const header = document.querySelector('header');

window.addEventListener('scroll', function () {
    const currentScroll = window.scrollY;
    if (currentScroll > 50) {
        header.classList.add('header_sticky');
    } else {
        header.classList.remove('header_sticky');
    }
    if (currentScroll > lastScrollTop && currentScroll > 50) {
        header.classList.remove('show-on-scroll');
        header.classList.add('hide-on-scroll');
    } else if (currentScroll < lastScrollTop) {
        header.classList.remove('hide-on-scroll');
        header.classList.add('show-on-scroll');
    }

    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;

});



const navItems = document.querySelectorAll('.about-slider-section__bottom li');
const contentBlocks = document.querySelectorAll('.about-slider-section__content');

let hoverTimeout;


navItems.forEach((item) => {
    const index = parseInt(item.dataset.index, 10);

    const activateSlide = () => {
        aboutSlider.slideToLoop(index);

        navItems.forEach(i => i.classList.remove('active'));
        contentBlocks.forEach(block => block.classList.remove('active'));

        item.classList.add('active');
        const activeContent = document.querySelector(`.about-slider-section__content[data-content="${index}"]`);
        if (activeContent) {
            activeContent.classList.add('active');
        }
    };

    if (isMobile) {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            activateSlide();
        });
    } else {

        item.addEventListener('click', (e) => {
            e.preventDefault();
        });
        item.addEventListener('mouseenter', (e) => {
            e.preventDefault();
            clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(activateSlide, 150);
        });

        item.addEventListener('mouseleave', () => {
            clearTimeout(hoverTimeout);
        });
    }
});

aboutSlider.on('slideChange', () => {
    const realIndex = aboutSlider.realIndex;

    navItems.forEach((item) => item.classList.remove('active'));

    const activeItem = document.querySelector(`.about-slider-section__bottom li[data-index="${realIndex}"]`);
    if (activeItem) activeItem.classList.add('active');
});



class ItcAccordion {
    constructor(target, config) {
        this._el = typeof target === 'string' ? document.querySelector(target) : target;
        const defaultConfig = {
            alwaysOpen: true,
            duration: 350
        };
        this._config = Object.assign(defaultConfig, config);
        this.addEventListener();
    }
    addEventListener() {
        this._el.addEventListener('click', (e) => {
            const elHeader = e.target.closest('.accordion__header');
            if (!elHeader) {
                return;
            }
            if (!this._config.alwaysOpen) {
                const elOpenItem = this._el.querySelector('.accordion__item_show');
                if (elOpenItem) {
                    elOpenItem !== elHeader.parentElement ? this.toggle(elOpenItem) : null;
                }
            }
            this.toggle(elHeader.parentElement);
        });
    }
    show(el) {
        const elBody = el.querySelector('.accordion__body');
        if (elBody.classList.contains('collapsing') || el.classList.contains('accordion__item_show')) {
            return;
        }
        elBody.style['display'] = 'block';
        const height = elBody.offsetHeight;
        elBody.style['height'] = 0;
        elBody.style['overflow'] = 'hidden';
        elBody.style['transition'] = `height ${this._config.duration}ms ease`;
        elBody.classList.add('collapsing');
        el.classList.add('accordion__item_slidedown');
        elBody.offsetHeight;
        elBody.style['height'] = `${height}px`;
        window.setTimeout(() => {
            elBody.classList.remove('collapsing');
            el.classList.remove('accordion__item_slidedown');
            elBody.classList.add('collapse');
            el.classList.add('accordion__item_show');
            elBody.style['display'] = '';
            elBody.style['height'] = '';
            elBody.style['transition'] = '';
            elBody.style['overflow'] = '';
        }, this._config.duration);
    }
    hide(el) {
        const elBody = el.querySelector('.accordion__body');
        if (elBody.classList.contains('collapsing') || !el.classList.contains('accordion__item_show')) {
            return;
        }
        elBody.style['height'] = `${elBody.offsetHeight}px`;
        elBody.offsetHeight;
        elBody.style['display'] = 'block';
        elBody.style['height'] = 0;
        elBody.style['overflow'] = 'hidden';
        elBody.style['transition'] = `height ${this._config.duration}ms ease`;
        elBody.classList.remove('collapse');
        el.classList.remove('accordion__item_show');
        elBody.classList.add('collapsing');
        window.setTimeout(() => {
            elBody.classList.remove('collapsing');
            elBody.classList.add('collapse');
            elBody.style['display'] = '';
            elBody.style['height'] = '';
            elBody.style['transition'] = '';
            elBody.style['overflow'] = '';
        }, this._config.duration);
    }
    toggle(el) {
        el.classList.contains('accordion__item_show') ? this.hide(el) : this.show(el);
    }
}


document.querySelectorAll('.accordion').forEach((accordion) => {
    new ItcAccordion(accordion, {
        alwaysOpen: false
    });
});