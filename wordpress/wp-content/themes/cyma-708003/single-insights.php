<?php


$args = [
    'wfPage' => '698d5bf6d57f425c10d28056',
    'body' => '',
    'head' => 'head/page-style-guide',
];   

     
get_header('', $args);

/* Start the Loop */
while ( have_posts() ) :
    the_post();
    ;
endwhile;
// End of the loop.

$args = [
  'footer' => 'footer/single-insights',
];  


get_footer('', $args);
