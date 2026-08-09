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

  var REVEAL_DURATION_MS = 1300;

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
    return rect.top < window.innerHeight * 0.92 && rect.bottom > window.innerHeight * 0.08;
  }

  function markRevealed(el) {
    el.classList.add('is-revealed');
    el.style.removeProperty('--cyma-reveal-delay');
  }

  function bindRevealCleanup(el) {
    function onDone(event) {
      if (event.propertyName !== 'transform' && event.propertyName !== 'opacity') {
        return;
      }

      el.removeEventListener('transitionend', onDone);
      markRevealed(el);
    }

    el.addEventListener('transitionend', onDone);
    window.setTimeout(function () {
      el.removeEventListener('transitionend', onDone);
      if (el.classList.contains('is-visible')) {
        markRevealed(el);
      }
    }, REVEAL_DURATION_MS + 200);
  }

  function revealSection(el) {
    if (el.classList.contains('is-visible')) {
      return;
    }

    bindRevealCleanup(el);

    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        el.classList.add('is-visible');
      });
    });
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
      block.style.setProperty('--cyma-reveal-delay', Math.min(index * 0.025, 0.12) + 's');

      if (isInViewport(block)) {
        revealSection(block);
      }
    });

    if (!('IntersectionObserver' in window)) {
      blocks.forEach(function (block) {
        revealSection(block);
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }

          revealSection(entry.target);
          observer.unobserve(entry.target);
        });
      },
      {
        threshold: [0, 0.05, 0.12],
        rootMargin: '0px 0px 12% 0px',
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
