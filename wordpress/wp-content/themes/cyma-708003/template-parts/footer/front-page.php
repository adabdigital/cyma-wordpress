<script type="text/javascript">var $ = window.jQuery;</script><script src="<?php echo get_template_directory_uri(); ?>/assets/js/webflow.js?v=1780144474" type="text/javascript"></script>
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
</style>
<script>
window.addEventListener("scroll", function () {
const blueLogo = document.querySelector(".blue-logo");
const darkLogo = document.querySelector(".dark-logo");
const blueIcon = document.querySelector(".call-icon-blue");
const darkIcon = document.querySelector(".call-icon-dark");
if (!blueLogo || !darkLogo || !blueIcon || !darkIcon) return;
if (window.scrollY > 80) {
blueLogo.style.display = "none";
darkLogo.style.display = "block";
blueIcon.style.display = "none";
darkIcon.style.display = "block";
} else {
blueLogo.style.display = "block";
darkLogo.style.display = "none";
blueIcon.style.display = "block";
darkIcon.style.display = "none";
}
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
if (typeof jQuery === 'undefined') {
return;
}

var iOS = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);
var overlay = document.querySelector('.w-nav-overlay');
if (!overlay) {
return;
}

var observer = new MutationObserver(function(mutations) {
mutations.forEach(function(mutationRecord) {
if (mutationRecord.attributeName === 'style' && window.getComputedStyle(overlay).getPropertyValue('display') !== 'none') {
if (iOS) {
var x = jQuery(window).scrollTop().toFixed();
jQuery('body').css({'overflow': 'hidden', 'position': 'fixed', 'top': '-' + x + 'px', 'width': '100vw'});
} else {
jQuery('body').css('overflow', 'hidden');
}
} else {
if (iOS) {
var t = jQuery('body').css('top').replace('-','').replace('px','');
jQuery('body').css({'overflow': 'auto', 'position': '', 'width': '100vw'});
jQuery('body').animate({scrollTop:t}, 0);
} else {
jQuery('body').css('overflow', '');
}
}
});
});
observer.observe(overlay, { attributes: true, attributeFilter: ['style']});
});
</script>
<!--
hamburger
-->
<style>
/* 1. Container Styles */
.hambuger-menu-main {
position: relative;
display: flex;
flex-direction: column;
justify-content: space-between;
width: 24px;
cursor: pointer;
}
/* 2. BASE & INITIAL STATE (White) */
.hamburger-lines-1,
.hamburger-lines-2,
.hamburger-lines-2-copy,
.hamburger-lines-3 {
height: 2.3px;
background-color: #ffffff !important; /* Default White */
transition: all 0.3s cubic-bezier(0.215, 0.61, 0.355, 1) !important;
transform-origin: center !important;
}
/* 3. SCROLLED STATE (Blue) */
.menu-button.is-scrolled .hamburger-lines-1,
.menu-button.is-scrolled .hamburger-lines-2,
.menu-button.is-scrolled .hamburger-lines-2-copy,
.menu-button.is-scrolled .hamburger-lines-3 {
background-color: #0562A7 !important;
}
/* --- The Cross Animation --- */
/* Line 1: Top line rotates 45 deg */
.menu-button.is-active .hamburger-lines-1 {
position: absolute;
top: 50%;
transform: translateY(-50%) rotate(45deg) !important;
}
/* Line 2: Middle line disappears */
.menu-button.is-active .hamburger-lines-2-copy,
.menu-button.is-active .hamburger-lines-2 {
opacity: 0 !important;
transform: translateX(-10px) !important;
}
/* Line 3: Bottom line rotates -45 deg */
.menu-button.is-active .hamburger-lines-3 {
position: absolute;
top: 50%;
transform: translateY(-50%) rotate(-45deg) !important;
}
/* 4. "X" COLOR LOGIC:
Ensures the X is Blue ONLY when scrolled, otherwise stays White */
.menu-button.is-active.is-scrolled .hamburger-lines-1,
.menu-button.is-active.is-scrolled .hamburger-lines-3 {
background-color: #0562A7 !important;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
const menuWrapper = document.querySelector('.menu-button');
if (menuWrapper) {
// Toggle Active State (The X animation)
menuWrapper.addEventListener('click', function() {
this.classList.toggle('is-active');
});
// Handle Scroll Color Change
window.addEventListener('scroll', function() {
if (window.scrollY > 50) {
menuWrapper.classList.add('is-scrolled');
} else {
menuWrapper.classList.remove('is-scrolled');
}
});
}
});
</script>