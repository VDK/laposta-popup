<?php
/*
Template Name: Laposta Theme (form page)
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/laposta-form-style.css">
    
    <script>
document.addEventListener("DOMContentLoaded", function() {
    function sendHeight() {
        if (window.parent) {
            let height = document.body.scrollHeight;
            window.parent.postMessage({ height: height }, "*");
        }
    }

    // Send height when the page loads
    sendHeight();

    // Observe changes in the DOM to update height dynamically
    const observer = new MutationObserver(sendHeight);
    observer.observe(document.body, { childList: true, subtree: true });

    // Update height when the window resizes
    window.addEventListener("resize", sendHeight);

    // Detect form success and notify parent
    const formObserver = new MutationObserver(function(mutations) {
        if (document.querySelector(".lsb-success-title")) {
            setTimeout(function () {
                window.parent.postMessage({ action: "closePopup" }, "*");
                //stop popup for a year when someone signs up:
                document.cookie = "nsp_popup_closed=true; path=/; max-age=" + (365 * 24 * 60 * 60); 
            }, 1000); // Wait 2 seconds before closing
        }
    });

    formObserver.observe(document.body, { childList: true, subtree: true });

});
</script>
<title><?php the_title(); ?> - <?php echo get_bloginfo('name'); ?></title>

</head>
<body <?php body_class('laposta-theme-page'); ?>>

    <h1><?php the_title(); ?></h1> <!-- Page title remains visible -->

    <div class="content">
        <?php
        while (have_posts()) : the_post();
            the_content();
        endwhile;
        ?>
    </div>
   <?php wp_footer(); ?> <!-- Ensures any necessary scripts load -->
</body>
</html>
