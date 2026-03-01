<?php
defined("ABSPATH") or die("Direct access not allowed.");
function wc_filter_ajax_template($template_id){
    if(!$template_id) return;
    
    $args = [
        "post_type" => "product",
        "meta_query" => ['relation' => 'AND'],
        "tax_query" => ['relation' => 'AND'],
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
    ];

    if(isset($_GET['paged'])) $args['paged'] = intval($_GET['paged']);

    // order by
    if(isset($_GET['orderby'])){
        switch ($_GET['orderby']) {
            case 'atoz':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'ztoa':
                $args['orderby'] = 'title';
                $args['order'] = 'DESC';
                break;
            case 'lowtohigh':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_price';
                $args['order'] = 'ASC';
                break;
            case 'hightolow':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_price';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }
    }else{
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }

    // per page
    $args['posts_per_page'] = isset($_GET['per_page']) ? intval($_GET['per_page']) : 12;

    // stock status
    if(isset($_GET['stock_status'])){
        $args['meta_query'][] = [
            'key' => '_stock_status',
            'value' => $_GET['stock_status'],
            'compare' => 'IN',
        ];
    }

    // price range
    if(isset($_GET['min_price']) || isset($_GET['max_price'])){
        $min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : 0;
        $max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : PHP_INT_MAX;
        if($min_price < $max_price){
            $args['meta_query'][] = [
                'key' => '_price',
                'value' => [$min_price, $max_price],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ];
        }
    }

    if(isset($_GET['terms']) && is_array($_GET['terms'])){
        foreach ($_GET['terms'] as $taxonomy => $terms) {
            $args['tax_query'][] = [
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => $terms,
                'operator' => 'IN',
            ];
        }
    }

    $products = new WP_Query( $args );
    if ( $products->have_posts() ) {
        ?><div class="wc-filter-loop" data-template-id="<?php echo esc_attr($template_id); ?>"><?php
        while ( $products->have_posts() ) {
            $products->the_post();
            echo do_shortcode('[elementor-template id="' . esc_attr($template_id) . '"]');
        }
        ?>
        </div>
        <div class="wc-filter-pagination">
            <div class="current_item_status">
                Showing <?php echo ($args['paged'] - 1) * $args['posts_per_page'] + 1; ?>-<?php echo min($args['paged'] * $args['posts_per_page'], $products->found_posts); ?> of <?php echo $products->found_posts; ?> item(s)
            </div>
            <div class="main_pagination">
                <?php 
                echo paginate_links([
                    'total' => $products->max_num_pages,
                    'current' => $args['paged'],
                    'format' => '?paged=%#%',
                    'prev_text' => __('Previous'),
                    'next_text' => __('Next'),
                ]); 
                ?>
            </div>
        </div>
        <?php
        wp_reset_postdata();
    } else {
        echo '<p>No products found.</p>';
    }
}

