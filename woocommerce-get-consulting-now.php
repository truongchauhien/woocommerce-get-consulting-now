<?php
/*
 * Plugin Name: WooCommerce Get Consulting Now
 * Plugin URI: https://github.com/truongchauhien/woocommerce-get-consulting-now
 * Text Domain: woocommerce-get-consulting-now
 * Domain Path: /languages
 */

register_activation_hook(__FILE__, 'wgcn_activate');
register_deactivation_hook(__FILE__, 'wgcn_deactivate');

function wgcn_activate() {

}

function wgcn_deactivate() {

}

add_action( 'init', 'wgcn_load_textdomain' );
function wgcn_load_textdomain() {
    load_plugin_textdomain('woocommerce-get-consulting-now', false, dirname( plugin_basename( __FILE__ ) ) . '/languages'); 
}

add_action('woocommerce_before_add_to_cart_button', 'wgcn_add_get_consulting_now_button');
function wgcn_add_get_consulting_now_button() {
    global $product;
    $terms = get_the_terms($product->get_id(), 'product_cat');

    $links = get_option('wgcn_links', []);
    $link = NULL;

    foreach ($terms as $product_category) {
        $category_slug = $product_category->slug;
        if (array_key_exists($category_slug, $links)) {
            $link = $links[$category_slug];
            break;
        }
    }

    if (!$link) {
        return;
    }

    echo '<div>';
    printf('<a class="wgcn-get-consulting-now button" href="%s" rel="nofollow">%s</a>', esc_attr($link), esc_html(__('Get consulting now', 'woocommerce-get-consulting-now')));
    echo '</div>';
}

function wgcn_settings_init() {
    register_setting('wgcn_settings', 'wgcn_links', [
        'type' => 'array',
        'description' => 'Product categories with respectively consulting link.',
        'sanitize_callback' => 'wgcn_sanitize_links'
    ]);

    add_settings_section(
        'wgcn_section_links',
        __('Consulting links', 'woocommerce-get-consulting-now'),
        'wgcn_section_links_html',
        'wgcn_settings'
    );

    $links = get_option('wgcn_links', []);

    if (!count($links) || array_key_last($links) !== '') {
        $links[''] = '';
    }

    foreach ($links as $category_slug => $consulting_link) {
        add_settings_field(
            'wgcn_field_link_' . $category_slug,
            __('Consulting link', 'woocommerce-get-consulting-now'),
            'wgcn_field_link_html',
            'wgcn_settings',
            'wgcn_section_links',
            [
                'category_slug' => $category_slug,
                'consulting_link' => $consulting_link
            ]
        );
    }
}
add_action( 'admin_init', 'wgcn_settings_init');

function wgcn_sanitize_links($input) {
    $category_slugs = $input['category_slugs'];
    $consulting_links = $input['consulting_links'];
    
    $output = [];
    foreach ($category_slugs as $index => $category_slug) {
        $category_slug = sanitize_text_field($category_slug);
        $consulting_link = sanitize_text_field($consulting_links[$index]);
        if ($category_slug === '') {
            continue;
        }

        $output[$category_slug] = $consulting_link;
    }

    return $output;
}

function wgcn_section_links_html($args) {
    echo '<div>';
    printf('<p>%s</p>', __('Specifying a consulting link for a product category.', 'woocommerce-get-consulting-now'));
    echo '</div>';
}

function wgcn_field_link_html($args) {
    printf('<input type="text" name="wgcn_links[category_slugs][]" value="%s">', $args['category_slug']);
    printf('<input type="text" name="wgcn_links[consulting_links][]" value="%s">', $args['consulting_link']);
}

function wgcn_settings_page() {
    add_menu_page(
        __('Get consulting', 'woocommerce-get-consulting-now'),
        __('Get consulting', 'woocommerce-get-consulting-now'),
        'manage_options',
        'wgcn_settings',
        'wgcn_settings_page_html'
    );
}
add_action('admin_menu', 'wgcn_settings_page');

function wgcn_settings_page_html() {
    echo '<div class="wgcn_settings_page">';
    printf('<h1>%s</h1>', get_admin_page_title());
    echo '<form action="options.php" method="post">';
        settings_fields('wgcn_settings');
        do_settings_sections('wgcn_settings');
        submit_button();
    echo '</form>';
    echo '</div>';
}

add_action('wp_enqueue_scripts', 'wgcn_add_scripts');
function wgcn_add_scripts() {
    wp_enqueue_style('wgcn_get_consulting_now_css', plugin_dir_url(__FILE__) . '/public/css/get-consulting-now.css');
}
