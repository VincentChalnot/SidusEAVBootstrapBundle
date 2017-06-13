"use strict"; // jshint ;_;

function updateComboSelector($, $select) {
    var $widget = $select.parents('.sidus-combo-selector').first();
    var $input = $widget.find(':input[data-family="' + $select.val() + '"]');
    $input.siblings().hide();
    initSelect2($, $input.show().addClass('select2'));
}

function initComboSelector($, target) {
    $(target).find('.sidus-combo-selector select').each(function () {
        var $select = $(this);
        if ($select.val()) {
            updateComboSelector($, $select);
        }
        $select.on('change', function () {
            updateComboSelector($, $select);
        });
    });
}

!function ($) {
    $(document).ready(function () {
        initComboSelector($, document);
    });
}(window.jQuery);
