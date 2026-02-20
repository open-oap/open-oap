import $ from 'jquery';

$(function() {
    $('#form-selection-todo .btn[type="submit"]').on('click', function (ev) {
        if (0 == $(this).parent().parent().find('select').val()) {
            ev.preventDefault();
            $(this).blur();
            return false;
        }
    });

    $('#record-selection-check-actions-toggle').attr('disabled', function () {
        return (0 === $('#proposals .t3js-entity .t3js-multi-record-selection-check').length);
    });

    $('#action-check-all').on('click', function () {
        $('#proposals .t3js-entity').addClass('success');
        $('#proposals .t3js-entity .t3js-multi-record-selection-check').prop('checked', true);

        $(this).addClass('disabled');
        $('#action-check-none').removeClass('disabled');

        $('#selection-row').show();
    });

    $('#action-check-none').on('click', function () {
        $('#proposals .t3js-entity').removeClass('success');
        $('#proposals .t3js-entity .t3js-multi-record-selection-check').prop('checked', false);

        $(this).addClass('disabled');
        $('#action-check-all').removeClass('disabled');

        $('#selection-row').hide();
    });

    $('.t3js-multi-record-selection-check').on('change', function () {
        const $checkbox = $(this);

        $checkbox.closest('.t3js-entity').toggleClass('success', $checkbox.is(":checked"));

        $('#selection-row').toggle(
            $('.t3js-multi-record-selection-check:checked').length > 0
        );
    });

    $('button.action-add-comment').on('click', function () {
        let f = $(this).parent().next().find('.comment-add-form');
        if (f.css('display') !== 'block') {
            f.css('display', 'block');
            $('textarea', f).focus();
        } else {
            f.css('display', 'none');
        }
    });

    $('.js-oap-multi-check[data-oap-multi-check-value]').on('click', function () {
        const value = $(this).attr('data-oap-multi-check-value');

        $('.js-oap-multi-check-target[value="' + value + '"]').prop('checked', true);
    });
});
