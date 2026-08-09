<script type="text/javascript">var $ = window.jQuery;</script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/webflow.js?v=1780144474" type="text/javascript"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const menuButton = document.querySelector(".w-nav-button");
  if (menuButton) {
    menuButton.addEventListener("click", function() {
      document.body.classList.toggle("no-scroll");
    });
  }
});
</script>
<style>
.no-scroll {
  overflow: hidden;
}

.menu-button.w-nav-button > .contact-btn {
  pointer-events: none;
}

.hambuger-menu-main {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  width: 24px;
  cursor: pointer;
}

.hamburger-lines-1,
.hamburger-lines-2,
.hamburger-lines-2-copy,
.hamburger-lines-3 {
  height: 2.3px;
  /* Brand blue by default — white headers (job-seekers, about, etc.) */
  background-color: #0562A7 !important;
  transition: all 0.3s cubic-bezier(0.215, 0.61, 0.355, 1) !important;
  transform-origin: center !important;
}

/* Transparent/dark hero nav (home): white bars until the header solidifies on scroll */
.header-992:not(.white) .hamburger-lines-1,
.header-992:not(.white) .hamburger-lines-2,
.header-992:not(.white) .hamburger-lines-2-copy,
.header-992:not(.white) .hamburger-lines-3 {
  background-color: #ffffff !important;
}

.header-992:not(.white) .menu-button.is-scrolled .hamburger-lines-1,
.header-992:not(.white) .menu-button.is-scrolled .hamburger-lines-2,
.header-992:not(.white) .menu-button.is-scrolled .hamburger-lines-2-copy,
.header-992:not(.white) .menu-button.is-scrolled .hamburger-lines-3 {
  background-color: #0562A7 !important;
}

.menu-button.is-active .hamburger-lines-1 {
  position: absolute;
  top: 50%;
  transform: translateY(-50%) rotate(45deg) !important;
}

.menu-button.is-active .hamburger-lines-2-copy,
.menu-button.is-active .hamburger-lines-2 {
  opacity: 0 !important;
  transform: translateX(-10px) !important;
}

.menu-button.is-active .hamburger-lines-3 {
  position: absolute;
  top: 50%;
  transform: translateY(-50%) rotate(-45deg) !important;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const menuWrapper = document.querySelector('.menu-button');
  if (!menuWrapper) {
    return;
  }

  menuWrapper.addEventListener('click', function() {
    this.classList.toggle('is-active');
  });

  window.addEventListener('scroll', function() {
    if (window.scrollY > 50) {
      menuWrapper.classList.add('is-scrolled');
    } else {
      menuWrapper.classList.remove('is-scrolled');
    }
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof jQuery === 'undefined') {
    return;
  }

  var overlay = document.querySelector('.w-nav-overlay');
  if (!overlay) {
    return;
  }

  var iOS = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);
  var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutationRecord) {
      if (mutationRecord.attributeName === 'style' && window.getComputedStyle(overlay).getPropertyValue('display') !== 'none') {
        if (iOS) {
          var x = jQuery(window).scrollTop().toFixed();
          jQuery('body').css({
            overflow: 'hidden',
            position: 'fixed',
            top: '-' + x + 'px',
            width: '100vw'
          });
        } else {
          jQuery('body').css('overflow', 'hidden');
        }
      } else {
        if (iOS) {
          var t = jQuery('body').css('top').replace('-', '').replace('px', '');
          jQuery('body').css({ overflow: 'auto', position: '', width: '100vw' });
          jQuery('body').animate({ scrollTop: t }, 0);
        } else {
          jQuery('body').css('overflow', '');
        }
      }
    });
  });

  observer.observe(overlay, { attributes: true, attributeFilter: ['style'] });
});
</script>
