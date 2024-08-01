<?php
/*
Plugin Name: HGT AJANS AKORDİYON
Description: HGT AJANS MENÜ AKORDİYONU
Version: 1.4
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
                                <div class="product-allergens">
                                    <?php
                                    $allergens = $product->get_attribute('pa_alerjen');
                                    if (!empty($allergens)) {
                                        $allergen_array = explode('|', $allergens);
                                        foreach ($allergen_array as $allergen) {
                                            $allergen = trim($allergen);
                                            echo '<img src="' . get_allergen_icon_url($allergen) . '" alt="' . $allergen . '" class="allergen-icon" title="' . $allergen . '">';
                                        }
                                    }
                                    ?>
                                </div>
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
                <div id="popup-attributes"></div>
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
    wp_enqueue_script('custom-accordion-script', plugins_url('custom-accordion.js', __FILE__), array('jquery'), '1.2', true);
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

    $attributes = array();
    $product_attributes = $product->get_attributes();
    
    foreach ($product_attributes as $attribute_name => $attribute) {
        if ($attribute_name === 'pa_alerjen' || $attribute_name === 'alerjen') {
            if ($attribute['is_taxonomy']) {
                $attribute_terms = wp_get_post_terms($product_id, $attribute_name, array('fields' => 'names'));
                if (!is_wp_error($attribute_terms)) {
                    $attributes['alerjen'] = $attribute_terms;
                }
            } else {
                $attributes['alerjen'] = explode(', ', $product->get_attribute($attribute_name));
            }
        }
    }

    // Eğer özel ürün niteliği olarak tanımlanmışsa
    if (empty($attributes['alerjen'])) {
        $all_attributes = $product->get_attributes();
        foreach ($all_attributes as $attr_name => $attr) {
            if (strtolower($attr_name) === 'alerjen') {
                $attributes['alerjen'] = explode('|', $attr->get_options()[0]);
                break;
            }
        }
    }

    $response = array(
        'title' => $product->get_name(),
        'price' => $product->get_price_html(),
        'description' => $product->get_short_description(),
        'image' => get_the_post_thumbnail_url($product_id, 'full'),
        'attributes' => $attributes
    );

    error_log('Product Details Response: ' . print_r($response, true));

    wp_send_json_success($response);
}

function get_allergen_icon_url($allergen) {
    $icon_paths = [
        'ACI' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Aci.png',
        'MANTAR' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/mantar-250x250-1.png',
        'ALKOL' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/alerjen_alkol.png',
        'GLUTEN' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Gluten.png',
        'HARDAL' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Hardal.png',
        'KEREVIZ' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Kereviz.png',
        'SUSAM' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Susam.png',
        'SUT' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Sut.png',
        'YUMURTA' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Yumurta.png',
        'KABUK' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Kabuklu.png',
        'KUKURT' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/Alerjen_Kukurt.png',
        'SOYA' => 'https://caddeparklounge.com/wp-content/uploads/2024/08/alerjen_soya.png'
    ];

    return isset($icon_paths[strtoupper($allergen)]) ? $icon_paths[strtoupper($allergen)] : '';
}

add_action('wp_ajax_get_product_details', 'get_product_details');
add_action('wp_ajax_nopriv_get_product_details', 'get_product_details');    