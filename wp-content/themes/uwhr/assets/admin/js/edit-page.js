jQuery(document).ready(function($){
    // Show or hide the featured page call to action input field
    $('#featuredPageCheckbox').click(function() {
        if ( $(this).is(':checked') ) {
            $('#callToActionTextContainer').removeClass('hidden');
        } else {
            $('#callToActionTextContainer').addClass('hidden');
        }
    });

    // Show or hide the anchor links more btn
    $('#pageAnchorLinkingActiveCheckbox').click(function() {
        if ( $(this).is(':checked') ) {
            $('#pageAnchorLinkingMoreBtnContainer').removeClass('hidden');
        } else {
            $('#pageAnchorLinkingMoreBtnContainer').addClass('hidden');
        }
    });

    $('#excludeSidebarCheckbox').click(function() {
        if ( $(this).is(':checked') ) {
            $('#page_sidebar_content').addClass('hidden');
        } else {
            $('#page_sidebar_content').removeClass('hidden');
        }
    });

    // Toggle visibility of custom metaboxes for different page formats
    $('#formatdiv input[type="radio"]').click(function() {
        var val = $(this).val();
        toggle_format_metaboxes(val);
    });

    var $externalLinkDiv = $('#acf_acf_external-link');
    $('label[for=acf_acf_external-link-hide]').css('display', 'none' );

    toggle_format_metaboxes( $('#formatdiv input[type="radio"]:checked').val() );

    function toggle_format_metaboxes(val) {
        if ( val == 'link' ) {
            $('#postdivrich').css('display', 'none');
            $('#page_sidebar_content').css('display', 'none');
            $('#page_anchor_links').css('display', 'none');
            $('#page_no_sidebar').css('display', 'none');

            $externalLinkDiv.attr('style', 'display: block !important');
        } else {
            $('#postdivrich').css('display', 'block');
            $('#page_sidebar_content').css('display', 'block');
            $('#page_anchor_links').css('display', 'block');
            $('#page_no_sidebar').css('display', 'block');

            $externalLinkDiv.attr('style', 'display: none !important');
        }
    }
});
