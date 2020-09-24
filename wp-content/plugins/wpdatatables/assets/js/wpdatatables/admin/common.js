/**
 * Controller for common methods in the admin section
 *
 * @author Alexander Gilmanov
 * @since 18.10.2016
 */

/**
 * Hide tooltip on button click or on mouseout event
 */
var wdtHideTooltip = function () {
    jQuery('[data-toggle="tooltip"]').click(function() {
        jQuery(this).tooltip('hide');
    });

    jQuery('[data-toggle="tooltip"]').mouseout(function(event) {
        var e = event.toElement || event.relatedTarget;
        if (e != null && (e.parentNode == this || e == this)) {
            return;
        }
        jQuery(this).tooltip('hide');
    });
};

/**
 * Extend jQuery to use AnimateCSS
 */
jQuery.fn.extend({
    animateCss: function (animationName, onEnd) {
        var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
      jQuery(this).addClass('animated ' + animationName).one(animationEnd, function () {
        jQuery(this).removeClass('animated ' + animationName);
            if (typeof onEnd == 'function') {
                onEnd();
            }
        });
    },
    fadeInDown: function () {
      jQuery(this)
          .removeClass('hidden')
          .show()
          .animateCss('fadeInDown');
    },
    fadeInRight: function (onEnd) {
      jQuery(this)
          .removeClass('hidden')
          .show()
          .animateCss('fadeInRight');
        if (typeof onEnd == 'function') {
            onEnd();
        }
    },
    fadeOutDown: function () {
        var $this = jQuery(this);
      jQuery(this).animateCss('fadeOutDown', function () {
            $this
              .addClass('hidden')
              .hide();
        });
    },
    fadeOutRight: function () {
        var $this = jQuery(this);
      jQuery(this).animateCss('fadeOutRight', function () {
            $this
              .addClass('hidden')
              .hide();
        });
    },
    animateFadeIn: function () {
        var $this = jQuery(this);
      jQuery(this)
          .removeClass('hidden')
          .show()
          .removeClass('fadeOut')
          .animateCss('fadeIn', function () {
              $this
                .removeClass('fadeIn')
                .removeClass('hidden')
                .show()
          });
    },
    animateFadeOut: function ( onEnd ) {
        var $this = jQuery(this);
      jQuery(this)
          .removeClass('fadeIn')
          .animateCss('fadeOut', function () {
              $this
                .addClass('hidden')
                .removeClass('fadeOut')
                .hide();
              if( typeof onEnd == 'function' ){
                  onEnd();
              }
          });
    }
});

/**
 * Helper method to insert at textarea cursor position
 */
jQuery.fn.extend({
    insertAtCaret: function (myValue) {
        return this.each(function (i) {
            if (document.selection) {
                //For browsers like Internet Explorer
                this.focus();
                var sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
            }
            else if (this.selectionStart || this.selectionStart == '0') {
                //For browsers like Firefox and Webkit based
                var startPos = this.selectionStart;
                var endPos = this.selectionEnd;
                var scrollTop = this.scrollTop;
                this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
                this.focus();
                this.selectionStart = startPos + myValue.length;
                this.selectionEnd = startPos + myValue.length;
                this.scrollTop = scrollTop;
            } else {
                this.value += myValue;
                this.focus();
            }
        });
    }
});

(function ($) {

    $(function () {

        /**
         * Show WordPress warnings before wpDataTables data
         */
        $('.card-header:eq(0) > *').not('img, h2, ul.actions, button#wdt-table-id, .clear').prependTo('div.wdt-datatables-admin-wrap');


        /**
         * Attach tooltips
         */
        $('[data-toggle="tooltip"]').tooltip();

        wdtHideTooltip();

        /**
         * Attach HTML Popovers (Hints with images)
         */
        $('[data-toggle="html-popover"]').popover({
            html: true,
            content: function () {
                var content = $(this).attr("data-popover-content");
                return $(content).children(".popover-body").html();
            },
            title: function () {
                var title = $(this).attr("data-popover-content");
                return $(title).children(".popover-heading").html();
            }
        });

        /**
         * Apply selectpicker
         */
        $('select.selectpicker').selectpicker();

        /**
         * Apply colorpicker
         */
        $(".color-picker").each(function () {
            wdtApplyColorPicker(this);
        });

        /**
         * Hide modal dialog on Esc button
         */
        $(document).on('keyup','.modal', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if ( e.which == 27 ) {
                $('.modal').modal('hide');
            }
        });

        $('button.wdt-backend-close').click(function(){
            $('#wdt-backend-close-modal').modal('show');

            $('#wdt-backend-close-button').click(function() {
                $(location).attr('href', wdtWpDataTablesPage.browseTablesUrl);
            });
        });

        $('button.wdt-backend-chart-close').click(function(){
            $('#wdt-backend-close-modal').modal('show');

            $('#wdt-backend-close-button').click(function() {
                $(location).attr('href', wdtWpDataTablesPage.browseChartsUrl);
            });
        });

        $(".wpdt-c .wdt-datatables-admin-wrap div.toggle-switch input[hidden='hidden']").each(function (index,value){
            $(this).removeAttr("hidden")
        });


        /**
         * Get only text when copy shortcode from browse
         */

        $('.wpdt-c').on('click', '.wdt-copy-shortcode-browse', function(e){
            e.preventDefault();
            e.stopImmediatePropagation();

            var $temp = $("<input>");
            $($temp).insertAfter($(this));
            $temp.val($(this).data('shortcode')).select();
            document.execCommand("copy");
            $temp.remove();
            wdtNotify(
                wpdatatables_edit_strings.success,
                wpdatatables_edit_strings.shortcodeSaved,
                'success'
            );
        });

        /**
         * Logic for plus and minus button on number input field
         */
        $('.wdt-btn-number').on("click",function(e){
            e.preventDefault();

            var fieldName = $(this).attr('data-field');
            var type      = $(this).attr('data-type');
            var input = $("input[name='"+fieldName+"']");
            var currentVal = parseInt(input.val());
            if (!isNaN(currentVal)) {
                if(type == 'minus') {

                    if(currentVal > input.attr('min')) {
                        input.val(currentVal - 1).change();
                    }
                    if(parseInt(input.val()) == input.attr('min')) {
                        $(this).attr('disabled', true);
                    }

                } else if(type == 'plus') {
                    input.val(currentVal + 1).change();
                    $('.wdt-button-minus').attr('disabled', false);
                }
            } else {
                input.val(0);
            }
        });
        $(".input-number").on("keydown", function (e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

        /**
         * Get only text when copy shortcode
         */
        $('.wpdt-c').on('click', '.wdt-copy-shortcode', function(e){
            e.preventDefault();
            e.stopImmediatePropagation();

            var $temp = $("<input>");
            var $shortcodeType = $(this).data('shortcode-type');
            $($temp).insertAfter($(this));
            $temp.val($('#wdt-' + $shortcodeType + '-shortcode-id').text()).select();
            document.execCommand("copy");
            $temp.remove();
            wdtNotify(
              wpdatatables_edit_strings.success,
              wpdatatables_edit_strings.shortcodeSaved,
              'success'
            );
        });

        /**
         * Input underline animations
         */
        $(".collapse")[0] && ($(".collapse").on("show.bs.collapse", function(e) {
            $(this).closest(".panel").find(".panel-heading").addClass("active")
        }), $(".collapse").on("hide.bs.collapse", function(e) {
            $(this).closest(".panel").find(".panel-heading").removeClass("active")
        }), $(".collapse.in").each(function() {
            $(this).closest(".panel").find(".panel-heading").addClass("active")
        }));

        $(".fg-line")[0] && ($("body").on("focus", ".fg-line .form-control:not(.bootstrap-select)", function() {
            $(this).closest(".fg-line").addClass("fg-toggled")
        }));

        $("body").on("blur", ".form-control", function() {
            var p = $(this).closest(".form-group, .input-group")
              , i = p.find(".form-control").val();
            p.hasClass("fg-float") ? 0 == i.length && $(this).closest(".fg-line").removeClass("fg-toggled") : $(this).closest(".fg-line").removeClass("fg-toggled")
        });

        $(".fg-float")[0] && $(".fg-float .form-control").each(function() {
            var i = $(this).val();
            0 == !i.length && $(this).closest(".fg-line").addClass("fg-toggled")
        })


    });

})(jQuery);

/**
 * Hide preloader on window load
 */
jQuery(window).on('load', function(){
    jQuery('.wdt-preload-layer').animateFadeOut();
});

/**
 * Show preloader before leaving the page
 */
window.onbeforeunload = function(e) {
     if (window.reportbuilderobj == 'undifined') jQuery('.wdt-preload-layer').animateFadeIn();
};


/**
 * Growl notification in the right top corner
 * @param title
 * @param message
 * @param type
 */
function wdtNotify(title, message, type) {

    if (typeof title == 'undefined') {
        title = 'info';
    }
    if (typeof message == 'undefined') {
        message = 'info';
    }
    if (typeof type == 'undefined') {
        type = 'info';
    }


    switch (type) {
        case 'danger':
            icon = 'wpdt-icon-exclamation-triangle';
            break;
        case 'success':
        default:
            icon = 'wpdt-icon-check-circle-full';
            break;
    }

    jQuery.growl({
        icon: icon,
        title: ' ' + title + ' ',
        message: message,
        url: ''
    }, {
        element: 'body',
        type: type,
        allow_dismiss: true,
        placement: {
            from: 'top',
            align: 'right'
        },
        offset: {
            x: 20,
            y: 40
        },
        spacing: 10,
        z_index: 100002,
        delay: 2500,
        timer: 1000,
        url_target: '_blank',
        mouse_over: false,
        animate: {
            enter: 'animated fadeIn',
            exit: 'animated fadeOut'
        },
        icon_type: 'class',
        template: '<div data-growl="container" class="wpdt-c alert" role="alert">'+
          '<span class="sr-only">' + wpdatatables_edit_strings.close + '</span>' +
          '</button>' +
          '<span data-growl="icon"></span>' +
          '<span data-growl="title"></span>' +
          '<span data-growl="message"></span>' +
          '<a href="#" data-growl="url"></a>' +
          '</div>'
    });
}

/**
 * Replace input with Colorpicker layout
 */
var wdtInputToColorpicker = function (selecter) {
    var colorPickerHtml = jQuery('#wdt-color-picker-template').html();
    var val = jQuery(selecter).val();
    var classes = jQuery(selecter).prop('class');
    var $newEl = jQuery(colorPickerHtml);
    jQuery(selecter).replaceWith($newEl);
    $newEl.find('input').val(val).addClass(classes);
    wdtApplyColorPicker($newEl.find('.colorpicker-component'));
};

/**
 * Apply Bootstrap Colorpicker
 */
var wdtApplyColorPicker = function (selecter) {
    var colorOutput = jQuery(selecter).closest(".cp-container").find(".cp-value");
    var container = colorOutput.hasClass('cp-inside') ? jQuery(selecter).closest('.form-group') : false;
    jQuery(selecter).colorpicker({
        input: colorOutput,
        container: container,
        colorSelectors: {
            'black': '#000000',
            'white': '#ffffff',
            'red': '#FF0000',
            'default': '#777777',
            'primary': '#337ab7',
            'success': '#5cb85c',
            'info': '#5bc0de',
            'warning': '#f0ad4e',
            'danger': '#d9534f'
        }
    }).on('changeColor', function() {
        if (jQuery(this).find('input.cp-value').val() == '') {
            jQuery(this).find('span i').css('background-color', '');
        }
    });
    jQuery(selecter).find('input').click(function (e) {
        jQuery(selecter).colorpicker('show');
    });
};

/**
 * Replace colorpicker with input
 */
var wdtColorPickerToInput = function (selecter) {
    var val = jQuery(selecter).val();
    var classes = jQuery(selecter).prop('class');
    var $newEl = jQuery('<input />');
    jQuery(selecter).closest('div.cp-container').replaceWith($newEl);
    $newEl.val(val).addClass(classes);
};


