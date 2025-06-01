<?php
// Child theme functies
add_action( 'wp_enqueue_scripts', 'klei_re_enqueue_styles' );
function klei_re_enqueue_styles() {
    wp_enqueue_style( 'astra-child-style', get_stylesheet_uri(), array( 'astra-theme-css' ), '1.0.0' );
}


// Toon tags met beschrijving op de productpagina
add_action('woocommerce_product_additional_information', 'toon_tag_tekst_bijkomende_info');
function toon_tag_tekst_bijkomende_info() {
    $product_id = get_the_ID();
    $tags = get_the_terms($product_id, 'product_tag');

    if ($tags && !is_wp_error($tags)) {
        foreach ($tags as $tag) {
            $tag_desc = term_description($tag);
            if (!empty($tag_desc)) {
                echo '<strong>Tag:</strong> ' . esc_html($tag->name) . '<br>';
                echo nl2br(strip_tags($tag_desc, '<br>')); // Verwijdert <p> en behoudt <br>
            }
        }
    }
}