"use strict"; // jshint ;_;

function updateComboSelector(target) {
    var widget = target.parents('.sidus-combo-selector').first();
    widget.find('.select2').removeClass('select2').select2('destroy');
    widget.find('input[data-family="' + target.val() + '"]')
        .addClass('select2')
        .samsonSelect2();
}

function initComboSelector(target) {
    $(target).find('.sidus-combo-selector select').each(function () {
        var t = $(this);
        if (t.val()) {
            updateComboSelector(t);
        }
        t.on('change', function () {
            updateComboSelector(t);
        });
    });
}

!function ($) {
    $(document).ready(function () {
        initComboSelector(document);
    });
}(window.jQuery);
