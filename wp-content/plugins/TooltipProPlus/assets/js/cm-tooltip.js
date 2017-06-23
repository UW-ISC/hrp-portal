
(function ($) {

    $(document).ready(function () {

        // Add Color Picker to all inputs that have 'color-field' class
        $(function () {
            $('input[type="text"].colorpicker').wpColorPicker();
        });

        /*
         * CUSTOM REPLACEMENTS
         */

        $(document).on('click', '#cmtt-glossary-add-replacement-btn', function () {
            var data, replace_from, replace_to, replace_case, valid = true;

            replace_from = $('.cmtt-glossary-replacement-add input[name="cmtt_glossary_from_new"]');
            replace_to = $('.cmtt-glossary-replacement-add input[name="cmtt_glossary_to_new"]');
            replace_case = $('.cmtt-glossary-replacement-add input[name="cmtt_glossary_case_new"]');

            if (replace_from.val() === '') {
                replace_from.addClass('invalid');
                valid = false;
            }
            else {
                replace_from.removeClass('invalid');
            }

            if (replace_to.val() === '') {
                replace_to.addClass('invalid');
                valid = false;
            } else {
                replace_to.removeClass('invalid');
            }

            if (!valid) {
                return false;
            }

            data = {
                action: 'cmtt_add_replacement',
                replace_from: replace_from.val(),
                replace_to: replace_to.val(),
                replace_case: replace_case.is(':checked') ? 1 : 0
            };

            $('.glossary_loading').fadeIn('fast');

            $.post(window.cmtt_data.ajaxurl, data, function (response) {
                $('.cmtt_replacements_list').html(response);
                $('.glossary_loading').fadeOut('fast');

                replace_from.val('');
                replace_to.val('');
                replace_case.val('');
            });
        });

        $(document).on('click', '.cmtt-glossary-delete-replacement', function () {
            if (window.window.confirm('Do you really want to delete this replacement?')) {
                var data = {
                    action: 'cmtt_delete_replacement',
                    id: $(this).data('rid')
                };
                $('.glossary_loading').fadeIn('fast');
                $.post(window.cmtt_data.ajaxurl, data, function (response) {
                    $('.cmtt_replacements_list').html(response);
                    $('.glossary_loading').fadeOut('fast');
                });
            } else {
                $('.glossary_loading').fadeOut('fast');
            }
        });

        $(document).on('click', '.cmtt-glossary-update-replacement', function () {
            if (window.window.confirm('Do you really want to update this replacement?')) {

                var data, id, replace_from, replace_to, replace_case, valid = true;

                id = $(this).data('uid');
                replace_from = $('.cmtt_replacements_list input[name="cmtt_glossary_from[' + id + ']"]');
                replace_to = $('.cmtt_replacements_list input[name="cmtt_glossary_to[' + id + ']"]');
                replace_case = $('.cmtt_replacements_list input[name="cmtt_glossary_case[' + id + ']"]');

                if (replace_from.val() === '') {
                    replace_from.addClass('invalid');
                    valid = false;
                }
                else {
                    replace_from.removeClass('invalid');
                }

                if (replace_to.val() === '') {
                    replace_to.addClass('invalid');
                    valid = false;
                } else {
                    replace_to.removeClass('invalid');
                }

                if (!valid) {
                    return false;
                }

                data = {
                    action: 'cmtt_update_replacement',
                    replace_id: $(this).data('uid'),
                    replace_from: replace_from.val(),
                    replace_to: replace_to.val(),
                    replace_case: replace_case.is(':checked') ? 1 : 0
                };
                $('.glossary_loading').fadeIn('fast');
                $.post(window.cmtt_data.ajaxurl, data, function (response) {
                    $('.cmtt_replacements_list').html(response);
                    $('.glossary_loading').fadeOut('fast');
                });
            } else {
                $('.glossary_loading').fadeOut('fast');
            }
        });

        /*
         * RELATED ARTICLES
         */
        $.fn.add_new_replacement_row = function () {
            var articleRow, articleRowHtml, rowId;

            rowId = $(".custom-related-article").length;
            articleRow = $('<div class="custom-related-article"></div>');
            articleRowHtml = $('<input type="text" name="cmtt_related_article_name[]" style="width: 40%" id="cmtt_related_article_name" placeholder="Name"><input type="text" name="cmtt_related_article_url[]" style="width: 50%" id="cmtt_related_article_url" placeholder="http://"><a href="#javascript" class="cmtt_related_article_remove">Remove</a>');
            articleRow.append(articleRowHtml);
            articleRow.attr('id', 'custom-related-article-' + rowId);

            $("#glossary-related-article-list").append(articleRow);
            return false;
        };

        $.fn.delete_replacement_row = function (row_id) {
            $("#custom-related-article-" + row_id).remove();
            return false;
        };

        /*
         * Added in 2.7.7 remove replacement_row
         */
        $(document).on('click', 'a.cmtt_related_article_remove', function () {
            var $this = $(this), $parent;
            $parent = $this.parents('.custom-related-article').remove();
            return false;
        });

        /*
         * Added in 2.4.9 (shows/hides the explanations to the variations/synonyms/abbreviations)
         */
        $(document).on('click showHideInit', '.cm-showhide-handle', function () {
            var $this = $(this), $parent, $content;

            $parent = $this.parent();
            $content = $this.siblings('.cm-showhide-content');

            if (!$parent.hasClass('closed'))
            {
                $content.hide();
                $parent.addClass('closed');
            }
            else
            {
                $content.show();
                $parent.removeClass('closed');
            }
        });

        $('.cm-showhide-handle').trigger('showHideInit');

        /*
         * CUSTOM REPLACEMENTS - END
         */

        if ($.fn.tabs) {
            $('#cmtt_tabs').tabs({
                activate: function (event, ui) {
                    window.location.hash = ui.newPanel.attr('id').replace(/-/g, '_');
                },
                create: function (event, ui) {
                    var tab = location.hash.replace(/\_/g, '-');
                    var tabContainer = $(ui.panel.context).find('a[href="' + tab + '"]');
                    if (typeof tabContainer !== 'undefined' && tabContainer.length)
                    {
                        var index = tabContainer.parent().index();
                        $(ui.panel.context).tabs('option', 'active', index);
                    }
                }
            });
        }

        $('.cmtt_field_help_container').each(function () {
            var newElement,
                    element = $(this);

            newElement = $('<div class="cmtt_field_help"></div>');
            newElement.attr('title', element.html());

            if (element.siblings('th').length)
            {
                element.siblings('th').append(newElement);
            }
            else
            {
                element.siblings('*').append(newElement);
            }
            element.remove();
        });

        $('.cmtt_field_help').tooltip({
            show: {
                effect: "slideDown",
                delay: 100
            },
            position: {
                my: "left top",
                at: "right top"
            },
            content: function () {
                var element = $(this);
                return element.attr('title');
            },
            close: function (event, ui) {
                ui.tooltip.hover(
                        function () {
                            $(this).stop(true).fadeTo(400, 1);
                        },
                        function () {
                            $(this).fadeOut("400", function () {
                                $(this).remove();
                            });
                        });
            }
        });


        $('#cmtt_test_glosbe_dictionary_api').on('click', function () {
            var data = {
                action: 'cmtt_test_glosbe_dictionary_api'
            };
            $.post(window.cmtt_data.ajaxurl, data, function (response) {
                alert(response);
            });
        });

        $('#cmtt-test-dictionary-api').on('click', function () {
            var data = {
                action: 'cmtt_test_dictionary_api'
            };
            $.post(window.cmtt_data.ajaxurl, data, function (response) {
                alert(response);
            });
        });


        $('#cmtt-test-thesaurus-api').on('click', function () {
            var data = {
                action: 'cmtt_test_thesaurus_api'
            };
            $.post(window.cmtt_data.ajaxurl, data, function (response) {
                alert(response);
            });
        });

        $('#cmtt-test-google-api').on('click', function () {
            var data = {
                action: 'cmtt_test_google_api'
            };
            $.post(window.cmtt_data.ajaxurl, data, function (response) {
                alert(response);
            });
        });

    });

})(jQuery);