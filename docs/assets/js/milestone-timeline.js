/**
 * About Us — "Milestone That Shaped Us" prev/next arrows.
 * Webflow groups every in-view card as one page, so next/prev is a no-op
 * when four milestones fit. Drive the mask with native card scrolling instead.
 */
(function () {
  'use strict';

  var ROOT = '.section-6 .slider-wrapper.future.w-slider';

  function maxScroll(mask) {
    return Math.max(0, mask.scrollWidth - mask.clientWidth);
  }

  function slideStep(mask) {
    var slide = mask.querySelector('.w-slide');
    if (!slide) {
      return Math.max(280, Math.round(mask.clientWidth * 0.26));
    }
    var style = window.getComputedStyle(slide);
    var margin = parseFloat(style.marginRight) || 0;
    return Math.max(1, slide.getBoundingClientRect().width + margin);
  }

  function neutralize(mask) {
    Array.prototype.forEach.call(mask.querySelectorAll('.w-slide'), function (slide) {
      slide.style.transform = 'none';
      slide.style.left = '';
      slide.style.right = '';
      slide.style.top = '';
      slide.style.position = '';
    });
  }

  function bind() {
    var slider = document.querySelector(ROOT);
    if (!slider || slider.dataset.cymaMilestoneBound === '1') {
      return;
    }

    var mask = slider.querySelector('.w-slider-mask');
    var left = slider.querySelector('.w-slider-arrow-left');
    var right = slider.querySelector('.w-slider-arrow-right');
    if (!mask || !left || !right) {
      return;
    }

    slider.dataset.cymaMilestoneBound = '1';
    slider.classList.add('cyma-milestone-timeline');
    slider.setAttribute('data-disable-swipe', 'false');
    neutralize(mask);

    function rebind(arrow, dir) {
      var clone = arrow.cloneNode(true);
      arrow.parentNode.replaceChild(clone, arrow);
      clone.addEventListener(
        'click',
        function (e) {
          e.preventDefault();
          e.stopPropagation();
          if (typeof e.stopImmediatePropagation === 'function') {
            e.stopImmediatePropagation();
          }

          var max = maxScroll(mask);
          var step = slideStep(mask);
          var next = mask.scrollLeft + dir * step;

          if (max <= 1) {
            return;
          }

          if (dir > 0 && mask.scrollLeft >= max - 2) {
            next = 0;
          } else if (dir < 0 && mask.scrollLeft <= 2) {
            next = max;
          }

          next = Math.max(0, Math.min(max, next));
          if (typeof mask.scrollTo === 'function') {
            mask.scrollTo({ left: next, behavior: 'smooth' });
          } else {
            mask.scrollLeft = next;
          }
        },
        true
      );
      return clone;
    }

    rebind(left, -1);
    rebind(right, 1);

    window.addEventListener('resize', function () {
      neutralize(mask);
    });

    if (typeof MutationObserver !== 'undefined') {
      var obs = new MutationObserver(function () {
        neutralize(mask);
      });
      obs.observe(mask, {
        attributes: true,
        attributeFilter: ['style'],
        subtree: true
      });
      window.setTimeout(function () {
        obs.disconnect();
      }, 4000);
    }
  }

  function start() {
    bind();
    window.setTimeout(bind, 400);
    window.setTimeout(bind, 1200);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
