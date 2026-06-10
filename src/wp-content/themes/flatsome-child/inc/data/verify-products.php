<?php
if ( ! defined( 'ABSPATH' ) ) exit;

echo 'Variable products: ' . count(wc_get_products(['type' => 'variable', 'limit' => -1])) . PHP_EOL;
echo 'Variations: ' . wp_count_posts('product_variation')->publish . PHP_EOL;

$terms_color = get_terms(['taxonomy' => 'pa_color', 'hide_empty' => false]);
echo 'Colors: ' . count($terms_color) . ' => ';
if (is_array($terms_color)) {
    foreach($terms_color as $t) echo $t->name . ', ';
}
echo PHP_EOL;

$terms_size = get_terms(['taxonomy' => 'pa_size', 'hide_empty' => false]);
echo 'Sizes: ' . count($terms_size) . ' => ';
if (is_array($terms_size)) {
    foreach($terms_size as $t) echo $t->name . ', ';
}
echo PHP_EOL;
