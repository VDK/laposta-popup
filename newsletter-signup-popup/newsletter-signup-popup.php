<?php
/*
Plugin Name: Laposta Newsletter Signup Popup
Plugin URI: https://github.com/VDK/laposta-popup/
Description: Displays a 'Sign up for my newsletter' lightbox after users scroll down or when a link is clicked. Works with the Laposta Signup Basic plugin.
Version: 1.1
Author: Vera de Kok
Author URI: https://www.veradekok.nl/
License: GPL2
*/

function nsp_register_settings() {
    $supported_languages = get_supported_languages();
    foreach ($supported_languages as $lang_code) {
        add_option("nsp_page_id_$lang_code", '');
        register_setting('nsp_options_group', "nsp_page_id_$lang_code");
    }
}
add_action('admin_init', 'nsp_register_settings');

function nsp_enqueue_scripts() {
    $supported_languages = get_supported_languages();
    $page_ids = [];
    foreach ($supported_languages as $lang_code) {
        $page_id = get_option("nsp_page_id_$lang_code", '');
        // If the page exists and is published, keep it
        if ($page_id && get_post_status($page_id) === 'publish') {
            $page_ids[] = (int) $page_id;
        }
        // If the page is missing or trashed, reset the option
        elseif ($page_id && get_post_status($page_id) !== 'publish') {
            update_option("nsp_page_id_$lang_code", ''); // Reset only invalid pages
        }
    }
    // Prevent recursion even if no pages are left
    if (!empty($page_ids) && is_page($page_ids)) {
        return; // Don't add the popup if it's inside the iframe
    }

    wp_enqueue_style('nsp-style', plugin_dir_url(__FILE__) . 'css/nsp-style.css');
    wp_enqueue_script('nsp-script', plugin_dir_url(__FILE__) . 'js/nsp-script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'nsp_enqueue_scripts');

function nsp_register_options_page() {
    add_options_page('Laposta Newsletter Popup Settings', 'Laposta Popup', 'manage_options', 'nsp-settings', 'nsp_options_page');
}
add_action('admin_menu', 'nsp_register_options_page');

function nsp_options_page() {
    $pages = get_pages();
    $supported_languages = get_supported_languages();
    ?>
    <div class="wrap">
        <h2>Laposta Newsletter Popup Settings</h2>
        <p>Add a page in the backend with the shortcode of a signup form and change the theme to "Laposta Theme (form page)".</p>
        <p>Once configured, you can add a link anywhere on your site using:
            <code>&lt;a href="#" id="nsp-open"&gt;Sign up for my newsletter!&lt;/a&gt;</code>
        </p>
        <form method="post" action="options.php">
            <?php settings_fields('nsp_options_group'); ?>
            <table class="form-table">
                <?php
                foreach ($supported_languages as $lang_code) {
                    $language_name = get_language_name($lang_code);
                    $selected_page = get_option("nsp_page_id_$lang_code", '');
                    ?>
                    <tr>
                        <th scope="row"><label for="nsp_page_id_<?php echo $lang_code; ?>"><?php echo $language_name; ?> Page:</label></th>
                        <td>
                            <select id="nsp_page_id_<?php echo $lang_code; ?>" name="nsp_page_id_<?php echo $lang_code; ?>">
                                <option value="">-- Select a Page --</option>
                                <?php foreach ($pages as $page) { ?>
                                    <option value="<?php echo $page->ID; ?>" <?php selected($selected_page, $page->ID); ?>>
                                        <?php echo esc_html($page->post_title); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
function get_supported_languages() {
    $laposta_languages = array('nl', 'en', 'de', 'es', 'fr'); // Laposta-supported languages
    $active_languages = [];

    // Detect if WPML is installed and get active languages
    if (function_exists('icl_get_languages')) {
        $wpml_languages = icl_get_languages('skip_missing=0');
        if (!empty($wpml_languages)) {
            $active_languages = array_keys($wpml_languages);
        }
    }
    
    // Detect if Polylang is installed and get active languages
    elseif (function_exists('pll_get_languages')) {
        $active_languages = array_keys(pll_get_languages(['fields' => 'slug']));
    }

    // If no multilingual plugin, use the site's locale
    if (empty($active_languages)) {
        $active_languages[] = substr(get_locale(), 0, 2);
    }

    // Only return languages that Laposta supports
    return array_intersect($laposta_languages, array_unique($active_languages));
}



function get_language_name($lang_code) {
    $language_names = array(
        'nl' => 'Dutch',
        'en' => 'English',
        'de' => 'German',
        'es' => 'Spanish',
        'fr' => 'French'
    );
    return $language_names[$lang_code] ?? strtoupper($lang_code);
}


function nsp_get_newsletter_url() {
    $locale = substr(get_locale(), 0, 2);
    $page_id = get_option("nsp_page_id_$locale", '');

    // If no page is set, return empty
    if (!$page_id) {
        return '';
    }

    // If the page no longer exists, reset the ID and return empty
    if (get_post_status($page_id) !== 'publish') {
        update_option("nsp_page_id_$locale", ''); // Reset if invalid
        return '';
    }

    // Prevent recursion: If this function is called inside the popup itself, return empty
    if (is_page((int) $page_id)) {
        return '';
    }

    return esc_url(get_permalink((int) $page_id));
}

function nsp_lightbox_markup() {
    $iframe_url = nsp_get_newsletter_url();
    if (!$iframe_url) {
        return; // Don't display the popup if there's no page linked
    }
    ?>
    <div id="nsp-popup" class="nsp-hidden">
        <div class="nsp-popup-content">
            <span id="nsp-close">&times;</span>
            <div id="nsp-form-container">
                <iframe id="nsp-iframe" src="<?php echo $iframe_url; ?>" width="100%" height="450" style="border:none;"></iframe>
            </div>
        </div>
    </div>
  
    <?php
}
add_action('wp_footer', 'nsp_lightbox_markup');

// Shortcode to insert a newsletter popup button
function nsp_popup_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'text' => 'Sign up for my newsletter!', // Default button text
        ), 
        $atts, 
        'laposta_popup_button'
    );

    return '<a href="#" class="nsp-open">' . esc_html($atts['text']) . '</a>';
}
add_shortcode('laposta_popup_button', 'nsp_popup_shortcode');

?>
