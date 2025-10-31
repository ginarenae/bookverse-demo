<?php
/**
 * Plugin Name: BookVerse Demo
 * Description: WooCommerce + Stripe demo (Books CPT + ACF + automatic CSV import + Buy button).
 * Version: 1.2
 * Author: Gina Renae
 */

if (!defined('ABSPATH')) exit;

/**
 * 1. Register Custom Post Type: Book
 */
function bookverse_register_cpt() {
    $labels = [
        'name' => 'Books',
        'singular_name' => 'Book',
        'add_new' => 'Add New Book',
        'add_new_item' => 'Add New Book',
        'edit_item' => 'Edit Book',
        'new_item' => 'New Book',
        'view_item' => 'View Book',
        'search_items' => 'Search Books',
        'not_found' => 'No Books Found',
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'supports' => ['title', 'editor', 'thumbnail'],
        'menu_icon' => 'dashicons-book',
    ];

    register_post_type('book', $args);
}
add_action('init', 'bookverse_register_cpt');

/**
 * 2. Register ACF fields
 */
function bookverse_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;

    acf_add_local_field_group([
        'key' => 'group_bookverse_fields',
        'title' => 'Book Details',
        'fields' => [
            [
                'key' => 'field_book_author',
                'label' => 'Author',
                'name' => 'book_author',
                'type' => 'text',
            ],
            [
                'key' => 'field_book_isbn',
                'label' => 'ISBN',
                'name' => 'book_isbn',
                'type' => 'text',
            ],
            [
                'key' => 'field_book_price',
                'label' => 'Price',
                'name' => 'book_price',
                'type' => 'number',
                'prepend' => '$',
                'step' => '0.01',
            ],
            [
                'key' => 'field_linked_product_id',
                'label' => 'Linked Product ID',
                'name' => 'linked_product_id',
                'type' => 'number',
                'readonly' => 1,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'book',
                ],
            ],
        ],
    ]);
}
add_action('acf/init', 'bookverse_register_acf_fields');

/**
 * 3. Import books from CSV on plugin activation
 */
function bookverse_import_books_from_csv() {
    $csv_path = plugin_dir_path(__FILE__) . 'books.csv';

    if (!file_exists($csv_path)) {
        error_log('BookVerse CSV not found: ' . $csv_path);
        return;
    }

    $csv = array_map('str_getcsv', file($csv_path));
    $headers = array_map('trim', array_shift($csv)); // first line headers

    foreach ($csv as $row) {
        $data = array_combine($headers, $row);
        if (empty($data['title']) || empty($data['isbn'])) continue;

        // Skip if Book already exists
        $existing = get_page_by_title($data['title'], OBJECT, 'book');
        if ($existing) continue;

        // Create WooCommerce product
        $product = new WC_Product_Simple();
        $product->set_name($data['title']);
        $product->set_regular_price($data['price']);
        $product->set_sku($data['isbn']);
        $product->set_manage_stock(false);
        $product_id = $product->save();

        // Create Book post
        $book_id = wp_insert_post([
            'post_title' => $data['title'],
            'post_type' => 'book',
            'post_status' => 'publish',
            'post_content' => 'Imported from CSV — Author: ' . $data['author'],
        ]);

        // Store ACF fields
        update_field('book_author', $data['author'], $book_id);
        update_field('book_isbn', $data['isbn'], $book_id);
        update_field('book_price', $data['price'], $book_id);
        update_field('linked_product_id', $product_id, $book_id);
    }
}
register_activation_hook(__FILE__, 'bookverse_import_books_from_csv');

/**
 * 4. Add “Buy with Stripe” button on Book pages
 */
function bookverse_buy_with_stripe_button($content) {
    if (is_singular('book')) {
        global $post;
        $author = get_field('book_author', $post->ID);
        $isbn = get_field('book_isbn', $post->ID);
        $price = get_field('book_price', $post->ID);
        $product_id = get_field('linked_product_id', $post->ID);

        $content .= '<div class="book-details">';
        $content .= '<p><strong>Author:</strong> ' . esc_html($author) . '</p>';
        $content .= '<p><strong>ISBN:</strong> ' . esc_html($isbn) . '</p>';
        $content .= '<p><strong>Price:</strong> $' . esc_html($price) . '</p>';
        $content .= '</div>';

        if ($product_id) {
            $url = wc_get_cart_url() . '?add-to-cart=' . $product_id;
            $content .= '<p><a href="' . esc_url($url) . '" class="button alt" style="background-color:#635bff;color:#fff;padding:10px 20px;border-radius:6px;">Buy with Stripe</a></p>';
        }
    }
    return $content;
}
add_filter('the_content', 'bookverse_buy_with_stripe_button');
