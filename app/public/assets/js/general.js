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
