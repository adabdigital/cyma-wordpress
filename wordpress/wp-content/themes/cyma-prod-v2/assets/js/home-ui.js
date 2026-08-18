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

    initStatCountUp();
  });

  var STAT_GROUPS = [
    {
      root: '.container.c-1.together',
      items: '._90px.together-color'
    },
    {
      root: '.container-copy-bg.c-1.together',
      items: '._90px-copy.light.purple-text'
    },
    {
      root: '.stat-box._2',
      items: '.text-block-474, .text-block-475'
    },
    {
      root: '#trusted-by-talent .div-block-1177, .section-28 .div-block-1177',
      items: '.div-block-1176 > .text-block-485'
    }
  ];

  var STAT_DURATION_MS = 1600;
  var STAT_STAGGER_MS = 90;

  function parseStat(raw) {
    var text = String(raw || '').replace(/\s+/g, ' ').trim();
    var match = text.match(/^([^\d-]*)(-?\d[\d,]*(?:\.\d+)?)(.*)$/);
    if (!match) {
      return null;
    }
    var numeric = match[2].replace(/,/g, '');
    return {
      original: text,
      prefix: match[1],
      value: parseFloat(numeric),
      suffix: match[3],
      decimals: (numeric.split('.')[1] || '').length,
      commas: match[2].indexOf(',') !== -1
    };
  }

  function formatStat(value, parsed) {
    var rounded = parsed.decimals
      ? value.toFixed(parsed.decimals)
      : String(Math.round(value));
    if (parsed.commas) {
      var parts = rounded.split('.');
      parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      rounded = parts.join('.');
    }
    return parsed.prefix + rounded + parsed.suffix;
  }

  function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
  }

  function prepareStat(el) {
    if (!el || el.dataset.cymaStatReady === '1') {
      return null;
    }

    var parsed = parseStat(el.textContent);
    el.dataset.cymaStatReady = '1';
    el.setAttribute('aria-label', (parsed ? parsed.original : el.textContent).trim());

    if (!parsed) {
      el.classList.add('cyma-stat-acronym');
      return { el: el, parsed: null };
    }

    el.classList.add('cyma-stat-count');
    el.style.minWidth = Math.ceil(el.getBoundingClientRect().width) + 'px';
    el.textContent = formatStat(0, parsed);
    return { el: el, parsed: parsed };
  }

  function playCount(stat, delay) {
    var el = stat.el;
    var parsed = stat.parsed;

    if (!parsed) {
      window.setTimeout(function () {
        el.classList.add('is-visible');
      }, delay);
      return;
    }

    window.setTimeout(function () {
      var start = null;

      function frame(now) {
        if (start === null) {
          start = now;
        }
        var t = Math.min(1, (now - start) / STAT_DURATION_MS);
        el.textContent = formatStat(parsed.value * easeOutCubic(t), parsed);
        if (t < 1) {
          requestAnimationFrame(frame);
        } else {
          el.textContent = parsed.original;
        }
      }

      requestAnimationFrame(frame);
    }, delay);
  }

  function playGroup(root, items) {
    if (!root || root.dataset.cymaStatsPlayed === '1') {
      return;
    }
    root.dataset.cymaStatsPlayed = '1';
    items.forEach(function (stat, index) {
      playCount(stat, index * STAT_STAGGER_MS);
    });
  }

  function initStatCountUp() {
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var groups = [];

    STAT_GROUPS.forEach(function (config) {
      var roots = document.querySelectorAll(config.root);
      Array.prototype.forEach.call(roots, function (root) {
        var nodes = root.querySelectorAll(config.items);
        if (!nodes.length) {
          return;
        }
        var items = [];
        Array.prototype.forEach.call(nodes, function (el) {
          if (reduceMotion) {
            return;
          }
          var prepared = prepareStat(el);
          if (prepared) {
            items.push(prepared);
          }
        });
        if (items.length) {
          groups.push({ root: root, items: items });
        }
      });
    });

    if (!groups.length) {
      return;
    }

    function inView(el) {
      var rect = el.getBoundingClientRect();
      return rect.top < window.innerHeight * 0.88 && rect.bottom > window.innerHeight * 0.12;
    }

    if (!('IntersectionObserver' in window)) {
      groups.forEach(function (group) {
        playGroup(group.root, group.items);
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }
          var group = null;
          for (var i = 0; i < groups.length; i++) {
            if (groups[i].root === entry.target) {
              group = groups[i];
              break;
            }
          }
          if (!group) {
            return;
          }
          playGroup(group.root, group.items);
          observer.unobserve(entry.target);
        });
      },
      {
        threshold: 0.28,
        rootMargin: '0px 0px -8% 0px'
      }
    );

    groups.forEach(function (group) {
      if (inView(group.root)) {
        playGroup(group.root, group.items);
      } else {
        observer.observe(group.root);
      }
    });
  }
})();
