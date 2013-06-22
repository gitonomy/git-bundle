/**
 * Detects changes on single-file checkbox.
 */
$(document).on('change', 'ul.diff-summary input[type=checkbox][data-file-id]', function (event) {
    var $t = $(event.currentTarget);
    var $target = $("#" + $t.attr('data-file-id'));

    if ($t.prop('checked')) {
        $target.show();
    } else {
        $target.hide();
    }
});

/**
 * Detects changes on "toggle-all" on top of a commit.
 */
$(document).on('change', 'input[type=checkbox].diff-toggle-all', function (event) {
    var $t = $(event.currentTarget);
    var $target = $("#" + $t.attr('data-target') + " input[type=checkbox][data-file-id]");

    $target.prop('checked', $t.prop('checked'));
    $target.trigger('change');
});
