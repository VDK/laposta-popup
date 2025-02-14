# Laposta Newsletter Signup Popup

## Overview
This WordPress plugin displays a "Sign up for my newsletter" lightbox popup that appears after users scroll down or click a link. It integrates with the **Laposta Signup Basic** plugin and supports multiple languages.

## Installation

1. **Install and configure the [Laposta Signup Basic plugin](https://wordpress.org/plugins/laposta-signup-basic/)** if you haven’t already.
2. **Expand your theme** by placing the following files inside your active theme directory:
   - `laposta-theme.php`
   - `laposta-form-style.css` (can be modified to match your theme's design).
3. **Edit your theme’s `functions.php` file** as described in the [Required Theme Customization](#required-theme-customization) section below.
4. **Upload this plugin** to the `/wp-content/plugins/` directory.
5. Activate **Laposta Newsletter Signup Popup** through the **Plugins** menu in WordPress.
6. Create a **new page** with the Laposta signup form shortcode and assign it the theme **Laposta Theme (form page)**.
7. Navigate to **Settings → Laposta Popup** to configure the signup pages.
8. Assign each language to a dedicated signup page.

## Required Theme Customization
For the popup to work correctly, you must **add a custom page template** to your theme. This ensures the Laposta signup form is loaded in a minimal layout.

### Add This to Your Theme's `functions.php`:
```php
function custom_laposta_template($template) {
    if (is_page('custom-page')) {
        return get_stylesheet_directory() . '/laposta-theme.php';
    }
    return $template;
}
add_filter('template_include', 'custom_laposta_template');
```
* This ensures that pages assigned the **Laposta Theme (form page)** will load correctly.
* You can modify `laposta-form-style.css` to better match your theme's styling.

###How to Add the Newsletter Link

Once configured, you can trigger the popup by adding this link anywhere on your site:
```html
<a href="#" id="nsp-open">Sign up for my newsletter!</a>
```

###Language Support
The plugin only loads languages that are both **configured in the site's frontend** and **supported by Laposta**. It checks:

* **WPML**: Uses `icl_get_languages()`
* **Polylang**: Uses `pll_get_languages()`
* **Default WordPress Locale**: Uses `get_locale()`

Supported Languages:
✅ Dutch (`nl`), English (`en`), German (`de`), Spanish (`es`), French (`fr`)

###Notes
* Ensure the **Laposta Signup Basic plugin** is installed and configured.
* The popup will not appear unless at least **one language page** is set in the plugin settings.
* The template file (`laposta-theme.php`) must be structured correctly to display only the signup form.
* You can modify `laposta-form-style.css` to fit your theme’s design.

###Troubleshooting

* **Popup not appearing?** Ensure you have set the correct pages in **Settings → Laposta Popup**.
* **Form page loading incorrectly?** Verify that `laposta-theme.php` exists in your theme and is correctly referenced in `functions.php`.
* **Languages not showing in settings?** Check if WPML or Polylang is installed and configured properly.