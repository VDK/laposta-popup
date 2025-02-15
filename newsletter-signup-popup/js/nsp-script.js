jQuery(document).ready(function ($) {
    //enable "Sign up for my newsletter" hyperlink
    $(".nsp-open").on("click", function (event) {
        event.preventDefault();
        $("#nsp-popup").fadeIn();
    });

    // Listen for "succes" messages from the iframe
    window.addEventListener("message", function(event) {
        if (event.data.action === "closePopup") {
            $("#nsp-popup").fadeOut();
        }
    });

    //don't nag users for another week if they close the popup
    $("#nsp-close").on("click", function () {
        $("#nsp-popup").fadeOut();
        document.cookie = "nsp_popup_closed=true; path=/; max-age=" + (7 * 24 * 60 * 60);
    });
    if (document.cookie.includes("nsp_popup_closed=true")) {
        return;
    }

    
    
    //open if 1/3 of the page is scrolled down
    let hasScrolled = false;
    $(window).on("scroll", function () {
        if (!hasScrolled && $(window).scrollTop() > $(document).height() * 0.3) {
            hasScrolled = true;
            $("#nsp-popup").fadeIn();
        }
    });
    

});
