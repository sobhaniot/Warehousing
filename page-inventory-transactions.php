<?php
/*
Template Name: Inventory Transactions
*/
include_once ("inc/check_login.php");

if (!check_login_cookies()) {
    wp_redirect(home_url('/'));
    exit;
}

get_header();
// گرفتن همه پروژه‌ها برای نمایش در دراپ‌داون
$projects = get_terms(array(
    'taxonomy' => 'project_item',
    'hide_empty' => false,
));
$current_project = isset($_GET['project']) ? $_GET['project'] : '';

// گرفتن همه کالاها برای نمایش در دراپ‌داون
$items = get_terms(array(
    'taxonomy' => 'item_name',
    'hide_empty' => false,
));
$current_item = isset($_GET['item']) ? $_GET['item'] : '';

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$tax_query = array();

if (!empty($current_project)) {
    $tax_query[] = array(
        'taxonomy' => 'project_item',
        'field'    => 'slug',
        'terms'    => $current_project,
    );
}

if (!empty($current_item)) {
    $tax_query[] = array(
        'taxonomy' => 'item_name',
        'field'    => 'slug',
        'terms'    => $current_item,
    );
}

if (count($tax_query) > 1) {
    $tax_query['relation'] = 'AND';
}


$args = array( 
    'post_type' => 'post',
    'posts_per_page' => 50, 
    'paged' => $paged,
    'tax_query' => $tax_query,
);

$query = new WP_Query($args);





// echo '<pre>';
// var_dump($query);
// echo '</pre>';


?>

<div class="transactions-list-container">
    <h2>ورود و خروج کالاها</h2>
    <button id="print-button">چاپ</button> 
    <button id="save-pdf-button">ذخیره به صورت PDF</button>

    <form method="get" class="filter-form">
        <label for="project">فیلتر بر اساس پروژه:</label>
        <select name="project" id="project">
            <option value="">همه پروژه‌ها</option>
            <?php foreach ($projects as $project) : ?>
                <option value="<?php echo esc_attr($project->slug); ?>" <?php selected($current_project, $project->slug); ?>>
                    <?php echo esc_html($project->name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="item">فیلتر بر اساس کالا:</label>
        <select name="item" id="item">
            <option value="">همه کالاها</option>
            <?php foreach ($items as $item) : ?>
                <option value="<?php echo esc_attr($item->slug); ?>" <?php selected($current_item, $item->slug); ?>>
                    <?php echo esc_html($item->name); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">اعمال فیلتر</button>
    </form>


        <table class="transactions-table">
            <thead>
                <tr>
                    <th>تاریخ</th>
                    <th>کاربر</th>
                    <th>نوع عملیات</th>
                    <th>نام کالا</th>
                    <th>دسته‌بندی</th>
                    <th>پروژه</th>
                    <th>تعداد</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($query->have_posts()) { ?>
                 <?php while ($query->have_posts()) {
                     $query->the_post(); ?>
                 <!-- foreach ($transactions as $post) : setup_postdata($post); ?> -->
                    <?php
                        $categories = get_the_terms(get_the_ID(), 'category');
                        // echo '<pre>';
                        // var_dump($categories);
                        // echo '</pre>';

                        $project_item = get_the_terms(get_the_ID(), 'project_item');
                        // echo '<pre>';
                        // var_dump($project_item);
                        // echo '</pre>';
                        
                        $item_name = get_the_terms(get_the_ID(), 'item_name');
//                         echo '<pre>';
//                         var_dump($item_name[1]->parent);
//                         echo '</pre>';
                        
                    ?>
                    <tr>
                        <td><?php echo the_title(); ?></td>
                        <td><?php echo $categories[1]->name; ?></td>
                        <td><?php echo $categories[0]->name; ?></td>
						
						<?php 
                        if ($item_name[0]->parent == 0){
                            ?>
                            <td><?php echo $item_name[1]->name; ?></td>
                            <td><?php echo $item_name[0]->name; ?></td>
                            <?php
                        }else{
                            ?>
                            <td><?php echo $item_name[0]->name; ?></td>
                            <td><?php echo $item_name[1]->name; ?></td>
                            <?php
                        }
                        ?>
						
                        <td><?php 
                        if ($project_item && !is_wp_error($project_item)){
                            echo $project_item[0]->name;
                        }else{
                            echo "";
                        }
                            ?></td>
                        <td><?php echo the_excerpt(); ?></td>
                    </tr>
                <?php }
                 wp_reset_postdata(); ?>
                 <?php }else { ?>
                    <p>هیچ تراکنشی موجود نیست.</p>
                <?php } ?>
            </tbody>
        </table>
        <div class="pagination">
            <?php 
            echo paginate_links(array( 
                'total' => $query->max_num_pages, 
                'current' => max(1, get_query_var('paged')), 
                'format' => '?paged=%#%', 
                'show_all' => false, 
                'type' => 'plain', 
                'end_size' => 2, 
                'mid_size' => 1, 
                'prev_next' => true, 
                'prev_text' => __('« قبلی'), 
                'next_text' => __('بعدی »'), 
                'add_args' => array(
                    'project' => $current_project,
                    'item' => $current_item,
                ),
                'add_fragment' => '', 
                )); 
            ?> 
        </div>
</div>
<p class="back-to-home"> 
    <a href="<?php echo home_url(); ?>" class="button">بازگشت به صفحه اصلی</a> 
</p>

<script>
$(document).ready(function() {
        // دکمه پرینت
        $('#print-button').on('click', function() {
            window.print();
        });

        
    document.getElementById('save-pdf-button').addEventListener('click', function () {
        var element = document.querySelector('.transactions-list-container');
		var currentPage = <?php echo get_query_var('paged') ? get_query_var('paged') : 1; ?>;
    	var filename = 'transactions_page_' + currentPage + '.pdf';

        html2pdf(element, {
            margin: 0,
            filename: filename,
            image: { type: 'jpeg', quality: 1 },
            html2canvas: {
                scale: 2, 
                useCORS: true, 
                width: element.scrollWidth
            }, //,width:1080,height:1080
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' } // landscape
        });
    });
    });
</script>

<?php get_footer(); ?>

