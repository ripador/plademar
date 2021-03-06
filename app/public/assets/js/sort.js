$(function() {
    $('.response').hide();

    let sortable = $("#sortable");
    sortable.sortable();
    sortable.disableSelection();

    $('#check').on('click', function(e) {
        $('.response').hide();
        let stop = false;
        let pos = 1;
        let ant = 0;

        sortable.find('li').each(function (idx) {
            let obj = $(this);
            let val = parseInt(obj.attr('data-value'));

            if (stop === false && val < ant) {
                $('#ko').show();
                stop = true;
            }
            pos++;
            ant = val;
        });

        if (stop === false) {
            $('#ok').show();
            $('#check').hide();
        }
    });
});

$(document).ready(function() {
    /* Change the numbers size depending on the level */
    let lvl = $('#form_difficult').val();
    if (lvl >= 6) {
        $('#sortable li').css('font-size', '1em');
    }
});