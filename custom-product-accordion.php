<?php
/*
Plugin Name: HGT AJANS AKORDİYON
Description: HGT AJANS MENÜ AKORDİYONU
Version: 1.3
Author: Said Berk
*/

function custom_product_accordion_shortcode() {
    ob_start();
    ?>
    <div class="custom-accordion">
        <?php
        $categories = get_terms('product_cat', array('hide_empty' => false));
        foreach ($categories as $category) :
            $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
            $image = wp_get_attachment_url($thumbnail_id);
        ?>
            <div class="accordion-item">
                <div class="accordion-header" style="background-image: url('<?php echo $image; ?>');">
                    <?php echo $category->name; ?>
                </div>
                <div class="accordion-content">
                    <div class="product-grid">
                        <?php
                        $args = array(
                            'post_type' => 'product',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'product_cat',
                                    'field' => 'term_id',
                                    'terms' => $category->term_id,
                                ),
                            ),
                        );
                        $products = new WP_Query($args);
                        if ($products->have_posts()) :
                            while ($products->have_posts()) : $products->the_post();
                                global $product;
                                ?>
                                <div class="product-item" data-product-id="<?php echo get_the_ID(); ?>">
                                    <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" alt="<?php the_title(); ?>">
                                    <h3><?php the_title(); ?></h3>
                                    <p class="price"><?php echo $product->get_price_html(); ?></p>
                                    <p class="description"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                                </div>
                                <?php
                            endwhile;
                        endif;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="product-popup" class="product-popup">
    <div class="product-popup-content">
        <span class="close-popup">&times;</span>
        <div class="image-container">
            <div class="circular-progress">
                <div class="inner"></div>
            </div>
            <img id="popup-image" src="" alt="Product Image">
        </div>
        <div class="product-info">
            <h2 id="popup-title"></h2>
            <p id="popup-description"></p>
            <p id="popup-price"></p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('product_accordion', 'custom_product_accordion_shortcode');

function enqueue_custom_accordion_scripts() {
    wp_enqueue_style('custom-accordion-style', plugins_url('custom-accordion.css', __FILE__));
    wp_enqueue_script('custom-accordion-script', plugins_url('custom-accordion.js', __FILE__), array('jquery'), '1.1', true);
    wp_localize_script('custom-accordion-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_accordion_scripts');

function get_product_details() {
    $product_id = $_POST['product_id'];
    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error('Ürün bulunamadı');
        return;
    }

    $response = array(
        'title' => $product->get_name(),
        'price' => $product->get_price_html(),
        'description' => $product->get_short_description(),
        'image' => get_the_post_thumbnail_url($product_id, 'full')
    );

    wp_send_json_success($response);
}
add_action('wp_ajax_get_product_details', 'get_product_details');
add_action('wp_ajax_nopriv_get_product_details', 'get_product_details');