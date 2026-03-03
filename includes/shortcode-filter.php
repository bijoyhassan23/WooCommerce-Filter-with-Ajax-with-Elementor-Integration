<?php
defined("ABSPATH") or die("Direct access not allowed.");

$atts = wp_parse_args($atts, [
    "terms" => null,
    "per_page" => null,
]);
global $wpdb;
$atts['terms'] = $atts['terms'] ? explode(",", $atts['terms']) : [];
$atts['per_page'] = $atts['per_page'] ? array_map('intval', explode(",", $atts['per_page'])) : [];
?>
    <form class="filter_con" action="#">
        <!-- Search Query -->
        <div class="each_filter search_filter" show_status="<?php echo in_array("search", $atts) ? 'true' : 'false'; ?>">
            <div class="filter_header">
                <label>Search</label>
                <span class="toggle-icon">+</span>
            </div>
            <div class="clapse_able_part">
                <input type="text" name="s" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" placeholder="Search products...">
            </div>
        </div>

        <!-- ORDERBY -->
        <?php if(in_array("orderby", $atts)){?>
            <div class="each_filter orderby_filter">
                <div class="filter_header">
                    <label>Order By</label>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="clapse_able_part">
                    <?php
                    $orderby_options = [
                        'atoz' => 'A to Z',
                        'ztoa' => 'Z to A',
                        'lowtohigh' => 'Low to High',
                        'hightolow' => 'High to Low',
                    ];
                    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : '';
                    foreach ($orderby_options as $val => $label) {?>
                        <label>
                            <input type="radio" class="filter-radio" name="orderby" value="<?php echo esc_attr($val); ?>" <?php checked($orderby, $val); ?>> 
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <!-- PER PAGE -->
        <?php if(is_array($atts['per_page']) && !empty($atts['per_page'])){ ?>
            <div class="each_filter per_page_filter">
                <div class="filter_header">
                    <label>Products Per Page</label>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="clapse_able_part">
                    <?php 
                    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 18;
                    foreach ($atts['per_page'] as $num) { ?>
                        <label>
                            <input type="radio" class="filter-radio" name="per_page" value="<?php echo esc_attr($num); ?>" <?php checked($per_page, $num); ?>> 
                            <?php echo esc_html($num); ?>
                        </label>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <!-- STOCK STATUS -->
        <?php if(in_array("stock", $atts)){?>
            <div class="each_filter stock_status_filter">
                <div class="filter_header">
                    <label>Stock</label>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="clapse_able_part">
                    <?php
                    $stock_statuses = $wpdb->get_col("
                        SELECT DISTINCT meta_value 
                        FROM {$wpdb->postmeta} 
                        WHERE meta_key = '_stock_status' 
                        AND meta_value != ''
                    ");
                    if ($stock_statuses) {
                        $current_stock_status = isset($_GET['stock_status']) ? $_GET['stock_status'] : [];
                        foreach ($stock_statuses as $status) { ?>
                            <label>
                                <input type="checkbox" class="filter-input" name="stock_status[]" value="<?php echo esc_attr($status); ?>" <?php checked(in_array($status, $current_stock_status)); ?>> 
                                <?php echo esc_html(ucwords(str_replace(['_', '-'], ' ', $status))); ?>
                            </label>
                            <?php }
                    } else {
                        echo '<em>No stock statuses found.</em>';
                    }
                    ?>
                </div>
            </div>
        <?php } ?>

        <!-- PRICE RANGE -->
        <?php if(in_array("price", $atts)){
            $min_price = $wpdb->get_var("SELECT MIN(CAST(meta_value AS DECIMAL)) FROM {$wpdb->postmeta} WHERE meta_key = '_price' AND meta_value != ''");
            $max_price = $wpdb->get_var("SELECT MAX(CAST(meta_value AS DECIMAL)) FROM {$wpdb->postmeta} WHERE meta_key = '_price' AND meta_value != ''");
            ?>
            <div class="each_filter price_range_filter">
                <div class="filter_header">
                    <label>Price</label>
                    <span class="toggle-icon">+</span>
                </div>
                <div class="clapse_able_part" translate="no">
                    <div class="price_inputs">
                        <input type="number" id="min_price" placeholder="Min" name="min_price" value="<?php echo isset($_GET['min_price']) ? esc_attr($_GET['min_price']) : esc_attr($min_price); ?>"> -
                        <input type="number" id="max_price" placeholder="Max" name="max_price" value="<?php echo isset($_GET['max_price']) ? esc_attr($_GET['max_price']) : esc_attr($max_price); ?>">
                    </div>
                    <!-- <button class="apply-price">Apply</button> -->
                </div>
            </div>
        <?php } ?>

        <!-- Terms Filter -->
        <?php if(is_array($atts['terms']) && !empty($atts['terms'])){
            $term_filters = isset($_GET['terms']) ? $_GET['terms'] : []; 
            foreach($atts['terms'] as $term_slug){
                $term_obj = get_taxonomy( $term_slug );
                $term = get_terms(['taxonomy' => $term_slug, 'hide_empty' => true]);
                if (is_wp_error($term) || empty($term)) continue;
                $title = $term_obj->labels->singular_name ? $term_obj->labels->singular_name : ucwords(str_replace(['-', '_'], ' ', $term_slug));
                ?>
                <div class="each_filter <?php echo esc_attr($term_slug) ?>_filter">
                    <div class="filter_header">
                        <label><?php echo esc_html($title); ?></label>
                        <span class="toggle-icon">+</span>
                    </div>
                    <div class="clapse_able_part">
                        <?php foreach ($term as $t) { 
                            $is_term_selected = isset($term_filters[$term_slug]) && is_array($term_filters[$term_slug]) && in_array($t->slug, $term_filters[$term_slug]);
                            ?> 
                            <label>
                                <span>
                                    <input type="checkbox" class="filter-input" name="terms[<?php echo esc_attr($term_slug); ?>][]" value="<?php echo esc_attr($t->slug); ?>" <?php echo $is_term_selected ? 'checked' : ''; ?>> 
                                    <?php echo esc_html($t->name); ?>
                                </span>
                                <span><?php echo esc_html($t->count); ?></span>
                            </label>
                        <?php } ?>
                    </div>
                </div>
            <?php } 
        } ?>

        <!-- RESET BUTTON -->
        <div class="button reset-filters">Reset Filters</div>
        <button type="submit" class="button apply-filters">Apply Filters</button>
    </form>

