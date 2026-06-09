(function () {
  'use strict';

  if (window.cymaSectionAnimationsInit) {
    return;
  }
  window.cymaSectionAnimationsInit = true;

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }

  var EXCLUDED_CLASSES = [
    'footer',
    'section-spacer',
    'section-49',
    'section-3',
    'head-banner',
  ];

  function shouldAnimate(el) {
    if (!el || el.tagName !== 'SECTION') {
      return false;
    }

    for (var i = 0; i < EXCLUDED_CLASSES.length; i++) {
      if (el.classList.contains(EXCLUDED_CLASSES[i])) {
        return false;
      }
    }

    if (el.closest('.w-nav, .navbar, nav, .navbar-logo-left-2, .section-3')) {
      return false;
    }

    if (!el.textContent.trim() && el.children.length === 0) {
      return false;
    }

    return true;
  }

  function prepareSection(el) {
    el.style.removeProperty('opacity');
    el.style.removeProperty('transform');
  }

  function isInViewport(el) {
    var rect = el.getBoundingClientRect();
    return rect.top < window.innerHeight * 0.9 && rect.bottom > window.innerHeight * 0.1;
  }

  function initSectionAnimations() {
    var blocks = Array.prototype.filter.call(
      document.querySelectorAll('main section, body > section'),
      shouldAnimate
    );

    if (!blocks.length) {
      return;
    }

    blocks.forEach(function (block, index) {
      prepareSection(block);
      block.classList.add('cyma-reveal');
      block.style.setProperty('--cyma-reveal-delay', Math.min(index * 0.04, 0.2) + 's');

      if (isInViewport(block)) {
        block.classList.add('is-visible');
      }
    });

    if (!('IntersectionObserver' in window)) {
      blocks.forEach(function (block) {
        block.classList.add('is-visible');
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }

          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        });
      },
      {
        threshold: 0.1,
        rootMargin: '0px 0px -6% 0px',
      }
    );

    blocks.forEach(function (block) {
      if (!block.classList.contains('is-visible')) {
        observer.observe(block);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSectionAnimations);
  } else {
    initSectionAnimations();
  }
})();
