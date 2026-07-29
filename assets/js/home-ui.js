(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  function isMobile() {
    return window.innerWidth < 992;
  }

  function bindArrowScroll(mask, leftArrow, rightArrow, amount) {
    if (!mask) {
      return;
    }
    var step = amount || 300;
    if (leftArrow) {
      leftArrow.addEventListener('click', function (e) {
        e.preventDefault();
        mask.scrollBy({ left: -step, behavior: 'smooth' });
      });
    }
    if (rightArrow) {
      rightArrow.addEventListener('click', function (e) {
        e.preventDefault();
        mask.scrollBy({ left: step, behavior: 'smooth' });
      });
    }
  }

  function enableMobileCardScroll() {
    var pairs = [
      {
        slider: document.querySelector('.industry-list .w-slider'),
        mask: document.querySelector('.industry-list .w-slider-mask'),
        left: document.querySelector('.industry-list .w-slider-arrow-left'),
        right: document.querySelector('.industry-list .w-slider-arrow-right'),
        step: 304
      },
      {
        slider: document.querySelector('.slider-30.w-slider'),
        mask: document.querySelector('.slider-30 .w-slider-mask'),
        left: document.querySelector('.slider-30 .w-slider-arrow-left'),
        right: document.querySelector('.slider-30 .w-slider-arrow-right'),
        step: 321
      },
      {
        slider: document.querySelector('.technology-list.w-slider'),
        mask: document.querySelector('.technology-list .mask-11, .technology-list .w-slider-mask'),
        left: document.querySelector('.left-arrow-10'),
        right: document.querySelector('.right-arrow-16'),
        step: 300
      }
    ];

    pairs.forEach(function (pair) {
      if (pair.slider) {
        pair.slider.setAttribute('data-disable-swipe', 'false');
      }
      if (pair.mask) {
        pair.mask.classList.add('cyma-mobile-scroll');
        // Neutralize Webflow absolute slide transforms so native scroll works.
        Array.prototype.forEach.call(pair.mask.querySelectorAll('.w-slide'), function (slide) {
          slide.style.transform = '';
          slide.style.left = '';
          slide.style.position = '';
        });
      }
      bindArrowScroll(pair.mask, pair.left, pair.right, pair.step);
    });
  }

  ready(function () {
    var navbar = document.querySelector('.navbar-logo-left-container.shadow-three.header-992');
    var hamburgerMenu = document.querySelector('.hambuger-menu-main');

    function syncNavbar() {
      if (!navbar) {
        return;
      }
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    }

    function syncHamburger() {
      if (!hamburgerMenu) {
        return;
      }
      hamburgerMenu.style.display = window.innerWidth < 992 ? 'flex' : '';
    }

    window.addEventListener('scroll', syncNavbar, { passive: true });
    window.addEventListener('resize', syncHamburger);
    syncNavbar();
    syncHamburger();

    enableMobileCardScroll();

    // Re-apply after Webflow IX/slider init may reposition slides.
    window.setTimeout(enableMobileCardScroll, 400);
    window.setTimeout(enableMobileCardScroll, 1200);
  });
})();
