define(['jquery'], function ($) {

    $('#record-selection-check-actions-toggle').attr('disabled', function () {
        if (0 == $('#proposals .t3js-entity .form-check-input').length) {
            return true;
        }
    });

    $('#form-selection-todo .btn[type="submit"]').each(function () {
        $(this).on('click', function (ev) {
            if (0 == $(this).parent().parent().find('select').val()) {
                ev.preventDefault();
                $(this).blur();
                return false;
            }
        });
    });

    $('#action-check-all').on('click', function () {
        $('#proposals').find('.t3js-entity').each(function () {
            $('.form-check-input', this).prop('checked', true);
            if (!$('.col-selector', this).hasClass('success')) {
                $('.col-selector', this).addClass('success');
            }
        });
        if (!$(this).hasClass('disabled')) {
            $(this).addClass('disabled');
        }
        if ($('#action-check-none').hasClass('disabled')) {
            $('#action-check-none').removeClass('disabled');
        }
        $('#selection-row').show();
    });

    $('#action-check-none').on('click', function () {
        $('#proposals').find('.t3js-entity').each(function () {
            $('.form-check-input', this).prop('checked', false);
            if ($('.col-selector', this).hasClass('success')) {
                $('.col-selector', this).removeClass('success');
            }
        });
        if ($('#action-check-all').hasClass('disabled')) {
            $('#action-check-all').removeClass('disabled');
        }
        if (!$(this).hasClass('disabled')) {
            $(this).addClass('disabled');
        }
        $('#selection-row').hide();
    });

    $('.check-toggle-selection').each(function () {
        $(this).on('click', function () {
            $('#multi-check-actions').find('button').each(function () {
                if ($(this).hasClass('disabled')) {
                    $(this).removeClass('disabled');
                }
            });
            if ($('.t3js-multi-record-selection-check:checked').length > 0) {
                $('#selection-row').show();
            } else {
                $('#selection-row').hide();
            }
            $('#proposals .form-check-input').change(function () {
                if ($(this).is(":checked")) {
                    $(this).parent().parent().addClass('success');
                } else {
                    $(this).parent().parent().removeClass('success');
                }
            });
        });
    });

    $('.button.action-add-comment').each(function () {
        $(this).on('click', function () {
            let f = $(this).parent().next().find('.comment-add-form');
            if (f.css('display') !== 'block') {
                f.css('display', 'block');
                $('textarea', f).focus();
            } else {
                f.css('display', 'none');
            }
        });
    });

    $('.js-oap-multi-check[data-oap-multi-check-value]').on('click', function () {
        const value = $(this).attr('data-oap-multi-check-value');

        $('.js-oap-multi-check-target[value="' + value + '"]').prop('checked', true);
    });

});
