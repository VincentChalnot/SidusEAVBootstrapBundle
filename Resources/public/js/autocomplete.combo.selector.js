
"use strict"; // jshint ;_;

function initComboSelector(target) {
    $(target).find('.sidus-combo-selector select').on('change', function(e){
        var t = $(this);
        var w = t.parents('.sidus-combo-selector').first();
        w.find('.select2').select2('destroy');
        w.find('input[data-family="' + t.val() + '"]')
            .addClass('select2')
            .samsonSelect2();
    });
}


!function ($) {
    $(document).ready(function() {
        initComboSelector(document);
    });
}(window.jQuery);
