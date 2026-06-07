<?php


$args = [
    'wfPage' => '697c91c02a0ba03d31d36132',
    'body' => '',
    'head' => 'head/single-casestudies',
];   

     
get_header('', $args);

/* Start the Loop */
while ( have_posts() ) :
    the_post();
    ;
endwhile;
// End of the loop.

$args = [
  'footer' => 'footer/page-about-us',
];  


get_footer('', $args);
