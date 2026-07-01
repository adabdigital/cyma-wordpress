<?php
$cyma_breadcrumb_html = cyma_get_breadcrumb_html();
if ($cyma_breadcrumb_html === '') {
    return;
}
?>
  <section class="section-25 margin">
    <div class="text-block-476"><?php echo $cyma_breadcrumb_html; ?></div>
  </section>
