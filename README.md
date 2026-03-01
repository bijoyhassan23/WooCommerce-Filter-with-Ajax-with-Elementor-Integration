# WooCommerce Filter with Ajax (Elementor Integration)

A custom WooCommerce plugin that provides Ajax-based product filtering with Elementor template rendering support.

## Features

- Ajax product filtering endpoint for logged-in and guest users.
- Frontend filter UI shortcode.
- Elementor template-based product loop rendering shortcode.
- Localized Ajax config (`admin-ajax.php` URL and security nonce).
- Separate CSS/JS assets for frontend behavior and styling.

## Requirements

- WordPress 6.x+
- WooCommerce
- Elementor
- PHP 7.4+ (recommended: PHP 8.0+)

## Installation

1. Copy this plugin folder to:
	- `wp-content/plugins/woocommerce-filter-with-ajax`
2. In WordPress Admin, go to **Plugins**.
3. Activate **WooCommerce Filter with Ajax with Elementor Integration**.

## Shortcodes

### 1) Filter UI

Use this shortcode where you want to show the filter form:

```shortcode
[wc_filter_ajax]
```

### 2) Product Grid Template Output

Use this shortcode to render products through a specific Elementor template:

```shortcode
[wc_filter_ajax_template template_id="123"]
```

Replace `123` with your Elementor template ID.

## How It Works

1. The shortcode outputs filter controls and/or template wrapper.
2. Frontend script sends Ajax requests to `wc_filter_products`.
3. The request is protected with a nonce (`wc_filter_ajax_nonce`).
4. The plugin renders products using:
	- `includes/product-grid-template.php`

## Plugin Structure

```text
woocommerce-filter-with-ajax/
├── assets/
│   ├── css/main.css
│   └── js/main.js
├── includes/
│   ├── product-grid-template.php
│   └── shortcode-filter.php
├── woocommerce-filter-with-ajax.php
└── README.md
```

## Ajax Endpoint

- Action: `wc_filter_products`
- Supports:
  - `wp_ajax_wc_filter_products`
  - `wp_ajax_nopriv_wc_filter_products`

Expected request params:

- `nonce` (required)
- `template_id` (required, integer)

## Development Notes

- Main plugin bootstrap file: `woocommerce-filter-with-ajax.php`
- Assets are registered in `enqueue_scripts()` and loaded by shortcode rendering.
- You can extend filtering logic in `wc_filter_products_callback()` and template rendering helpers.

## Troubleshooting

- Confirm WooCommerce and Elementor are both active.
- Confirm the shortcode is added on a published page.
- Confirm `template_id` is valid for an existing Elementor template.
- If Ajax fails, check browser console and Network tab for nonce/template errors.

## License

GPL2
