/*global console,ajaxurl,$,jQuery,megamenu,document,window,bstw,alert,wp,this*/
/**
 * Mega Menu jQuery Plugin
 */
(function($) {
    "use strict";

    $.fn.megaMenu = function(options) {

        var panel = $("<div />");

        panel.settings = options;

        panel.log = function(message) {
            if (window.console && console.log) {
                console.log(message.data);
            }

            if (message.success !== true) {
                alert(message.data);
            }
        };


        panel.init = function() {

            var isDirty = false;

            panel.log({
                success: true,
                data: megamenu.debug_launched + " " + panel.settings.menu_item_id
            });

            $.colorbox.remove();

            $.colorbox({
                html: "",
                initialWidth: "75%",
                scrolling: true,
                fixed: true,
                initialHeight: "552",
                onOpen: function() {
                    $('body').addClass('mega-colorbox-open');
                    isDirty = false;
                },
                onClosed: function() {
                    $('body').removeClass('mega-colorbox-open');
                    isDirty = false;
                }
            });

            var originalClose = $.colorbox.close;
            
            $.colorbox.close = function(){
                if ( isDirty == false ) {
                    originalClose();
                    return;
                }

                if ( confirm( megamenu.unsaved_changes ) ) {
                    originalClose();
                }
            };

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "mm_get_lightbox_html",
                    _wpnonce: megamenu.nonce,
                    menu_item_id: panel.settings.menu_item_id
                },
                cache: false,
                beforeSend: function() {
                    $("#cboxLoadedContent").empty();
                    $("#cboxClose").empty();
                },
                complete: function() {
                    $("#cboxLoadingOverlay").remove();

                },
                success: function(response) {

                    $("#cboxLoadingGraphic").remove();

                    var json = $.parseJSON(response.data);

                    var header_container = $("<div />").addClass("mm_header_container");
                    var title = $("<div />").addClass("mm_title");
                    var saving = $("<div class='mm_saving'>" + megamenu.saving + "</div>");

                    header_container.append(title).append(saving);

                    var active_tab = "mega_menu";
                    var tabs_container = $("<div class='mm_tab_container' />");
                    var content_container = $("<div class='mm_content_container' />");

                    if (json === null) {
                        content_container.html(response);
                    }

                    $.each(json, function(idx) {

                        if (idx === "title") {
                            title.html(this);
                            return;
                        }

                        if (idx === "active_tab") {
                            active_tab = (this);
                            return;
                        }

                        var content = $("<div />").addClass("mm_content").addClass(idx).html(this.content).hide();
                        
                        // bind save button action
                        content.find("form").on("submit", function(e) {
                            start_saving();
                            isDirty = false;
                            e.preventDefault();
                            var data = $(this).serialize();
                            $.post(ajaxurl, data, function(submit_response) {
                                end_saving();
                                panel.log(submit_response);
                            });
                        });

                        // register changes made
                        content.find("form").on("change", function(e) {
                            isDirty = true;
                        });

                        if (idx === "menu_icon") {
                            var form = content.find("form.icon_selector").not(".icon_selector_custom");

                            // bind save button action
                            form.on("change", function(e) {
                                start_saving();
                                isDirty = false;
                                e.preventDefault();
                                $("input", form).not(e.target).removeAttr("checked");
                                var data = $(this).serialize();
                                $.post(ajaxurl, data, function(submit_response) {
                                    end_saving();
                                    panel.log(submit_response);
                                });

                            });
                        }

                        if (idx === "general_settings") {
                            content.find("select#mega-item-align").on("change", function() {
                                var select = $(this);
                                var selected = $(this).val();
                                select.next().children().hide();
                                select.next().children("." + selected).show();
                            });
                        }

                        if (idx === "mega_menu") {
                            var submenu_type = content.find("#mm_enable_mega_menu");

                            submenu_type.parents(".mm_content.mega_menu").attr('data-type', submenu_type.val());
                            
                            submenu_type.on("change", function() {

                                submenu_type.parents(".mm_content.mega_menu").attr('data-type', submenu_type.val());

                                start_saving();

                                var postdata = {
                                    action: "mm_save_menu_item_settings",
                                    settings: {
                                        type: submenu_type.val()
                                    },
                                    menu_item_id: panel.settings.menu_item_id,
                                    _wpnonce: megamenu.nonce
                                };

                                $.post(ajaxurl, postdata, function(select_response) {
                                    end_saving();

                                    panel.log(select_response);
                                });

                            });

                            setup_megamenu(content);
                            setup_grid(content);

                        }

                        var tab = $("<div />").addClass("mm_tab").addClass(idx).html(this.title).on("click", function() {
                            $(".mm_content").hide();
                            $(".mm_tab").removeClass("active");
                            $(this).addClass("active");
                            content.show();
                        });

                        tabs_container.append(tab);

                        $(".mm_tab_horizontal", content).on("click", function() {
                            var tab = $(this);
                            var tab_id = $(this).attr("rel");

                            // reset search
                            $(".filter_icons").val("");
                            $(".icon_selector > div").show();

                            tab.addClass("active");
                            tab.siblings().removeClass("active");
                            tab.parent().siblings().not("h4").not("input").hide();
                            tab.parent().siblings("." + tab_id).show();
                        });

                        $(".filter_icons", content).on("keyup", function() {
                            var string = $(".filter_icons").val();

                            var all = $(".icon_selector:visible div input");

                            var filtered = all.filter(function() {
                                return $(this).attr("id").indexOf(string) > -1;
                            });

                            filtered.parent().show();
                            var others = all.not(filtered);
                            others.parent().hide();
                        });

                        content_container.append(content);
                    });

                    $(".mm_tab." + active_tab + ":first", tabs_container).click();

                    $("#cboxLoadedContent").append(header_container).append(tabs_container).append(content_container);
                    $("#cboxLoadedContent").css({
                        "width": "100%",
                        "height": "100%",
                        "display": "block"
                    });

                    $("#cboxLoadedContent").trigger("megamenu_content_loaded");
                }
            });
        };

        var setup_grid = function(content) {

            var grid = content.find("#megamenu-grid");

            content.find("#mm_widget_selector").on("change", function() {

                var submenu_type = content.find("#mm_enable_mega_menu");

                if (submenu_type.length && submenu_type.val() != "grid") {
                    return;
                }

                var selector = $(this);

                if (selector.val() != "disabled") {

                    var postdata = {
                        action: "mm_add_widget",
                        id_base: selector.val(),
                        menu_item_id: panel.settings.menu_item_id,
                        is_grid_widget: "true",
                        title: selector.find("option:selected").text(),
                        _wpnonce: megamenu.nonce
                    };

                    $.post(ajaxurl, postdata, function(response) {
                        var widget = $(response.data);
                        $(".mega-col-widgets:first").append(widget);

                        grid.trigger("make_columns_sortable");
                        grid.trigger("make_widgets_sortable");
                        grid.trigger("update_column_block_count");
                        grid.trigger("save_grid_data");

                        // reset the dropdown
                        selector.val("disabled");
                    });

                }
            });

            // Add Column
            grid.on("click", ".mega-add-column", function() {
                var button = $(this);
                var row = button.parent().parent();
                var used_cols = parseInt(row.attr('data-used-cols'));
                var available_cols = parseInt(row.attr('data-available-cols'));

                row.find(".mega-row-is-full").hide();

                if ( used_cols + 1 > available_cols ) {
                    row.find(".mega-row-is-full").slideDown().delay(2000).slideUp();
                    return;
                }

                var space_left_on_row = available_cols - used_cols;

                var data = {
                    action: "mm_get_empty_grid_column",
                    _wpnonce: megamenu.nonce
                };

                $.post(ajaxurl, data, function(response) {
                    var column = $(response.data);

                    if (space_left_on_row < 3) {
                        column.attr('data-span', space_left_on_row);
                        column.find('.mega-num-cols').html(space_left_on_row);
                    }

                    button.parent().parent().append(column);

                    grid.trigger("make_columns_sortable");
                    grid.trigger("make_widgets_sortable");
                    grid.trigger("save_grid_data");
                    grid.trigger("update_row_column_count");
                    grid.trigger("update_column_block_count");
                });
            });

            // Delete Column
            grid.on("click", ".mega-col-description > .dashicons-trash", function() {
                $(this).closest(".mega-col").remove();

                grid.trigger("save_grid_data");
                grid.trigger("update_row_column_count");
            });

            // Add Row
            grid.on("click", ".mega-add-row", function() {
                var button = $(this);
                var data = {
                    action: "mm_get_empty_grid_row",
                    _wpnonce: megamenu.nonce
                };

                $.post(ajaxurl, data, function(response) {
                    var row = $(response.data);
                    button.before(row);

                    grid.trigger("make_columns_sortable");
                    grid.trigger("make_widgets_sortable");
                    grid.trigger("save_grid_data");
                    grid.trigger("update_row_column_count");
                    grid.trigger("update_column_block_count");

                });
            });

            // Delete Row
            grid.on("click", ".mega-row-actions > .dashicons-trash", function() {
                $(this).closest(".mega-row").remove();

                grid.trigger("save_grid_data");
            });

            // Expand Column
            grid.on("click", ".mega-col-expand", function() {

                var column = $(this).closest(".mega-col");
                var cols = parseInt(column.attr("data-span"), 10);

                if (cols < 12) {
                    cols = cols + 1;

                    column.attr("data-span", cols);

                    $(".mega-num-cols", column).html(cols);

                    grid.trigger("save_grid_data");
                    grid.trigger("update_row_column_count");
                }
            });

            // Contract Column
            grid.on("click", ".mega-col-contract", function() {

                var column = $(this).closest(".mega-col");

                var cols = parseInt(column.attr("data-span"), 10);

                if (cols > 1) {
                    cols = cols - 1;

                    column.attr("data-span", cols);

                    $(".mega-num-cols", column).html(cols);

                    grid.trigger("save_grid_data");
                    grid.trigger("update_row_column_count");
                }

            });

            grid.on("click", ".widget-action", function() {

                var action = "mm_edit_widget";

                if ($(this).parent().parent().parent().attr('data-type') == 'item') {
                    action = "mm_edit_menu_item";
                }

                var widget = $(this).closest(".widget");
                var widget_title = widget.find("h4");
                var id = widget.attr("data-id");
                var widget_inner = widget.find(".widget-inner");

                if (!widget.hasClass("open") && !widget.data("loaded")) {

                    widget_title.addClass("loading");

                    // retrieve the widget settings form
                    $.post(ajaxurl, {
                        action: action,
                        widget_id: id,
                        _wpnonce: megamenu.nonce
                    }, function(response) {

                        var $response = $(response);
                        var $form = $response;

                        // bind delete button action
                        $(".delete", $form).on("click", function(e) {
                            e.preventDefault();

                            widget.remove();

                            var data = {
                                action: "mm_delete_widget",
                                widget_id: id,
                                _wpnonce: megamenu.nonce
                            };

                            $.post(ajaxurl, data, function(delete_response) {
                                panel.log(delete_response);
                                grid.trigger("save_grid_data");
                                grid.trigger("update_column_block_count");
                            });

                        });

                        // bind close button action
                        $(".close", $form).on("click", function(e) {
                            e.preventDefault();

                            widget.toggleClass("open");
                        });

                        // bind save button action
                        $form.on("submit", function(e) {
                            e.preventDefault();

                            var data = $(this).serialize();

                            start_saving();

                            $.post(ajaxurl, data, function(submit_response) {
                                end_saving();
                                panel.log(submit_response);
                            });

                        });

                        widget_inner.html($response);

                        widget.data("loaded", true).toggleClass("open");

                        grid.trigger("check_widget_inner_position", [widget_inner]);

                        widget_title.removeClass("loading");

                        // Init Black Studio TinyMCE
                        if (widget.is("[id*=black-studio-tinymce]")) {
                            bstw(widget).deactivate().activate();
                        }

                        setTimeout(function(){
                            // fix for WordPress 4.8 widgets when lightbox is opened, closed and reopened
                            if (wp.textWidgets !== undefined) {
                                wp.textWidgets.widgetControls = {}; // WordPress 4.8 Text Widget
                            }

                            if (wp.mediaWidgets !== undefined) {
                                wp.mediaWidgets.widgetControls = {}; // WordPress 4.8 Media Widgets
                            }

                            if (wp.customHtmlWidgets !== undefined) {
                                wp.customHtmlWidgets.widgetControls = {}; // WordPress 4.9 Custom HTML Widgets
                            }

                            $(document).trigger("widget-added", [widget]);

                            if ('acf' in window) {
                                acf.getFields(document);
                            }
                        }, 100);

                    });

                } else {
                    widget.toggleClass("open");
                }

                grid.trigger("check_widget_inner_position", [widget_inner]);

                // close all other widgets
                $(".widget").not(widget).removeClass("open");

            });


            // Contract Column
            grid.on("click", ".mega-col-header .dashicons-admin-generic", function() {
                $(this).toggleClass('mega-settings-open');
                $(this).closest(".mega-col").find(".mega-col-settings").slideToggle();
            });


            grid.on("click", ".mega-row-header .dashicons-admin-generic", function() {
                $(this).toggleClass('mega-settings-open');
                $(this).closest(".mega-row").find(".mega-row-settings").slideToggle();
            });

            grid.on("keyup", ".widget-content input[name*='[title]'], .media-widget-control [id*='_title'].title, .custom-html-widget-fields [id*='_title'].title", function() {
                var title = $(this).val();

                if (title.length == 0) {
                    var desc = $(this).closest(".widget").find(".widget-title .widget-desc").html();
                    $(this).closest(".widget").find(".widget-title h4").html(desc);
                } else {
                    $(this).closest(".widget").find(".widget-title h4").html(title);
                }
            });

            grid.on("click", ".dashicons-desktop", function() {
                var icon = $(this);
                var input = $(this).parent().parent().parent().parent().find("input[name='mega-hide-on-desktop']");
                var tooltip = $(this).parent();

                if (input.val() == "true") {
                    input.val("false");
                    tooltip.removeClass("mega-disabled").addClass("mega-enabled");
                } else {
                    input.val("true");
                    tooltip.removeClass("mega-enabled").addClass("mega-disabled");
                }

                grid.trigger("save_grid_data");
            });


            grid.on("click", ".dashicons-smartphone", function() {
                var icon = $(this);
                var input = $(this).parent().parent().parent().parent().find("input[name='mega-hide-on-mobile']");
                var tooltip = $(this).parent();

                if (input.val() == "true") {
                    input.val("false");
                    tooltip.removeClass("mega-disabled").addClass("mega-enabled");
                } else {
                    input.val("true");
                    tooltip.removeClass("mega-enabled").addClass("mega-disabled");
                }

                grid.trigger("save_grid_data");
            });

            grid.on("click", ".mega-save-column-settings, .mega-save-row-settings", function() {
                grid.trigger("save_grid_data");
            });

            grid.on("click", ".mega-save-row-settings", function() {
                grid.trigger("update_total_columns_in_row");
            });

            grid.on("check_widget_inner_position", function(event, widget_inner) {
                var widget_inner_right_edge = widget_inner.offset().left + widget_inner.width();
                var content_right_edge = $(".mm_content_container").offset().left + $(".mm_content_container").width();

                if (widget_inner_right_edge > content_right_edge) {
                    widget_inner.css("right", "0");
                } else {
                    widget_inner.css("right", "");
                }
            });

            grid.on("save_grid_data", function() {
                start_saving();

                var rows = [];
                var cols = [];

                $(".mega-row", grid).each(function() {
                    var row_index = $(this).index();
                    var row_hide_on_desktop = $(this).find("input[name='mega-hide-on-desktop']").val();
                    var row_hide_on_mobile = $(this).find("input[name='mega-hide-on-mobile']").val();
                    var row_class = $(this).find("input.mega-row-class").val();
                    var row_columns = $(this).find("select.mega-row-columns").val();

                    rows[row_index] = {
                        "meta": {
                            "class": row_class,
                            "hide-on-desktop": row_hide_on_desktop,
                            "hide-on-mobile": row_hide_on_mobile,
                            "columns": row_columns
                        },
                        "columns": []
                    };
                });

                $(".mega-col", grid).each(function() {
                    var col_index = $(this).parent().children(".mega-col").index($(this));
                    var row_index = $(this).parent(".mega-row").index();
                    var col_span = $(this).attr("data-span");
                    var col_hide_on_desktop = $(this).find("input[name='mega-hide-on-desktop']").val();
                    var col_hide_on_mobile = $(this).find("input[name='mega-hide-on-mobile']").val();
                    var col_class = $(this).find("input.mega-column-class").val();

                    rows[row_index]["columns"][col_index] = {
                        "meta": {
                            "span": col_span,
                            "class": col_class,
                            "hide-on-desktop": col_hide_on_desktop,
                            "hide-on-mobile": col_hide_on_mobile
                        },
                        "items": []
                    };
                });

                $(".widget", grid).each(function() {
                    var block_index = $(this).index();
                    var id = $(this).attr("data-id");
                    var type = $(this).attr("data-type");
                    var row_index = $(this).closest(".mega-row").index();
                    var col = $(this).closest(".mega-col");
                    var col_index = col.parent().children(".mega-col").index(col);

                    var widget = {
                        "id": id,
                        "type": type
                    };

                    rows[row_index]["columns"][col_index]["items"].push(widget);
                });

                $.post(ajaxurl, {
                    action: "mm_save_grid_data",
                    grid: rows,
                    parent_menu_item: panel.settings.menu_item_id,
                    _wpnonce: megamenu.nonce
                }, function(move_response) {
                    end_saving();
                });

                grid.trigger("update_row_column_count");

            });

            grid.on("update_total_columns_in_row", function() {
                $(".mega-row", grid).each(function() {
                    var row = $(this);
                    var total_cols = $(this).find("select.mega-row-columns").val();
                    $(this).attr('data-available-cols', total_cols);
                    
                    $(".mega-col", row).not(".ui-sortable-helper").each(function() {
                        var col = $(this);
                        
                        $(this).find('.mega-num-total-cols').html(total_cols);
                    });
                });
            });

            grid.on("update_row_column_count", function() {

                grid.trigger("update_total_columns_in_row");

                $(".mega-row", grid).each(function() {
                    var row = $(this);
                    var used_cols = 0;
                    var available_cols = row.attr("data-available-cols");

                    $(".mega-col", row).not(".ui-sortable-helper").each(function() {
                        var col = $(this);
                        used_cols = used_cols + parseInt(col.attr("data-span"), 10);
                    });

                    row.attr("data-used-cols", used_cols);

                    row.removeAttr("data-too-many-cols");
                    row.removeAttr("data-row-is-full");

                    if ( used_cols > available_cols ) {
                        row.attr("data-too-many-cols", "true");
                    }

                    if ( used_cols == available_cols ) {
                        row.attr("data-row-is-full", "true");
                    }
                });
            });

            grid.on("update_column_block_count", function() {
                $(".mega-col", grid).each(function() {
                    var col = $(this);
                    col.attr("data-total-blocks", $(".mega-col-widgets > .widget", col).length);
                });
            });

            grid.on("make_rows_sortable", function() {
                // sortable row
                grid.sortable({
                    forcePlaceholderSize: true,
                    items: ".mega-row",
                    placeholder: "drop-area",
                    handle: ".mega-row-header > .mega-row-actions > .dashicons-sort",
                    tolerance: "pointer",
                    start: function(event, ui) {
                        $(".widget").removeClass("open");
                        ui.item.data("start_pos", ui.item.index());
                    },
                    stop: function(event, ui) {
                        // clean up
                        ui.item.removeAttr("style");

                        var start_pos = ui.item.data("start_pos");

                        if (start_pos !== ui.item.index()) {
                            grid.trigger("save_grid_data");
                        }
                    }
                });
            });

            grid.on("make_widgets_sortable", function() {
                // sortable widgets
                var cols = grid.find(".mega-col-widgets");

                cols.sortable({
                    connectWith: ".mega-col-widgets",
                    forcePlaceholderSize: true,
                    items: ".widget",
                    placeholder: "drop-area",
                    handle: ".widget-top",
                    helper: "clone",
                    tolerance: "pointer",
                    start: function(event, ui) {
                        $(".widget").removeClass("open");
                        ui.item.css("margin-top", $(window).scrollTop());

                    },
                    stop: function(event, ui) {
                        // clean up
                        ui.item.removeAttr("style");

                        grid.trigger("save_grid_data");
                        grid.trigger("update_column_block_count");
                    }
                });
            });

            grid.on("make_columns_sortable", function() {
                // sortable columns
                var rows = grid.find(".mega-row");

                rows.sortable({
                    connectWith: ".mega-row",
                    forcePlaceholderSize: false,
                    items: ".mega-col",
                    placeholder: "drop-area",
                    tolerance: "pointer",
                    handle: ".mega-col-header > .mega-col-description > .dashicons-move",
                    start: function(event, ui) {
                        ui.placeholder.height(ui.helper[0].scrollHeight);
                        ui.placeholder.width(ui.item.width() - 1);
                        $(".widget").removeClass("open");
                    },
                    sort: function(event, ui) {
                        grid.trigger("update_row_column_count");
                    },
                    stop: function(event, ui) {
                        grid.trigger("save_grid_data");

                        // clean up
                        ui.item.removeAttr("style");

                        grid.trigger("update_row_column_count");
                    }
                });
            });

            grid.trigger("update_row_column_count");
            grid.trigger("update_column_block_count");
            grid.trigger("make_rows_sortable");
            grid.trigger("make_columns_sortable");
            grid.trigger("make_widgets_sortable");

        }


        var setup_megamenu = function(content) {

            var megamenubuilder = content.find("#widgets");

            content.find("#mm_number_of_columns").on("change", function() {

                megamenubuilder.attr("data-columns", $(this).val());

                megamenubuilder.find(".widget-total-cols").html($(this).val());

                start_saving();

                var postdata = {
                    action: "mm_save_menu_item_settings",
                    settings: {
                        panel_columns: $(this).val()
                    },
                    menu_item_id: panel.settings.menu_item_id,
                    _wpnonce: megamenu.nonce
                };

                $.post(ajaxurl, postdata, function(select_response) {
                    end_saving();
                    panel.log(select_response);
                });

            });

            megamenubuilder.bind("reorder_widgets", function() {
                start_saving();

                var items = [];

                $(".widget").each(function() {
                    items.push({
                        "type": $(this).attr("data-type"),
                        "order": $(this).index() + 1,
                        "id": $(this).attr("data-id"),
                        "parent_menu_item": panel.settings.menu_item_id
                    });
                });

                $.post(ajaxurl, {
                    action: "mm_reorder_items",
                    items: items,
                    _wpnonce: megamenu.nonce
                }, function(move_response) {
                    end_saving();
                    panel.log(move_response);
                });
            });

            megamenubuilder.sortable({
                forcePlaceholderSize: true,
                items: ".widget:not(.sub_menu)",
                placeholder: "drop-area",
                handle: ".widget-top",
                start: function(event, ui) {
                    $(".widget").removeClass("open");
                    ui.item.data("start_pos", ui.item.index());
                },
                stop: function(event, ui) {
                    // clean up
                    ui.item.removeAttr("style");

                    var start_pos = ui.item.data("start_pos");

                    if (start_pos !== ui.item.index()) {
                        megamenubuilder.trigger("reorder_widgets");
                    }
                }
            });

            content.find("#mm_widget_selector").on("change", function() {

                var submenu_type = content.find("#mm_enable_mega_menu");

                if (submenu_type.length && submenu_type.val() != "megamenu") {
                    return;
                }

                var selector = $(this);

                if (selector.val() != "disabled") {

                    start_saving();

                    var postdata = {
                        action: "mm_add_widget",
                        id_base: selector.val(),
                        menu_item_id: panel.settings.menu_item_id,
                        title: selector.find("option:selected").text(),
                        _wpnonce: megamenu.nonce
                    };

                    $.post(ajaxurl, postdata, function(response) {
                        $(".no_widgets").hide();
                        var widget = $(response.data);
                        var number_of_columns = content.find("#mm_number_of_columns").val();
                        widget.find(".widget-total-cols").html(number_of_columns);
                        $("#widgets").append(widget);
                        megamenubuilder.trigger("reorder_widgets");
                        end_saving();
                        // reset the dropdown
                        selector.val("disabled");
                    });

                }

            });

            megamenubuilder.on("click", ".widget .widget-expand", function() {
                var widget = $(this).closest(".widget");
                var type = widget.attr("data-type");
                var id = widget.attr("id");
                var cols = parseInt(widget.attr("data-columns"), 10);
                var maxcols = parseInt($("#mm_number_of_columns").val(), 10);

                if (cols < maxcols) {
                    cols = cols + 1;

                    widget.attr("data-columns", cols);

                    $(".widget-num-cols", widget).html(cols);

                    start_saving();

                    if (type == "widget") {

                        $.post(ajaxurl, {
                            action: "mm_update_widget_columns",
                            id: id,
                            columns: cols,
                            _wpnonce: megamenu.nonce
                        }, function(expand_response) {
                            end_saving();
                            panel.log(expand_response);
                        });

                    }

                    if (type == "menu_item") {

                        $.post(ajaxurl, {
                            action: "mm_update_menu_item_columns",
                            id: id,
                            columns: cols,
                            _wpnonce: megamenu.nonce
                        }, function(contract_response) {
                            end_saving();
                            panel.log(contract_response);
                        });

                    }

                }

            });

            megamenubuilder.on("click", ".widget .widget-contract", function() {
                var widget = $(this).closest(".widget");
                var type = widget.attr("data-type");
                var id = widget.attr("id");
                var cols = parseInt(widget.attr("data-columns"), 10);

                // account for widgets that have say 8 columns but the panel is only 6 wide
                var maxcols = parseInt($("#mm_number_of_columns").val(), 10);

                if (cols > maxcols) {
                    cols = maxcols;
                }

                if (cols > 1) {
                    cols = cols - 1;
                    widget.attr("data-columns", cols);

                    $(".widget-num-cols", widget).html(cols);
                } else {
                    return;
                }

                start_saving();

                if (type == "widget") {

                    $.post(ajaxurl, {
                        action: "mm_update_widget_columns",
                        id: id,
                        columns: cols,
                        _wpnonce: megamenu.nonce
                    }, function(contract_response) {
                        end_saving();
                        panel.log(contract_response);
                    });

                }

                if (type == "menu_item") {

                    $.post(ajaxurl, {
                        action: "mm_update_menu_item_columns",
                        id: id,
                        columns: cols,
                        _wpnonce: megamenu.nonce
                    }, function(contract_response) {
                        end_saving();
                        panel.log(contract_response);
                    });

                }

            });


            megamenubuilder.on("click", ".widget .widget-action", function() {

                var action = "mm_edit_widget";

                if ($(this).parent().parent().parent().attr('data-type') == 'menu_item') {
                    action = "mm_edit_menu_item";
                }

                var widget = $(this).closest(".widget");
                var widget_title = widget.find(".widget-title");
                var widget_inner = widget.find(".widget-inner");
                var id = widget.attr("id");

                if (!widget.hasClass("open") && !widget.data("loaded")) {

                    widget_title.addClass("loading");

                    // retrieve the widget settings form
                    $.post(ajaxurl, {
                        action: action,
                        widget_id: id,
                        _wpnonce: megamenu.nonce
                    }, function(response) {

                        var $response = $(response);
                        var $form = $response;

                        // bind delete button action
                        $(".delete", $form).on("click", function(e) {
                            e.preventDefault();

                            var data = {
                                action: "mm_delete_widget",
                                widget_id: id,
                                _wpnonce: megamenu.nonce
                            };

                            $.post(ajaxurl, data, function(delete_response) {
                                widget.remove();
                                panel.log(delete_response);
                            });

                        });

                        // bind close button action
                        $(".close", $form).on("click", function(e) {
                            e.preventDefault();

                            widget.toggleClass("open");
                        });

                        // bind save button action
                        $form.on("submit", function(e) {
                            e.preventDefault();

                            var data = $(this).serialize();

                            start_saving();

                            $.post(ajaxurl, data, function(submit_response) {
                                end_saving();
                                panel.log(submit_response);
                            });

                        });

                        widget_inner.html($response);

                        widget.data("loaded", true).toggleClass("open");

                        widget_title.removeClass("loading");

                        // Init Black Studio TinyMCE
                        if (widget.is('[id*=black-studio-tinymce]')) {
                            bstw(widget).deactivate().activate();
                        }

                        setTimeout(function(){
                            // fix for WordPress 4.8 widgets when lightbox is opened, closed and reopened
                            if (wp.textWidgets !== undefined) {
                                wp.textWidgets.widgetControls = {}; // WordPress 4.8 Text Widget
                            }

                            if (wp.mediaWidgets !== undefined) {
                                wp.mediaWidgets.widgetControls = {}; // WordPress 4.8 Media Widgets
                            }

                            if (wp.customHtmlWidgets !== undefined) {
                                wp.customHtmlWidgets.widgetControls = {}; // WordPress 4.9 Custom HTML Widgets
                            }
                            
                            $(document).trigger("widget-added", [widget]);

                            if ('acf' in window) {
                                acf.getFields(document);
                            }

                        }, 100);


                    });

                } else {
                    widget.toggleClass("open");
                }

                // close all other widgets
                $(".widget").not(widget).removeClass("open");

            });

        }

        var start_saving = function() {
            var original_value = $(".mm_content:visible p.submit input.button-primary").attr('value');
            $(".mm_content:visible p.submit input.button-primary").addClass('is-busy').attr('value', megamenu.saving + "…").attr('data-orig-value', original_value);
            $(".mm_saving").show();
        }

        var end_saving = function() {
            var original_value = $(".mm_content:visible p.submit input.button-primary").attr('data-orig-value');
            $(".mm_content:visible p.submit input.button-primary").removeClass("is-busy").attr('value', original_value);
            $(".mm_saving").fadeOut("fast");
        }

        panel.init();

    };

}(jQuery));

/**
 *
 */
jQuery(function($) {
    "use strict";

    var apply_megamenu_enabled_class = function() {
        if ($("input.megamenu_enabled:checked") && $("input.megamenu_enabled:checked").length) {
            $("body").addClass("megamenu_enabled");
        } else {
            $("body").removeClass("megamenu_enabled");
        }
    }

    $("input.megamenu_enabled").on("change", function() {
        apply_megamenu_enabled_class();
    });

    apply_megamenu_enabled_class();

    $("#menu-to-edit li.menu-item").each(function() {

        var menu_item = $(this);

        menu_item.data("megamenu_has_button", "true");

        var id = parseInt(menu_item.attr("id").match(/[0-9]+/)[0], 10);

        var button = $("<span>").addClass("mm_launch")
            .html(megamenu.launch_lightbox)
            .on("click", function(e) {
                e.preventDefault();

                if (!$("body").hasClass("megamenu_enabled")) {
                    alert(megamenu.is_disabled_error);
                    return;
                }

                $(this).megaMenu({
                    menu_item_id: id
                });
            });

        $(".item-title", menu_item).append(button);

        if (megamenu.css_prefix === "true") {
            var custom_css_classes = menu_item.find(".edit-menu-item-classes");
            var css_prefix = $("<span>").addClass("mm_prefix").html(megamenu.css_prefix_message);
            custom_css_classes.after(css_prefix);
        }

    });

    $(".megamenu_enabled #menu-to-edit").on("mouseenter mouseleave", "li.menu-item", function() {
        var menu_item = $(this);

        if (!menu_item.data("megamenu_has_button")) {

            menu_item.data("megamenu_has_button", "true");

            var button = $("<span>").addClass("mm_launch mm_disabled")
                .html(megamenu.launch_lightbox)
                .on("click", function(e) {
                    e.preventDefault();
                    alert(megamenu.save_menu);
                });

            $(".item-title", menu_item).append(button);
        }
    });


    // AJAX Save MMM Settings
    $(".max-mega-menu-save").on("click", function(e) {
        e.preventDefault();

        $(".mega_menu_meta_box .spinner").css("visibility", "visible");

        var settings = JSON.stringify($("[name^='megamenu_meta']").serializeArray());
        var button = $(this);
        var enabled = button.parent().parent().find(".megamenu_enabled");
        var title = button.parent().parent().prev();
        // retrieve the widget settings form
        $.post(ajaxurl, {
            action: "mm_save_settings",
            menu: $("#menu").val(),
            megamenu_meta: settings,
            nonce: megamenu.nonce
        }, function(response) {
            $(".mega_menu_meta_box .spinner").css("visibility", "hidden");

            if ( enabled.is(":checked") ) {
                title.addClass("mega-location-enabled");
            } else {
                title.removeClass("mega-location-enabled");
            }
        });
    });

    $(".mega-accordion-title").on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var title = $(this);
        var content = title.next('.mega-accordion-content');

        $(".mega-accordion-content:visible").slideUp('fast');

        if ( content.is(':hidden') ) {
            content.slideDown('fast');
            title.addClass('mega-accordion-open');
        } else {
            content.slideUp('fast', function() {
                title.removeClass('mega-accordion-open');
            });
        }
    });

    $(".mega-ellipsis").on('click', function(e) {
        e.stopPropagation();

        var ellipsis = $(this);

        $(".mega-ellipsis").not(ellipsis).removeClass('mega-ellipsis-open');

        if ( ellipsis.hasClass('mega-ellipsis-open') ) {
            ellipsis.removeClass('mega-ellipsis-open');
        } else {
            ellipsis.addClass('mega-ellipsis-open');
        }
    });

    $(document).on("click", function(e) { // hide menu when clicked away from
        if ( ! $(e.target).closest(".mega-ellipsis").length ) {
            $(".mega-ellipsis").removeClass('mega-ellipsis-open');
        }
    });
    
});