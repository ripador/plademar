$(document).ready(function() {

    /* Get the operator from the hidden input and put it in all operatos' divs */
    $('div.operation').each(function (i) {
        let operator = $('#operations_operations_' + i + '_operator').val();
        $(this).find('div.operator span').text(operator);
    });

});