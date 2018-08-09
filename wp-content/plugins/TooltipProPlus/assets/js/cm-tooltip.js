
( function ( $ ) {

    $( document ).ready( function () {
        // Media Uploader
        $( '.CM_Media_Uploader .upload_image_button' ).click( function () {
            var $container = $( this ).closest( '.CM_Media_Uploader' );
            var $inputStorage = $container.find( '.cmtt_Media_Storage' );
            var $imageHolder = $container.find( '.cmtt_Media_Image' );
            wp.media.editor.send.attachment = function ( props, attachment ) {
                $inputStorage.val( attachment.id );
                $imageHolder.css( { 'background-image': 'url(' + attachment.url + ')' } ).addClass( 'cmtt_hasThumb' );
            }
            wp.media.editor.open( this );
            return false;
        } );
        $( '.cmtt_Media_Image' ).click( function () {
            var $t = $( this );
            var $container = $t.closest( '.CM_Media_Uploader' );
            var $inputStorage = $container.find( '.cmtt_Media_Storage' );
            if ( $t.hasClass( 'cmtt_hasThumb' ) ) {
                $t.css( { 'background-image': '' } ).removeClass( 'cmtt_hasThumb' )
                    .next( 'input[type="hidden"]' ).val( '' );
                $inputStorage.val( '' );
            }
        } ); // End

        // Add Color Picker to all inputs that have 'color-field' class
        $( function () {
            $( 'input[type="text"].colorpicker' ).wpColorPicker();
        } );

        /*
         * CUSTOM REPLACEMENTS
         */

        $( document ).on( 'click', '#cmtt-glossary-add-replacement-btn', function () {
            var data, replace_from, replace_to, replace_case, valid = true;

            replace_from = $( '.cmtt-glossary-replacement-add input[name="cmtt_glossary_from_new"]' );
            replace_to = $( '.cmtt-glossary-replacement-add input[name="cmtt_glossary_to_new"]' );
            replace_case = $( '.cmtt-glossary-replacement-add input[name="cmtt_glossary_case_new"]' );

            if ( replace_from.val() === '' ) {
                replace_from.addClass( 'invalid' );
                valid = false;
            } else {
                replace_from.removeClass( 'invalid' );
            }

            if ( replace_to.val() === '' ) {
                replace_to.addClass( 'invalid' );
                valid = false;
            } else {
                replace_to.removeClass( 'invalid' );
            }

            if ( !valid ) {
                return false;
            }

            data = {
                action: 'cmtt_add_replacement',
                replace_from: replace_from.val(),
                replace_to: replace_to.val(),
                replace_case: replace_case.is( ':checked' ) ? 1 : 0
            };

            $( '.glossary_loading' ).fadeIn( 'fast' );

            $.post( window.cmtt_data.ajaxurl, data, function ( response ) {
                $( '.cmtt_replacements_list' ).html( response );
                $( '.glossary_loading' ).fadeOut( 'fast' );

                replace_from.val( '' );
                replace_to.val( '' );
                replace_case.val( '' );
            } );
        } );

        $( document ).on( 'click', '.cmtt-glossary-delete-replacement', function () {
            if ( window.window.confirm( 'Do you really want to delete this replacement?' ) ) {
                var data = {
                    action: 'cmtt_delete_replacement',
                    id: $( this ).data( 'rid' )
                };
                $( '.glossary_loading' ).fadeIn( 'fast' );
                $.post( window.cmtt_data.ajaxurl, data, function ( response ) {
                    $( '.cmtt_replacements_list' ).html( response );
                    $( '.glossary_loading' ).fadeOut( 'fast' );
                } );
            } else {
                $( '.glossary_loading' ).fadeOut( 'fast' );
            }
        } );

        $( document ).on( 'click', '.cmtt-glossary-update-replacement', function () {
            if ( window.window.confirm( 'Do you really want to update this replacement?' ) ) {

                var data, id, replace_from, replace_to, replace_case, valid = true;

                id = $( this ).data( 'uid' );
                replace_from = $( '.cmtt_replacements_list input[name="cmtt_glossary_from[' + id + ']"]' );
                replace_to = $( '.cmtt_replacements_list input[name="cmtt_glossary_to[' + id + ']"]' );
                replace_case = $( '.cmtt_replacements_list input[name="cmtt_glossary_case[' + id + ']"]' );

                if ( replace_from.val() === '' ) {
                    replace_from.addClass( 'invalid' );
                    valid = false;
                } else {
                    replace_from.removeClass( 'invalid' );
                }

                if ( replace_to.val() === '' ) {
                    replace_to.addClass( 'invalid' );
                    valid = false;
                } else {
                    replace_to.removeClass( 'invalid' );
                }

                if ( !valid ) {
                    return false;
                }

                data = {
                    action: 'cmtt_update_replacement',
                    replace_id: $( this ).data( 'uid' ),
                    replace_from: replace_from.val(),
                    replace_to: replace_to.val(),
                    replace_case: replace_case.is( ':checked' ) ? 1 : 0
                };
                $( '.glossary_loading' ).fadeIn( 'fast' );
                $.post( window.cmtt_data.ajaxurl, data, function ( response ) {
                    $( '.cmtt_replacements_list' ).html( response );
                    $( '.glossary_loading' ).fadeOut( 'fast' );
                } );
            } else {
                $( '.glossary_loading' ).fadeOut( 'fast' );
            }
        } );

        /*
         * RELATED ARTICLES
         */
        $.fn.add_new_replacement_row = function () {
            var articleRow, articleRowHtml, rowId;

            rowId = $( ".custom-related-article" ).length;
            articleRow = $( '<div class="custom-related-article"></div>' );
            articleRowHtml = $( '<input type="text" name="cmtt_related_article_name[]" style="width: 40%" id="cmtt_related_article_name" placeholder="Name"><input type="text" name="cmtt_related_article_url[]" style="width: 50%" id="cmtt_related_article_url" placeholder="http://"><a href="#javascript" class="cmtt_related_article_remove">Remove</a>' );
            articleRow.append( articleRowHtml );
            articleRow.attr( 'id', 'custom-related-article-' + rowId );

            $( "#glossary-related-article-list" ).append( articleRow );
            return false;
        };

        $.fn.delete_replacement_row = function ( row_id ) {
            $( "#custom-related-article-" + row_id ).remove();
            return false;
        };

        /*
         * Added in 2.7.7 remove replacement_row
         */
        $( document ).on( 'click', 'a.cmtt_related_article_remove', function () {
            var $this = $( this ), $parent;
            $parent = $this.parents( '.custom-related-article' ).remove();
            return false;
        } );

        /*
         * Added in 2.4.9 (shows/hides the explanations to the variations/synonyms/abbreviations)
         */
        $( document ).on( 'click showHideInit', '.cm-showhide-handle', function () {
            var $this = $( this ), $parent, $content;

            $parent = $this.parent();
            $content = $this.siblings( '.cm-showhide-content' );

            if ( !$parent.hasClass( 'closed' ) )
            {
                $content.hide();
                $parent.addClass( 'closed' );
            } else
            {
                $content.show();
                $parent.removeClass( 'closed' );
            }
        } );

        $( '.cm-showhide-handle' ).trigger( 'showHideInit' );

        /*
         * CUSTOM REPLACEMENTS - END
         */

        if ( $.fn.tabs ) {
            $( '#cmtt_tabs' ).tabs( {
                activate: function ( event, ui ) {
                    window.location.hash = ui.newPanel.attr( 'id' ).replace( /-/g, '_' );
                },
                create: function ( event, ui ) {
                    var tab = location.hash.replace( /\_/g, '-' );
                    var tabContainer = $( ui.panel.context ).find( 'a[href="' + tab + '"]' );
                    if ( typeof tabContainer !== 'undefined' && tabContainer.length )
                    {
                        var index = tabContainer.parent().index();
                        $( ui.panel.context ).tabs( 'option', 'active', index );
                    }
                }
            } );
        }

        $( '.cmtt_field_help_container' ).each( function () {
            var newElement,
                element = $( this );

            newElement = $( '<div class="cmtt_field_help"></div>' );
            newElement.attr( 'title', element.html() );

            if ( element.siblings( 'th' ).length )
            {
                element.siblings( 'th' ).append( newElement );
            } else
            {
                element.siblings( '*' ).append( newElement );
            }
            element.remove();
        } );

        $( '.cmtt_field_help' ).tooltip( {
            show: {
                effect: "slideDown",
                delay: 100
            },
            position: {
                my: "left top",
                at: "right top"
            },
            content: function () {
                var element = $( this );
                return element.attr( 'title' );
            },
            close: function ( event, ui ) {
                ui.tooltip.hover(
                    function () {
                        $( this ).stop( true ).fadeTo( 400, 1 );
                    },
                    function () {
                        $( this ).fadeOut( "400", function () {
                            $( this ).remove();
                        } );
                    } );
            }
        } );


        $( '#cmtt_test_glosbe_dictionary_api' ).on( 'click', function () {
            var data = {
                action: 'cmtt_test_glosbe_dictionary_api'
            };
            $.post( window.cmtt_data.ajaxurl, data, function ( response ) {
                alert( response );
            } );
        } );

        $( '#cmtt-test-dictionary-api' ).on( 'click', function () {
            var data = {
                action: 'cmtt_test_dictionary_api'
            };
            $.post( window.cmtt_data.ajaxurl, data, function ( response ) {
                alert( response );
            } );
        } );


        $( '#cmtt-test-thesaurus-api' ).on( 'click', function () {
            var data = {
                action: 'cmtt_test_thesaurus_api'
            };
            $.post( window.cmtt_data.ajaxurl, data, function ( response ) {
                alert( response );
            } );
        } );

        $( '#cmtt-test-google-api' ).on( 'click', function () {
            var data = {
                action: 'cmtt_test_google_api'
            };
            $.post( window.cmtt_data.ajaxurl, data, function ( response ) {
                alert( response );
            } );
        } );

    } );

//QuickEditor

    if ( typeof inlineEditPost !== 'undefined' ) {
        //Prepopulating our quick-edit post info
        var $inline_editor = inlineEditPost.edit;
        inlineEditPost.edit = function ( id ) {

            var $row, $icon, $icon_container, $icon_value = '';

            //call old copy
            $inline_editor.apply( this, arguments );

            //our custom functionality below
            var post_id = 0;
            if ( typeof ( id ) == 'object' ) {
                post_id = parseInt( this.getId( id ) );
            }

            //if we have our post
            if ( post_id != 0 ) {

                //find our row
                $row = $( '#edit-' + post_id );

                //post subtitle
                $icon_container = $( '#cmtt_meta_icon_' + post_id );
                $icon = $icon_container.find( '.dashicons' );
                if ( $icon.length ) {
                    $icon_value = $icon.data( 'icon' );
                    $row.find( '#cmtt_term_icon' ).val( $icon_value );
                }

            }

        }

    }

//    Dashicons Picker
    /**
     *
     * @returns {void}
     */
    $.fn.dashiconsPicker = function () {

        /**
         * Dashicons, in CSS order
         *
         * @type Array
         */
        var icons = [
            'menu',
            'admin-site',
            'dashboard',
            'admin-media',
            'admin-page',
            'admin-comments',
            'admin-appearance',
            'admin-plugins',
            'admin-users',
            'admin-tools',
            'admin-settings',
            'admin-network',
            'admin-generic',
            'admin-home',
            'admin-collapse',
            'filter',
            'admin-customizer',
            'admin-multisite',
            'admin-links',
            'format-links',
            'admin-post',
            'format-standard',
            'format-image',
            'format-gallery',
            'format-audio',
            'format-video',
            'format-chat',
            'format-status',
            'format-aside',
            'format-quote',
            'welcome-write-blog',
            'welcome-edit-page',
            'welcome-add-page',
            'welcome-view-site',
            'welcome-widgets-menus',
            'welcome-comments',
            'welcome-learn-more',
            'image-crop',
            'image-rotate',
            'image-rotate-left',
            'image-rotate-right',
            'image-flip-vertical',
            'image-flip-horizontal',
            'image-filter',
            'undo',
            'redo',
            'editor-bold',
            'editor-italic',
            'editor-ul',
            'editor-ol',
            'editor-quote',
            'editor-alignleft',
            'editor-aligncenter',
            'editor-alignright',
            'editor-insertmore',
            'editor-spellcheck',
            'editor-distractionfree',
            'editor-expand',
            'editor-contract',
            'editor-kitchensink',
            'editor-underline',
            'editor-justify',
            'editor-textcolor',
            'editor-paste-word',
            'editor-paste-text',
            'editor-removeformatting',
            'editor-video',
            'editor-customchar',
            'editor-outdent',
            'editor-indent',
            'editor-help',
            'editor-strikethrough',
            'editor-unlink',
            'editor-rtl',
            'editor-break',
            'editor-code',
            'editor-paragraph',
            'editor-table',
            'align-left',
            'align-right',
            'align-center',
            'align-none',
            'lock',
            'unlock',
            'calendar',
            'calendar-alt',
            'visibility',
            'hidden',
            'post-status',
            'edit',
            'post-trash',
            'trash',
            'sticky',
            'external',
            'arrow-up',
            'arrow-down',
            'arrow-left',
            'arrow-right',
            'arrow-up-alt',
            'arrow-down-alt',
            'arrow-left-alt',
            'arrow-right-alt',
            'arrow-up-alt2',
            'arrow-down-alt2',
            'arrow-left-alt2',
            'arrow-right-alt2',
            'leftright',
            'sort',
            'randomize',
            'list-view',
            'excerpt-view',
            'grid-view',
            'hammer',
            'art',
            'migrate',
            'performance',
            'universal-access',
            'universal-access-alt',
            'tickets',
            'nametag',
            'clipboard',
            'heart',
            'megaphone',
            'schedule',
            'wordpress',
            'wordpress-alt',
            'pressthis',
            'update',
            'screenoptions',
            'cart',
            'feedback',
            'cloud',
            'translation',
            'tag',
            'category',
            'archive',
            'tagcloud',
            'text',
            'media-archive',
            'media-audio',
            'media-code',
            'media-default',
            'media-document',
            'media-interactive',
            'media-spreadsheet',
            'media-text',
            'media-video',
            'playlist-audio',
            'playlist-video',
            'controls-play',
            'controls-pause',
            'controls-forward',
            'controls-skipforward',
            'controls-back',
            'controls-skipback',
            'controls-repeat',
            'controls-volumeon',
            'controls-volumeoff',
            'yes',
            'no',
            'no-alt',
            'plus',
            'plus-alt',
            'plus-alt2',
            'minus',
            'dismiss',
            'marker',
            'star-filled',
            'star-half',
            'star-empty',
            'flag',
            'info',
            'warning',
            'share',
            'share1',
            'share-alt',
            'share-alt2',
            'twitter',
            'rss',
            'email',
            'email-alt',
            'facebook',
            'facebook-alt',
            'networking',
            'googleplus',
            'location',
            'location-alt',
            'camera',
            'images-alt',
            'images-alt2',
            'video-alt',
            'video-alt2',
            'video-alt3',
            'vault',
            'shield',
            'shield-alt',
            'sos',
            'search',
            'slides',
            'analytics',
            'chart-pie',
            'chart-bar',
            'chart-line',
            'chart-area',
            'groups',
            'businessman',
            'id',
            'id-alt',
            'products',
            'awards',
            'forms',
            'testimonial',
            'portfolio',
            'book',
            'book-alt',
            'download',
            'upload',
            'backup',
            'clock',
            'lightbulb',
            'microphone',
            'desktop',
            'tablet',
            'smartphone',
            'phone',
            'smiley',
            'index-card',
            'carrot',
            'building',
            'store',
            'album',
            'palmtree',
            'tickets-alt',
            'money',
            'thumbs-up',
            'thumbs-down',
            'layout',
            '',
            '',
            ''
        ];

        return this.each( function () {

            var button = $( this ),
                offsetTop,
                offsetLeft;

            button.on( 'click.dashiconsPicker', function ( e ) {
                offsetTop = $( e.currentTarget ).offset().top;
                offsetLeft = $( e.currentTarget ).offset().left;
                createPopup( button );
            } );

            function createPopup( button ) {

                var target = $( button.data( 'target' ) ),
                    preview = $( button.data( 'preview' ) ),
                    popup = $( '<div class="dashicon-picker-container"> \
						<div class="dashicon-picker-control" /> \
						<ul class="dashicon-picker-list" /> \
					</div>' )
                    .css( {
                        'top': offsetTop,
                        'left': offsetLeft
                    } ),
                    list = popup.find( '.dashicon-picker-list' );

                for ( var i in icons ) {
                    list.append( '<li data-icon="' + icons[i] + '"><a href="#" title="' + icons[i] + '"><span class="dashicons dashicons-' + icons[i] + '"></span></a></li>' );
                }

                $( 'a', list ).click( function ( e ) {
                    e.preventDefault();
                    var title = $( this ).attr( 'title' );
                    target.val( 'dashicons-' + title );
                    preview
                        .prop( 'class', 'dashicons' )
                        .addClass( 'dashicons-' + title );
                    removePopup();
                } );

                var control = popup.find( '.dashicon-picker-control' );

                control.html( '<a data-direction="back" href="#"> \
					<span class="dashicons dashicons-arrow-left-alt2"></span></a> \
					<input type="text" class="" placeholder="Search" /> \
					<a data-direction="forward" href="#"><span class="dashicons dashicons-arrow-right-alt2"></span></a>'
                    );

                $( 'a', control ).click( function ( e ) {
                    e.preventDefault();
                    if ( $( this ).data( 'direction' ) === 'back' ) {
                        $( 'li:gt(' + ( icons.length - 26 ) + ')', list ).prependTo( list );
                    } else {
                        $( 'li:lt(25)', list ).appendTo( list );
                    }
                } );

                popup.appendTo( 'body' ).show();

                $( 'input', control ).on( 'keyup', function ( e ) {
                    var search = $( this ).val();
                    if ( search === '' ) {
                        $( 'li:lt(25)', list ).show();
                    } else {
                        $( 'li', list ).each( function () {
                            if ( $( this ).data( 'icon' ).toLowerCase().indexOf( search.toLowerCase() ) !== -1 ) {
                                $( this ).show();
                            } else {
                                $( this ).hide();
                            }
                        } );
                    }
                } );

                $( document ).bind( 'mouseup.dashicons-picker', function ( e ) {
                    if ( !popup.is( e.target ) && popup.has( e.target ).length === 0 ) {
                        removePopup();
                    }
                } );
            }

            function removePopup() {
                $( '.dashicon-picker-container' ).remove();
                $( document ).unbind( '.dashicons-picker' );
            }
        } );
    };

    $( function () {
        $( '.dashicons-picker' ).dashiconsPicker();
    } );

} )( jQuery );