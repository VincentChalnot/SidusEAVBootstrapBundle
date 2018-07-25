/**
 * Initialize datepickers inside a given element
 * @param target
 */
function initDatePickers(target) {
    var defaultOptions = {
        icons: {
            time: 'fa fa-clock-o',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-play-circle',
            clear: 'fa fa-trash-o',
            close: 'fa fa-remove'
        },
        widgetPositioning: {
            horizontal: 'right',
            vertical: 'bottom'
        },
        useCurrent: false,
        ignoreReadonly: true,
        showTodayButton: true,
        showClear: true,
        locale: 'fr'
    };

    // Datetime pickers
    $(target).find('[data-provider="sidus-datetimepicker"]').each(function () {
        var format = $(this).data('format');
        // ICU to MomentJS pattern conversion
        format = format.replace(/(d?d|y|yyyy)/g, function (m) {
            return {
                'd': 'D',
                'dd': 'DD',
                'y': 'YYYY',
                'yyyy': 'YYYY'
                // @todo map the other cases
            }[m];
        });
        $(this).datetimepicker(
            $.extend({format: format, locale: $(this).data('locale')}, defaultOptions)
        );
    });
}
