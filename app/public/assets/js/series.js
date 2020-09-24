$(document).ready(function() {
    $('form[name="serie"] input[type="text"]').each(function(idx){
        let input = $(this);
        if (input.val() != '') {
            input.attr('readonly', 'readonly').attr('tabindex', '-1');
        }
    });
});