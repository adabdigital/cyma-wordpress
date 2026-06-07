<?php


$args = [
    'wfPage' => '69871d0bd80291ea6509625b',
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
  'footer' => 'footer/page-about-us',
];  


get_footer('', $args);
