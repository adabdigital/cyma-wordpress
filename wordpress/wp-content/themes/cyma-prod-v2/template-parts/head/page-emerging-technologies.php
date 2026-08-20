<meta charset="utf-8">
<meta content="Emerging Technologies" name="twitter:title">
<meta content="width=device-width, initial-scale=1" name="viewport">
<?php wp_enqueue_style('normalize', get_template_directory_uri() . '/assets/css/normalize.css', [], '1780144474'); ?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/wordpress.css">
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/style.css">
<style>
/* Emerging Technologies — keep hero banner + Specialized Talent image visible/full.
   Hero box stayed at 382px while large breakpoints raise .image-59-_et to 385–470px,
   which clipped the layered banner into a thin/partial strip. Talent image had no
   intrinsic size reserve (lazy + min-height:100%), so the right column looked empty. */
.section-38-copy-et .softwaredevelopment-copy-et,
.section-38-copy-et .div-block-1205-copy-copy_et {
  overflow: visible;
}
.section-38-copy-et .div-block-1205-copy-copy_et {
  /* Match .image-59-_et heights so the main banner is not clipped. */
  height: 362px;
  min-height: 362px;
}
.section-38-copy-et .image-59-_et,
.section-38-copy-et .image-58-_et-copy-m,
.section-38-copy-et .image-58-_et-copy-mv {
  opacity: 1 !important;
  visibility: visible !important;
  max-width: none;
}
.section-60 [data-w-id="ce72881f-40da-a889-7ae3-ef6c23c10c9b"],
.section-60 [data-w-id="b251a622-032d-c5bf-cf44-fd27f0a63cf2"] {
  opacity: 1 !important;
  visibility: visible !important;
}
.section-60 [data-w-id="ce72881f-40da-a889-7ae3-ef6c23c10c9b"] {
  width: 100%;
  min-width: 0;
}
.section-60 .image-113-copy-main {
  display: block;
  width: 100%;
  max-width: 100%;
  height: auto;
  min-height: 0;
  aspect-ratio: 594 / 410;
  object-fit: cover;
  opacity: 1 !important;
  visibility: visible !important;
}
@media screen and (max-width: 767px) {
  .section-38-copy-et .div-block-1205-copy-copy_et {
    height: auto;
    min-height: 0;
  }
  .section-60 .image-113-copy-main {
    display: none;
  }
  .section-60 .image-113-copy-et {
    display: inline-block;
    width: 100%;
    height: auto;
    aspect-ratio: 372 / 190;
    object-fit: cover;
    opacity: 1 !important;
    visibility: visible !important;
  }
}
@media screen and (min-width: 1280px) {
  .section-38-copy-et .div-block-1205-copy-copy_et {
    height: 385px;
    min-height: 385px;
  }
}
@media screen and (min-width: 1440px) {
  .section-38-copy-et .div-block-1205-copy-copy_et {
    height: 425px;
    min-height: 425px;
  }
}
@media screen and (min-width: 1920px) {
  .section-38-copy-et .div-block-1205-copy-copy_et {
    height: 470px;
    min-height: 470px;
  }
}
</style>
<style>@media (min-width:992px) {html.w-mod-js:not(.w-mod-ix) [data-w-id="65ee7c17-16b3-bdc0-94d5-51e02c524efd"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="65ee7c17-16b3-bdc0-94d5-51e02c524f0a"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="32036373-638c-ef98-c03d-0c3251e7c789"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="32036373-638c-ef98-c03d-0c3251e7c79c"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="bbea0b12-b64e-565c-8ab8-5217523f6035"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="faf1d3df-de01-5b36-4f76-92290a3159f7"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="b251a622-032d-c5bf-cf44-fd27f0a63cf2"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="ce72881f-40da-a889-7ae3-ef6c23c10c9b"] {opacity:0;}}@media (max-width:991px) and (min-width:768px) {html.w-mod-js:not(.w-mod-ix) [data-w-id="65ee7c17-16b3-bdc0-94d5-51e02c524efd"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="65ee7c17-16b3-bdc0-94d5-51e02c524f0a"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="32036373-638c-ef98-c03d-0c3251e7c789"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="32036373-638c-ef98-c03d-0c3251e7c79c"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="bbea0b12-b64e-565c-8ab8-5217523f6035"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="faf1d3df-de01-5b36-4f76-92290a3159f7"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="b251a622-032d-c5bf-cf44-fd27f0a63cf2"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="ce72881f-40da-a889-7ae3-ef6c23c10c9b"] {opacity:0;}}@media (max-width:767px) and (min-width:480px) {html.w-mod-js:not(.w-mod-ix) [data-w-id="65ee7c17-16b3-bdc0-94d5-51e02c524efd"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="65ee7c17-16b3-bdc0-94d5-51e02c524f0a"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="32036373-638c-ef98-c03d-0c3251e7c789"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="32036373-638c-ef98-c03d-0c3251e7c79c"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="bbea0b12-b64e-565c-8ab8-5217523f6035"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="faf1d3df-de01-5b36-4f76-92290a3159f7"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="b251a622-032d-c5bf-cf44-fd27f0a63cf2"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="ce72881f-40da-a889-7ae3-ef6c23c10c9b"] {opacity:0;}}@media (max-width:479px) {html.w-mod-js:not(.w-mod-ix) [data-w-id="65ee7c17-16b3-bdc0-94d5-51e02c524efd"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="65ee7c17-16b3-bdc0-94d5-51e02c524f0a"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="32036373-638c-ef98-c03d-0c3251e7c789"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="32036373-638c-ef98-c03d-0c3251e7c79c"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="bbea0b12-b64e-565c-8ab8-5217523f6035"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="b251a622-032d-c5bf-cf44-fd27f0a63cf2"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="ce72881f-40da-a889-7ae3-ef6c23c10c9b"] {opacity:0;}html.w-mod-js:not(.w-mod-ix) [data-w-id="faf1d3df-de01-5b36-4f76-92290a3159f7"] {opacity:0;}}</style>
<link href="https://fonts.googleapis.com" rel="preconnect">
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin="anonymous">
<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js" type="text/javascript"></script>
<script type="text/javascript">WebFont.load({
google: {
families: ["Open Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic","Inter:300,400,500,600,700"]
}});</script>
<script type="text/javascript">!function(o,c){var n=c.documentElement,t=" w-mod-";n.className+=t+"js",("ontouchstart"in o||o.DocumentTouch&&c instanceof DocumentTouch)&&(n.className+=t+"touch")}(window,document);</script>
<link href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon.webp?v=1780144474" rel="shortcut icon" type="image/x-icon">
<link href="<?php echo get_template_directory_uri(); ?>/assets/images/webclip.webp?v=1780144474" rel="apple-touch-icon"><!--
Finsweet Attributes
-->
<script async="" type="module" src="https://cdn.jsdelivr.net/npm/@finsweet/attributes@2/attributes.js" fs-list=""></script>