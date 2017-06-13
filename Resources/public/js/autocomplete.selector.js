"use strict"; // jshint ;_;

function initSelect2($, $el) {
    if ($el.data('query-uri')) {
        $el.select2({
            ajax: {
                delay: 250,
                url: $el.data('query-uri'),
                processResults: function (data) {
                    return data;
                }
            }
        });
    } else {
        $el.select2();
    }
}

function initAutocompleteSelector($, target) {
    $(target).find('.select2').each(function () {
        initSelect2($, $(this));
    });
}

!function ($) {
    $(document).ready(function () {
        initAutocompleteSelector($, document);
    });
}(window.jQuery);
