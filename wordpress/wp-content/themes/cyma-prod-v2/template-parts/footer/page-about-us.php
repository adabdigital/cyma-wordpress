<script type="text/javascript">var $ = window.jQuery;</script><script src="<?php echo get_template_directory_uri(); ?>/assets/js/webflow.js?v=1780144474" type="text/javascript"></script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/milestone-timeline.js?v=1787095004" type="text/javascript"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
const menuButton = document.querySelector(".w-nav-button");
menuButton.addEventListener("click", function() {
document.body.classList.toggle("no-scroll");
});
});
</script>
<style>
.no-scroll {
overflow: hidden;
}
</style>
<style>
/* Container */
.hambuger-menu-main {
position: relative;
display: flex;
flex-direction: column;
justify-content: space-between;
/* height: 16px;
*/
width: 24px;
cursor: pointer;
}
/* Base styles for your 3 unique classes */
.hamburger-lines-1
{
/* width: 100%; */
height: 2.3px; /* Matches your Webflow setting */
background-color: #0562A7; /* Your requested blue */
transition: all 0.3s cubic-bezier(0.215, 0.61, 0.355, 1) !important;
transform-origin: center !important;
}
.hamburger-lines-1
{
/* width: 100%; */
height: 2.3px; /* Matches your Webflow setting */
background-color: #0562A7; /* Your requested blue */
transition: all 0.3s cubic-bezier(0.215, 0.61, 0.355, 1) !important;
transform-origin: center !important;
}
.hamburger-lines-3 {
height: 2.3px; /* Matches your Webflow setting */
background-color: #0562A7; /* Your requested blue */
transition: all 0.3s cubic-bezier(0.215, 0.61, 0.355, 1) !important;
transform-origin: center !important;
}
/* --- The Cross Animation --- */
/* Line 1: Top line rotates 45 deg */
.menu-button.is-active .hamburger-lines-1 {
position: absolute;
top: 50%;
transform: translateY(-50%) rotate(45deg) !important;
}
/* Line 2 Copy: Middle line disappears */
.menu-button.is-active .hamburger-lines-2-copy {
opacity: 0 !important;
transform: translateX(-10px) !important;
}
/* Line 3: Bottom line rotates -45 deg */
.menu-button.is-active .hamburger-lines-3 {
position: absolute;
top: 50%;
transform: translateY(-50%) rotate(-45deg) !important;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
const menuWrapper = document.querySelector('.menu-button');
if (menuWrapper) {
menuWrapper.addEventListener('click', function() {
this.classList.toggle('is-active');
});
}
});
</script>