"use strict"; // jshint ;_;

RegExp.escape = function (s) {
    return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
};

/**
 * Make bootstrap collection sortable, reorder properly the name of the input
 */
function sortableCollections(el) {
    /* Initialize bootstrap collection sortable */
    $(el).find('.bootstrap-collection.sortable-collection').sortable({
        axis: "y",
        handle: '.position-handler:not(.disabled)',
        items: "> li",
        opacity: 0.5,
        update: function () {
            var pattern = $(this).data('input-pattern');
            var prototypeName = $(this).parent().data('prototype-name');
            var regex = new RegExp(RegExp.escape(pattern).replace(prototypeName, '\\d+'));
            $(this).children().each(function (idx) {
                $(this).find(':input[name]').each(function () {
                    var newName = $(this).attr('name').replace(regex, pattern.replace(prototypeName, idx));
                    $(this).attr('name', newName);
                });
            });
        }
    });
}
