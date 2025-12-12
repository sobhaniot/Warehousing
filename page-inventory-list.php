<?php
/*
Template Name: Inventory List
*/
include_once ("inc/check_login.php");

if (!check_login_cookies()) {
    wp_redirect(home_url('/'));
    exit;
}

get_header();

// تابع برای دریافت اطلاعات انبار
function get_inventory_list() {
    global $wpdb;
    $table_name = 'zigurat_inventory';

    // بازیابی اطلاعات انبار از دیتابیس
    $inventory_items = $wpdb->get_results("SELECT * FROM $table_name");

    return $inventory_items;
}

// دریافت اطلاعات انبار
$inventory_items = get_inventory_list();
?>

<div class="inventory-list-container">
    <h2>لیست انبار</h2>
    <?php if (!empty($inventory_items)) : ?>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>نام کالا</th>
                    <th>دسته‌بندی</th>
                    <th>تعداد</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory_items as $item) : ?>
                    <tr>
                        <td><?php echo esc_html($item->item_name); ?></td>
                        <td><?php echo esc_html($item->item_category); ?></td>
                        <td><?php echo number_format($item->item_quantity); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>هیچ کالایی در انبار موجود نیست.</p>
    <?php endif; ?>
</div>
<p class="back-to-home"> 
    <a href="<?php echo home_url(); ?>" class="button">بازگشت به صفحه اصلی</a> 
</p>

<?php get_footer(); ?>


