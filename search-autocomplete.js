jQuery(document).ready(function($) {
    $('#search-field').autocomplete({
        // reference global variable postTitles
        source: postTitles,
        minLength: 3,
        select: function(event, ui) {
            window.location.href = '/?s=' + ui.item.value;
        }
    });
});