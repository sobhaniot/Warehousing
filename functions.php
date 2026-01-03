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
    register_taxonomy_helper('employee', 'نام کارمند', 'کارمندها', 'employee');
});

function zigurat_create_custom_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // ساختار کوئری برای ایجاد جدول
    $table_name = 'zigurat_inventory'; // اضافه کردن پیشوند وردپرس به نام جدول

    $sql_inventory = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        item_name varchar(255) NOT NULL,
        item_category varchar(100) DEFAULT '' NOT NULL,
        item_quantity int(11) DEFAULT 0 NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $table_users = 'zigurat_users';
    $sql_users = "CREATE TABLE $table_users (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        username varchar(60) NOT NULL,
        password varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

   
    // فراخوانی فایل مورد نیاز برای اجرای تابع dbDelta
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    // اجرای دستورات ساخت جداول
    dbDelta( $sql_inventory );
    dbDelta( $sql_users );
}

// اجرای تابع دقیقاً زمانی که پوسته فعال می‌شود
add_action( 'after_switch_theme', 'zigurat_create_custom_tables' );
