<?php
/**
 * Plugin Name: WooCommerce Filter with Ajax with Elementor Integration
 * Description: A plugin to filter WooCommerce products using Ajax. Allows customers to filter products by category, price, attributes, and more with real-time Ajax updates and seamless Elementor widget integration.
 * Version: 2.0.0
 * Author: Forazitech
 * Author URI: https://forazitech.com
 * License: GPL2
 * Text Domain: woocommerce-filter-with-ajax
 * Requires plugins: woocommerce, elementor
 */

defined("ABSPATH") or die("Direct access not allowed.");

class Wc_filter_ajax_with_elementor{
    private static $instance = null;
    public static function get_instance(){
        if (self::$instance == null) self::$instance = new self();
        return self::$instance;
    }

    public function __construct(){
        add_action("plugins_loaded", [$this, "init"]);
    }

    public function init(){
        $this->define_constants();
        $this->includes_files();
        
        add_action("wp_enqueue_scripts", [$this, "enqueue_scripts"]);
        add_action("admin_enqueue_scripts", [$this, "enqueue_scripts"]);
        add_action("wp_ajax_wc_filter_products", [$this, "wc_filter_products_callback"]);
        add_action("wp_ajax_nopriv_wc_filter_products", [$this, "wc_filter_products_callback"]);
        
        add_shortcode("wc_filter_ajax", [$this, "filter_render_shortcode"]);
        add_shortcode("wc_filter_ajax_template", [$this, "template_render_shortcode"]);
        
        register_activation_hook(__FILE__, [$this, "activate"]);
        register_deactivation_hook(__FILE__, [$this, "deactivate"]);
    }

    private function define_constants(){
        define("WC_FILTER_AJAX_VERSION", "2.0.0");
        define("WC_FILTER_AJAX_PLUGIN_DIR", plugin_dir_path(__FILE__));
        define("WC_FILTER_AJAX_PLUGIN_URL", plugin_dir_url(__FILE__));
    }

    private function includes_files(){
        include WC_FILTER_AJAX_PLUGIN_DIR . "includes/product-grid-template.php";
    }

    public function enqueue_scripts(){
        // Register necessary scripts and styles here
        wp_register_style("wc-filter-ajax-style", WC_FILTER_AJAX_PLUGIN_URL . "assets/css/main.css", [], WC_FILTER_AJAX_VERSION);
        wp_register_script("wc-filter-ajax-script", WC_FILTER_AJAX_PLUGIN_URL . "assets/js/main.js", [], WC_FILTER_AJAX_VERSION, true);
        
        // Localize script to pass Ajax URL and nonce
        $wc_filter_ajax_params = [
            "ajax_url" => admin_url("admin-ajax.php"),
            "nonce" => wp_create_nonce("wc_filter_ajax_nonce"),
        ];

        global $wp_query;
        if (is_archive()) {
            $query_vars = $wp_query->query_vars;
            if (isset($query_vars['taxonomy']) && isset($query_vars['term'])) {
                $wc_filter_ajax_params['tax_query'] = [
                    'taxonomy' => $query_vars['taxonomy'],
                    'field'    => 'slug',
                    'terms'    => $query_vars['term'],
                    'operator' => 'IN',
                ];
            }
        }
        wp_localize_script("wc-filter-ajax-script", "wcFilterAjax", $wc_filter_ajax_params);
    }

    public function filter_render_shortcode($atts){
        // Render the shortcode output here
        wp_enqueue_style("wc-filter-ajax-style");
        wp_enqueue_script("wc-filter-ajax-script");
        ob_start();
        include WC_FILTER_AJAX_PLUGIN_DIR . "includes/shortcode-filter.php";
        return ob_get_clean();
    }

    public function template_render_shortcode($atts){
        // Render the template shortcode output here
        wp_enqueue_style("wc-filter-ajax-style");
        wp_enqueue_script("wc-filter-ajax-script");
        $atts = wp_parse_args($atts, ["template_id" => null, "filter_hook" => null]);
        $template_id = $atts['template_id'];
        if(!$template_id) return;
        ob_start();
            echo '<div class="wc-filter-loop-grid" data-template-id="' . esc_attr($template_id) .'" data-filter-hook="' . esc_attr($atts['filter_hook']) .'" . load-status="false" .>';
                wc_filter_ajax_template($template_id, $atts['filter_hook']);
            echo '</div>';
        return ob_get_clean();
    }

    public function wc_filter_products_callback(){
        if(!wp_verify_nonce($_GET['nonce'], "wc_filter_ajax_nonce")){
            wp_send_json_error("Invalid nonce");
            wp_die();
        };

        $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
        if(!$template_id){
            wp_send_json_error("Invalid template ID");
            wp_die();
        }
        $filter_hook = isset($_GET['filter_hook']) ? sanitize_text_field($_GET['filter_hook']) : null;
        wc_filter_ajax_template($template_id, $filter_hook);
    }

    public function activate(){
        // Activation code here
    }

    public function deactivate(){
        // Deactivation code here
    }
}

Wc_filter_ajax_with_elementor::get_instance();