(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  function neutralizeSlides(mask) {
    if (!mask) {
      return;
    }
    Array.prototype.forEach.call(mask.querySelectorAll('.w-slide'), function (slide) {
      slide.style.transform = 'none';
      slide.style.left = '';
      slide.style.right = '';
      slide.style.top = '';
      slide.style.position = '';
    });
  }

  function maxScrollLeft(mask) {
    return Math.max(0, mask.scrollWidth - mask.clientWidth);
  }

  function clampScroll(mask) {
    if (!mask) {
      return;
    }
    var max = maxScrollLeft(mask);
    if (mask.scrollLeft > max) {
      mask.scrollLeft = max;
    }
    if (mask.scrollLeft < 0) {
      mask.scrollLeft = 0;
    }
  }

  function bindArrowScroll(mask, leftArrow, rightArrow, amount) {
    if (!mask || mask.dataset.cymaArrowBound === '1') {
      return;
    }
    mask.dataset.cymaArrowBound = '1';

    var step = amount || 300;

    function rebind(arrow, dir) {
      if (!arrow) {
        return null;
      }
      // Drop Webflow listeners so it cannot advance past the last card.
      var clone = arrow.cloneNode(true);
      arrow.parentNode.replaceChild(clone, arrow);
      clone.addEventListener(
        'click',
        function (e) {
          e.preventDefault();
          e.stopPropagation();
          var next = mask.scrollLeft + dir * step;
          var max = maxScrollLeft(mask);
          if (next > max) {
            next = max;
          }
          if (next < 0) {
            next = 0;
          }
          mask.scrollTo({ left: next, behavior: 'smooth' });
        },
        true
      );
      return clone;
    }

    rebind(leftArrow, -1);
    rebind(rightArrow, 1);

    mask.addEventListener(
      'scroll',
      function () {
        clampScroll(mask);
      },
      { passive: true }
    );
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
        left: document.querySelector('.technology-list .left-arrow-10, .left-arrow-10'),
        right: document.querySelector('.technology-list .right-arrow-16, .right-arrow-16'),
        step: 340,
        tech: true
      }
    ];

    pairs.forEach(function (pair) {
      if (pair.slider) {
        pair.slider.setAttribute('data-disable-swipe', 'false');
        // Keep infinite off so Webflow never loops if it re-inits.
        pair.slider.setAttribute('data-infinite', 'false');
      }
      if (pair.mask) {
        pair.mask.classList.add('cyma-mobile-scroll');
        neutralizeSlides(pair.mask);
        if (pair.tech) {
          clampScroll(pair.mask);
        }
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

    // Re-neutralize after Webflow IX/slider init may reposition slides.
    window.setTimeout(enableMobileCardScroll, 400);
    window.setTimeout(enableMobileCardScroll, 1200);

    var techMask = document.querySelector('.technology-list .mask-11, .technology-list .w-slider-mask');
    if (techMask && typeof MutationObserver !== 'undefined') {
      var obs = new MutationObserver(function () {
        neutralizeSlides(techMask);
        clampScroll(techMask);
      });
      obs.observe(techMask, {
        attributes: true,
        attributeFilter: ['style'],
        subtree: true
      });
      window.setTimeout(function () {
        obs.disconnect();
      }, 4000);
    }
  });
})();
