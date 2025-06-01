
<?php
/*
Template Name: Product Upload Form (Winkelmanager)
*/

if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) {
    wp_redirect(home_url());
    exit;
}

$current_user = wp_get_current_user();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['product_title'])) {
    $product_title = sanitize_text_field($_POST['product_title']);
    $product_sku = sanitize_text_field($_POST['product_sku']);
    $product_description = sanitize_textarea_field($_POST['product_description']);
    $product_price = isset($_POST['product_price']) ? floatval($_POST['product_price']) : 0;
    $manage_stock = isset($_POST['manage_stock']) ? 'yes' : 'no';
    $stock_quantity = isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0;
    $stock_status = isset($_POST['stock_status']) ? sanitize_text_field($_POST['stock_status']) : 'instock';
    $individual_sold = isset($_POST['individual_sold']) ? 'yes' : 'no';
    $shipping_class = isset($_POST['shipping_class']) ? intval($_POST['shipping_class']) : 0;
    $product_category = isset($_POST['product_category']) ? array_map('intval', $_POST['product_category']) : [];
    $product_tags = isset($_POST['product_tags']) ? array_map('intval', $_POST['product_tags']) : [];
    $product_weight = isset($_POST['product_weight']) ? sanitize_text_field($_POST['product_weight']) : '';
    $product_attributes = isset($_POST['product_attributes']) ? $_POST['product_attributes'] : [];
    error_log("POST product_attributes: " . print_r($_POST['product_attributes'], true));

    $wc_attributes = array();

    // Product aanmaken
    $new_product = [
        'post_title' => $product_title,
        'post_content' => $product_description,
        'post_status' => 'publish',
        'post_type' => 'product',
        'post_author' => $current_user->ID,
    ];

    $product_id = wp_insert_post($new_product);

    if ($product_id) {
        wp_set_object_terms($product_id, $product_category, 'product_cat');

        // Tags correct opslaan
        if (!empty($product_tags)) {
            wp_set_object_terms($product_id, $product_tags, 'product_tag');
        }
        
        update_post_meta($product_id, '_regular_price', $product_price);
        update_post_meta($product_id, '_price', $product_price);
        update_post_meta($product_id, '_sku', $product_sku);
        update_post_meta($product_id, '_manage_stock', $manage_stock);
        update_post_meta($product_id, '_stock_status', $stock_status);
        update_post_meta($product_id, '_sold_individually', $individual_sold);
        update_post_meta($product_id, '_weight', $product_weight);
        
         // Voorraad correct opslaan
        if ($manage_stock === 'yes') {
            update_post_meta($product_id, '_stock', $stock_quantity);
            update_post_meta($product_id, '_stock_status', ($stock_quantity > 0) ? 'instock' : 'outofstock');
        }
        
        // Verzendklasse correct opslaan
        if (!empty($shipping_class)) {
            wp_set_object_terms($product_id, [$shipping_class], 'product_shipping_class');
        }
        
        // attributen
        if (!empty($product_attributes)) {
            foreach ($product_attributes as $taxonomy => $selected_terms) {
                // Koppel de geselecteerde termen (als slugs) aan het product
                wp_set_object_terms($product_id, $selected_terms, $taxonomy);
                $attribute_values = array();
                foreach ($selected_terms as $term_slug) {
                    $term = get_term_by('slug', $term_slug, $taxonomy);
                    if ($term && !is_wp_error($term)) {
                        $attribute_values[] = $term->name;
                    }
                }
                
                $attribute = new WC_Product_Attribute();
                $attribute->set_id( wc_attribute_taxonomy_id_by_name($taxonomy) );
                $attribute->set_name( $taxonomy );
                $attribute->set_options( $attribute_values );
                $attribute->set_position( 0 );
                $attribute->set_visible( true );
                $attribute->set_variation( false );
                
                $wc_attributes[$taxonomy] = $attribute;
            }
            error_log("WC Attributes: " . print_r($wc_attributes, true));
            // Attributen toepassen op het product
            if (!empty($wc_attributes)) {
                $product = wc_get_product($product_id);
                $product->set_attributes($wc_attributes);
                $product->save();
            }
        }
        

        // Afbeeldingen opslaan
        if (!empty($_FILES['product_image']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $uploadedfile = $_FILES['product_image'];
            $upload_overrides = ['test_form' => false];
            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $filename = $movefile['file'];
                $wp_upload_dir = wp_upload_dir();
                $attachment = [
                    'post_mime_type' => $movefile['type'],
                    'post_title' => sanitize_file_name(basename($filename)),
                    'post_status' => 'inherit'
                ];
                $attach_id = wp_insert_attachment($attachment, $filename, $product_id);
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
                wp_update_attachment_metadata($attach_id, $attach_data);
                set_post_thumbnail($product_id, $attach_id);
            }
        }
        
        // Galerij-afbeeldingen opslaan
        if (!empty($_FILES['product_gallery']['name'][0])) {
            $gallery_ids = [];
            foreach ($_FILES['product_gallery']['name'] as $key => $value) {
                if ($_FILES['product_gallery']['name'][$key]) {
                    $file = [
                        'name' => $_FILES['product_gallery']['name'][$key],
                        'type' => $_FILES['product_gallery']['type'][$key],
                        'tmp_name' => $_FILES['product_gallery']['tmp_name'][$key],
                        'error' => $_FILES['product_gallery']['error'][$key],
                        'size' => $_FILES['product_gallery']['size'][$key]
                    ];
                    $movefile = wp_handle_upload($file, ['test_form' => false]);
                    if ($movefile && !isset($movefile['error'])) {
                        $attachment = [
                            'post_mime_type' => $movefile['type'],
                            'post_title' => sanitize_file_name(basename($movefile['file'])),
                            'post_status' => 'inherit'
                        ];
                        $attach_id = wp_insert_attachment($attachment, $movefile['file'], $product_id);
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                        $gallery_ids[] = $attach_id;
                    }
                }
            }
            if (!empty($gallery_ids)) {
                update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
            }
        }
        echo '<p class="bg-accent text-text p-6 rounded">✅ Product succesvol toegevoegd!</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product toevoegen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: auto;
        }
        h2 {
            text-align: center;
            color:#b48c88;
        }
        label {
            font-size: 16px;
            font-weight: bold;
            color: #4a4441;
            display: block;
            margin-top: 12px;
        }
        .input-field, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #c7c789;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 5px;
        }
        .checkbox-group label {
            font-weight: normal;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .radio-group label {
            font-weight: normal;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn {
            display: block;
            width: 100%;
            text-align: center;
            background-color: #b48c88;
            color: white;
            padding: 12px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #a37772;
        }
        .file-input {
            padding: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Product toevoegen</h2>
        <a href="<?php echo home_url(); ?>" style="float: right;">Terug naar Home</a>
        <a href="/claire" style="float: left;">Terug naar Claire</a>
        <br>
       
        <form method="post" enctype="multipart/form-data">
            <label>Producttitel:</label>
            <input type="text" name="product_title" class="input-field" required>

            <label>SKU (Artikelnummer):</label>
            <input type="text" name="product_sku" class="input-field" required>

            <label>Beschrijving:</label>
            <textarea name="product_description" class="input-field" required></textarea>

            <label>Prijs (€):</label>
            <input type="number" step="0.01" name="product_price" class="input-field" required>

            <label><input type="checkbox" name="manage_stock" value="yes"> Voorraadbeheer inschakelen</label>

            <label>Voorraad (Aantal op voorraad):</label>
            <input type="number" name="stock_quantity" class="input-field" min="0">

            <label>Voorraadstatus:</label>
            <div class="radio-group">
                <label><input type="radio" name="stock_status" value="instock"> Op voorraad</label>
                <label><input type="radio" name="stock_status" value="outofstock"> Uitverkocht</label>
                <label><input type="radio" name="stock_status" value="onbackorder"> In nabestelling</label>
            </div>

            <label>Verzendmethode:</label>
            <select name="shipping_class" class="input-field">
                <option value="">Selecteer verzendmethode</option>
                <?php
                $shipping_classes = get_terms(['taxonomy' => 'product_shipping_class', 'hide_empty' => false]);
                foreach ($shipping_classes as $class) {
                    echo '<option value="' . esc_attr($class->term_id) . '">' . esc_html($class->name) . '</option>';
                }
                ?>
            </select>

            <label>Categorieën:</label>
            <div class="checkbox-group">
                <?php
                $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
                foreach ($categories as $category) {
                    echo '<label><input type="checkbox" name="product_category[]" value="' . esc_attr($category->term_id) . '"> ' . esc_html($category->name) . '</label>';
                }
                ?>
            </div>

            <label>Attributen:</label>
            <div class="checkbox-group">
                <?php
                global $wpdb;
                $attributes = $wpdb->get_results("SELECT attribute_name, attribute_label FROM {$wpdb->prefix}woocommerce_attribute_taxonomies");
                foreach ($attributes as $attribute) {
                    $taxonomy = 'pa_' . $attribute->attribute_name;
                    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
                    if (!empty($terms) && !is_wp_error($terms)) {
                        echo '<p>' . esc_html($attribute->attribute_label) . ':</p>';
                        foreach ($terms as $term) {
                            echo '<label><input type="checkbox" name="product_attributes[' . esc_attr($taxonomy) . '][]" value="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</label>';
                        }
                    }
                }
                ?>
            </div>

            <label>Tags:</label>
            <div class="checkbox-group">
                <?php
                $tags = get_terms(['taxonomy' => 'product_tag', 'hide_empty' => false]);
                foreach ($tags as $tag) {
                    echo '<label><input type="checkbox" name="product_tags[]" value="' . esc_attr($tag->term_id) . '"> ' . esc_html($tag->name) . '</label>';
                }
                ?>
            </div>

            <label>Hoofdafbeelding (1 afbeelding):</label>
            <input type="file" name="product_image" accept="image/*" class="file-input">

            <label>Galerij-afbeeldingen (meerdere afbeeldingen toegestaan):</label>
            <input type="file" name="product_gallery[]" accept="image/*" multiple class="file-input">

            <button type="submit" class="btn">Product toevoegen</button>
            <div class="text-center" style="margin-top: 10px;">
            <a href="<?php echo home_url(); ?>" class="btn">Terug naar Home</a>
            <a href="/claire" class="btn">Terug naar Claire</a>
        </div>
        </form>
    </div>
</body>
</html>
