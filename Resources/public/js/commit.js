/**
 * Detects changes on single-file checkbox.
 */
$(document).on('change', 'ul.diff-summary input[type=checkbox]', function (event) {
    $t = $(event.currentTarget);
    $target = $("#" + $t.attr('data-file-id'));

    if (undefined == $t.attr('checked')) {
        $target.hide();
    } else {
        $target.show();
    }
});

/**
 * Detects changes on "toggle-all" on top of a commit.
 */
$(document).on('change', 'input[type=checkbox].diff-toggle-all', function (event) {
    var $t = $(event.currentTarget);
    var $target = $("#" + $t.attr('data-target') + " input[type=checkbox][data-file-id]");

    if (undefined == $t.attr('checked')) {
        $target.removeAttr('checked');
    } else {
        $target.attr('checked', 'checked');
    }
    $target.trigger('change');
});
