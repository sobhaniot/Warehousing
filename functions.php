<?php
add_action('init', function() {
    $cookie_lifetime = time() + 86400 * 30; // 30 روز
    if (!isset($_COOKIE['zigpass'])) {
        setcookie('zigpass', 'ff', $cookie_lifetime, "/");
    }
    if (!isset($_COOKIE['ziguser'])) {
        setcookie('ziguser', 'unknow', $cookie_lifetime, "/");
    }
});

// function add_custom_menu() {
//     register_nav_menus(
//         array(
//             'new-menu' => __( 'New Menu' )
//         )
//     );
// }
// add_action( 'init', 'add_custom_menu' );

function create_custom_pages() {
    if (get_option('pages_created') != true) {
        $pages = array(
            'main' => 'main.php',
            'add-item' => 'add-item.php',
            'subtract-item' => 'subtract-item.php',
            'inventory-list' => 'inventory-list.php',
            'inventory-transactions' => 'inventory-transactions.php',
            'portfolio' => 'portfolio.php',
        );

        foreach ($pages as $title => $template) {
            $page_query = new WP_Query(array('post_type' => 'page', 'title' => $title));
            if (!$page_query->have_posts()) {
                $page = array(
                    'post_title'    => $title,
                    'post_content'  => '',
                    'post_status'   => 'publish',
                    'post_type'     => 'page',
                    'page_template' => $template,
                );
                wp_insert_post($page);
            }
        }

        // تنظیم گزینه برای جلوگیری از اجرای مجدد
        update_option('pages_created', true);
    }
}
add_action('after_setup_theme', 'create_custom_pages');


function register_taxonomy_helper($taxonomy, $singular_name, $plural_name, $slug) {
    $labels = array(
        'name'                       => _x($plural_name, 'taxonomy general name'),
        'singular_name'              => _x($singular_name, 'taxonomy singular name'),
        'search_items'               => __('جستجوی ' . $plural_name),
        'all_items'                  => __('همه ' . $plural_name),
        'edit_item'                  => __('ویرایش ' . $singular_name),
        'update_item'                => __('به‌روزرسانی ' . $singular_name),
        'add_new_item'               => __('افزودن ' . $singular_name . ' جدید'),
        'new_item_name'              => __('نام ' . $singular_name . ' جدید'),
        'menu_name'                  => __($plural_name),
    );

    $args = array(
        'hierarchical'               => true,
        'labels'                     => $labels,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'query_var'                  => true,
        'rewrite'                    => array('slug' => $slug),
    );

    register_taxonomy($taxonomy, array('post'), $args);
}

add_action('init', function() {
    register_taxonomy_helper('item_name', 'نام کالا', 'نام کالاها', 'item_name');
    register_taxonomy_helper('project_item', 'آیتم پروژه', 'آیتم‌های پروژه', 'project_item');
});


