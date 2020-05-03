/**
 * updateResponse.
 *
 * @param msg string
 * @param valid bool
 */
function updateResponse(msg, valid) {
    let response = $('#response');
    response.text(msg);
    response.removeClass('alert-danger').removeClass('alert-success');

    let lvl = 'alert-danger';
    if (valid) {
        lvl = 'alert-success';
    }
    response.addClass(lvl).show();
}

/**
 * formDisable.
 * Disables all inputs in the form and hides the submmit button.
 *
 * @param selector string
 */
function formDisable(selector) {
    let the_form = $(selector);
    the_form.find('input').attr('disabled', 'disabled');
    the_form.find('button[type="submit"]').hide();
}
