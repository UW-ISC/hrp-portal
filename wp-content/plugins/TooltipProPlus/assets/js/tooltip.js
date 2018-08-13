/*jslint browser: true*/
/*global cmtt_data, console, document, jQuery*/

var CM_Tooltip = { };

/*
 *
 * @use jQuery
 * @use document
 * @param {type} opts
 * @returns {CM_Tooltip.gtooltip.methods}
 */
CM_Tooltip.gtooltip = function ( opts ) {
    "use strict";
    var tooltipWrapper, tooltipWrapperInit, tooltipTop, tooltipContainer, tooltipBottom, tooltipButtonClose, h, w, id, alpha, ie, tooltipApi, closeButtonClicked, tooltipWrapperClicked;

    tooltipWrapper = null;
    tooltipWrapperInit = null;
    id = 'tt';
    alpha = 0;
    ie = document.all ? true : false;

    tooltipApi = {
        create: function ( switchElement ) {
            closeButtonClicked = false;
            tooltipWrapperClicked = false;
            if ( tooltipWrapperInit === null ) {
                tooltipWrapper = document.getElementById( 'tt' );
                if ( tooltipWrapper === null ) {
                    tooltipWrapper = document.createElement( 'div' );
                }
                jQuery( tooltipWrapper ).html( '' ); //reset the content if it's there

                tooltipWrapper.setAttribute( 'id', id );
                tooltipWrapper.setAttribute( 'role', 'tooltip' );
                tooltipWrapper.setAttribute( 'aria-hidden', true );

                tooltipTop = document.createElement( 'div' );
                tooltipTop.setAttribute( 'id', id + 'top' );

                tooltipContainer = document.createElement( 'div' );
                tooltipContainer.setAttribute( 'id', id + 'cont' );
                tooltipContainer.style.padding = opts.padding;
                tooltipContainer.style.backgroundColor = opts.background;
                tooltipContainer.style.color = opts.foreground;
                tooltipContainer.style.borderWidth = opts.borderWidth;
                tooltipContainer.style.borderStyle = opts.borderStyle;
                tooltipContainer.style.borderColor = opts.borderColor;
                tooltipContainer.style.borderRadius = opts.borderRadius;
                tooltipContainer.style.fontSize = opts.fontSize;

                tooltipBottom = document.createElement( 'div' );
                tooltipBottom.setAttribute( 'id', id + 'bot' );

                if ( opts.close_button !== false ) {
                    tooltipButtonClose = document.createElement( 'span' );
                    tooltipButtonClose.setAttribute( 'id', id + '-btn-close' );
                    tooltipButtonClose.setAttribute( 'class', 'dashicons ' + opts.close_symbol );
                    tooltipButtonClose.setAttribute( 'aria-label', 'Close the tooltip' );
                    tooltipTop.appendChild( tooltipButtonClose );
                }
                tooltipWrapper.appendChild( tooltipTop );
                tooltipWrapper.appendChild( tooltipContainer );
                tooltipWrapper.appendChild( tooltipBottom );

                document.body.appendChild( tooltipWrapper );

                tooltipWrapper.style.opacity = 0;
                tooltipWrapper.style.filter = 'alpha(opacity=0)';

                /*
                 * If the tooltip is not clickable we shouldn't be able to hover it
                 */
                if ( opts.clickable !== false ) {
                    jQuery( tooltipWrapper ).on( 'mouseover', function () {
                        clearTimeout( CM_Tooltip.timeoutId );
                        if ( closeButtonClicked ) {
                            clearTimeout( CM_Tooltip.delayId );
                            tooltipWrapper.style.display = 'none';
                        }
                        if ( jQuery( this ).is( ':animated' ) && !closeButtonClicked ) {
                            //animation effects
                            jQuery( tooltipWrapper ).removeClass( 'force-hide' );
                            jQuery( tooltipWrapper ).removeClass( 'flipOutY' );
                            jQuery( tooltipWrapper ).removeClass( 'zoomOut' );
                            jQuery( tooltipWrapper ).removeClass( 'fadeOut' );
                            tooltipWrapper.style.opacity = opts.endalpha / 100;
                            if ( opts.tooltipDisplayanimation == 'no_animation' ) {
                                tooltipWrapper.style.display = 'block';
                            } else if ( opts.tooltipDisplayanimation == 'fade_in' ) {
                                jQuery( tooltipWrapper ).addClass( 'fadeIn' );
                            } else if ( opts.tooltipDisplayanimation == 'grow' ) {
                                jQuery( tooltipWrapper ).addClass( 'zoomIn' );
                            } else if ( opts.tooltipDisplayanimation == 'horizontal_flip' ) {
                                jQuery( tooltipWrapper ).addClass( 'flipInY' );
                            } else if ( opts.tooltipDisplayanimation == 'center_flip' ) {
                            } else {
                                jQuery( this ).stop().fadeTo( tooltipWrapper.timer, ( opts.endalpha / 100 ) ).show();
                            }
                        }
                    } );
                }

                if ( opts.clickable !== false && opts.close_on_moveout ) {
                    jQuery( tooltipWrapper ).on( 'mouseleave', function () {
                        clearTimeout( CM_Tooltip.delayId );
                        if ( !jQuery( this ).is( ':animated' ) ) {
                            tooltipApi.hide();
                        }
                    } );
                }

                jQuery( tooltipWrapper ).on( 'click touchstart', function ( e ) {
                    if ( jQuery( e.target ).parents( '.cmtt-audio-player' ).length < 1 )
                    {
                        closeButtonClicked = jQuery( e.target ).attr( 'id' ) === 'tt-btn-close';

                        if ( !opts.only_on_button && !opts.touch_anywhere || opts.only_on_button && closeButtonClicked ) {
                            tooltipApi.hide();
                        }
                    }
                } );

                jQuery( 'body' ).on( 'touchstart', function ( e ) {
                    tooltipWrapperClicked = jQuery( e.target ).parents( '#tt' ).length;
                    if ( opts.touch_anywhere && !tooltipWrapperClicked ) {
                        tooltipApi.hide();
                    }
                } );

                if ( opts.clickable !== false ) {
                    jQuery( document ).keyup( function ( e ) {
                        if ( e.keyCode === 27 ) { // escape key maps to keycode `27`
                            tooltipApi.hide();
                        }
                    } );
                }
                tooltipWrapperInit = 1;
            }
        },
        show: function ( content, switchElement ) {
            /*
             * Create the tooltip
             */
            this.create( switchElement );

            clearTimeout( CM_Tooltip.timeoutId );
            document.onmousemove = this.pos;

            CM_Tooltip.delayId = setTimeout( function () {

                if ( switchElement && jQuery( switchElement ).hasClass( 'transparent' ) )
                {
                    tooltipContainer.style.backgroundColor = 'transparent';
                } else
                {
                    tooltipContainer.style.backgroundColor = opts.background;
                }

                tooltipContainer.innerHTML = content;

                CM_Tooltip.parseAudioPlayer();

                tooltipWrapper.style.display = 'block';
                tooltipWrapper.style.width = 'auto';
                tooltipWrapper.style.maxWidth = opts.maxw + 'px';
                tooltipWrapper.setAttribute( 'aria-hidden', false );

                if ( !switchElement && ie ) {
                    tooltipTop.style.display = 'block';
                    tooltipBottom.style.display = 'block';
                }

                h = parseInt( tooltipWrapper.offsetHeight, 10 ) + opts.top;
                //center flip issue
                if ( opts.tooltipDisplayanimation == 'center_flip' || opts.tooltipHideanimation == 'center_flip' ) {
                    if ( jQuery( '#tt' ).hasClass( 'no-in' ) ) {
                        jQuery( '#tt' ).css( { 'display': 'block' } );
                        jQuery( '#tt' ).removeClass( 'out' );
                    } else {
                        jQuery( '#tt' ).addClass( 'in' );
                        jQuery( '#tt' ).removeClass( 'out' );
                    }
                }
                jQuery( tooltipWrapper ).removeClass( 'force-hide' );
                jQuery( tooltipWrapper ).removeClass( 'flipOutY' );
                jQuery( tooltipWrapper ).removeClass( 'zoomOut' );
                jQuery( tooltipWrapper ).removeClass( 'fadeOut' );
                tooltipWrapper.style.opacity = opts.endalpha / 100;
                if ( opts.tooltipDisplayanimation == 'no_animation' ) {
                    tooltipWrapper.style.display = 'block';
                } else if ( opts.tooltipDisplayanimation == 'fade_in' ) {
                    jQuery( tooltipWrapper ).addClass( 'fadeIn' );
                } else if ( opts.tooltipDisplayanimation == 'grow' ) {
                    jQuery( tooltipWrapper ).addClass( 'zoomIn' );
                } else if ( opts.tooltipDisplayanimation == 'horizontal_flip' ) {
                    jQuery( tooltipWrapper ).addClass( 'flipInY' );
                } else if ( opts.tooltipDisplayanimation == 'center_flip' ) {
                } else {
                jQuery( tooltipWrapper ).stop().fadeTo( tooltipWrapper.timer, ( opts.endalpha / 100 ) );
                }
            }, opts.delay );
        },
        pos: function ( e ) {
            /*
             * Common part
             */
            var u, l, topShift, leftShift, screenWidth, screenHeight, horizontalOffscreen, verticalOffscreenBot, verticalOffscreenTop, fullWidth = false;
            h = parseInt( tooltipWrapper.offsetHeight, 10 ) + opts.top;
            w = parseInt( tooltipWrapper.offsetWidth, 10 ) + opts.top;

            if ( typeof e.pageX === 'undefined' && e.type === 'touchstart' ) {
                u = e.originalEvent.touches[0].pageY;
                l = e.originalEvent.touches[0].pageX;
            } else if ( e.type === 'focusin' ) {
                var offset = jQuery( e.currentTarget ).offset();
                u = offset.top + opts.top;
                l = offset.left;
            } else
            {
                u = ie ? event.clientY + document.documentElement.scrollTop : e.pageY;
                l = ie ? event.clientX + document.documentElement.scrollLeft : e.pageX;
            }

            if ( 'vertical' === opts.placement ) {

                jQuery( tooltipWrapper ).removeClass( 'vertical_bottom' );
                jQuery( tooltipWrapper ).removeClass( 'vertical_top' );

                topShift = ( u + opts.top + 10 );
//                leftShift = ( l - w ) > 28 ? ( l - w / 2 ) : 28;
                leftShift = ( l - w ) > 28 ? ( l - w / 2 ) : 28;

                screenWidth = jQuery( window ).width();

                tooltipWrapper.style.right = 'none';
                tooltipWrapper.style.left = 'none';

                /*
                 * Check the vertical offscreen
                 */
                screenHeight = jQuery( window ).height();

                var docViewTop = jQuery( window ).scrollTop();
                var docViewBottom = docViewTop + screenHeight;

                var elemTop = topShift;
                var elemBottom = elemTop + h;

                if ( jQuery( '#wpadminbar' ).length )
                {
                    docViewTop += jQuery( '#wpadminbar' ).height();
                }

                verticalOffscreenBot = elemBottom > docViewBottom;

                if ( verticalOffscreenBot )
                {
                    topShift = ( u - opts.top - h );
                    verticalOffscreenTop = topShift < docViewTop;
                    if ( verticalOffscreenTop )
                    {
                        topShift = elemTop;
                        jQuery( tooltipWrapper ).addClass( 'vertical_top' );
                    } else {
                        jQuery( tooltipWrapper ).addClass( 'vertical_bottom' );
                    }
                } else {
                    jQuery( tooltipWrapper ).addClass( 'vertical_top' );
                }

                tooltipWrapper.style.top = topShift + 'px';

                /*
                 * Check the horizontal offscreen
                 */

                horizontalOffscreen = ( screenWidth - leftShift ) < opts.minw;

                if ( horizontalOffscreen )
                {
                    tooltipWrapper.style.width = 'auto';
                    tooltipWrapper.style.left = null;
                    tooltipWrapper.style.right = 0 + 'px';
                    /*
                     * Recalculate the height
                     */
                    h = parseInt( tooltipWrapper.offsetHeight, 10 ) + opts.top;
                    fullWidth = true;
                    topShift -= h / 2 + 10;
                } else
                {
                    tooltipWrapper.style.width = 'auto';
                    tooltipWrapper.style.left = leftShift + 'px';
                    tooltipWrapper.style.right = null;
                }
            } else {
                topShift = ( u - h ) > 28 ? ( u - h / 2 ) : 28;
                leftShift = ( l + opts.left - 5 );

                screenWidth = jQuery( window ).width();

                tooltipWrapper.style.right = 'none';
                tooltipWrapper.style.left = 'none';

                /*
                 * Check the vertical offscreen
                 */

                horizontalOffscreen = ( screenWidth - leftShift ) < opts.minw;

                if ( horizontalOffscreen )
                {
                    tooltipWrapper.style.width = 'auto';
                    tooltipWrapper.style.left = null;
                    tooltipWrapper.style.right = 0 + 'px';
                    /*
                     * Recalculate the height
                     */
                    h = parseInt( tooltipWrapper.offsetHeight, 10 ) + opts.top;
                    fullWidth = true;
                    topShift -= h / 2 + 10;
                } else
                {
                    tooltipWrapper.style.width = 'auto';
                    tooltipWrapper.style.left = leftShift + 'px';
                    tooltipWrapper.style.right = null;
                }

                /*
                 * Check the vertical offscreen
                 */
                screenHeight = jQuery( window ).height();

                var docViewTop = jQuery( window ).scrollTop();
                var docViewBottom = docViewTop + screenHeight;

                var elemTop = topShift;
                var elemBottom = elemTop + h;

                if ( jQuery( '#wpadminbar' ).length )
                {
                    docViewTop += jQuery( '#wpadminbar' ).height();
                }

                verticalOffscreenBot = elemBottom > docViewBottom;
                verticalOffscreenTop = elemTop < docViewTop;

                if ( verticalOffscreenBot )
                {
                    topShift -= ( ( elemBottom - docViewBottom ) + 1 );
                }
                if ( verticalOffscreenTop )
                {
                    if ( fullWidth )
                    {
                        topShift += h + 20;
                    } else
                    {
                        topShift += ( ( docViewTop - elemTop ) + 1 );
                    }
                }

                tooltipWrapper.style.top = topShift + 'px';
            }

            /*
             * If the tooltip has to be clickable we have to turnoff it's repositioning 'feature'
             */
            if ( opts.clickable ) {
                document.onmousemove = null;
            }
            /*
             * Touch devices should not fire this
             */
            if ( CM_Tools.Modernizr.touch )
            {
                document.onmousemove = null;
            }
        },
        fade: function ( d ) {
            var i, a = alpha;
            tooltipWrapper.setAttribute( 'aria-hidden', true );
            if ( ( a !== opts.endalpha && d === 1 ) || ( a !== 0 && d === -1 ) ) {
                i = opts.speed;
                if ( opts.endalpha - a < opts.speed && d === 1 ) {
                    i = opts.endalpha - a;
                } else if ( alpha < opts.speed && d === -1 ) {
                    i = a;
                }
                alpha = a + ( i * d );
                tooltipWrapper.style.opacity = alpha * 0.01;
                tooltipWrapper.style.filter = 'alpha(opacity=' + alpha + ')';
            } else {
                clearInterval( tooltipWrapper.timer );
                if ( d === -1 ) {
                    tooltipWrapper.style.display = 'none';
                }
            }
        },
        hide: function () {
            tooltipWrapper.setAttribute( 'aria-hidden', true );
            //animation effects
            if ( opts.tooltipDisplayanimation == 'center_flip' || opts.tooltipHideanimation == 'center_flip' ) {
                if ( jQuery( '#tt' ).hasClass( 'no-out' ) ) {
                    jQuery( '#tt' ).addClass( 'temp' );
                    jQuery( '#tt' ).removeClass( 'in' );
                } else {
                    jQuery( '#tt' ).addClass( 'out' );
                    jQuery( '#tt' ).removeClass( 'in' );
                }
            }
            jQuery( tooltipWrapper ).removeClass( 'flipInY' );
            jQuery( tooltipWrapper ).removeClass( 'zoomIn' );
            jQuery( tooltipWrapper ).removeClass( 'fadeIn' );
            jQuery( tooltipWrapper ).removeClass( 'tooltip-bloated-content' );
            if ( opts.tooltipHideanimation == 'no_animation' ) {
                if ( opts.tooltipHideanimation == 'no_animation' ) {
                    jQuery( tooltipWrapper ).addClass( 'force-hide' );
                }
                tooltipWrapper.style.display = 'none !important';
            } else if ( opts.tooltipHideanimation == 'fade_out' ) {
                jQuery( tooltipWrapper ).addClass( 'fadeOut' );
            } else if ( opts.tooltipHideanimation == 'shrink' ) {
                jQuery( tooltipWrapper ).addClass( 'zoomOut' );
            } else if ( opts.tooltipHideanimation == 'horizontal_flip' ) {
                jQuery( tooltipWrapper ).addClass( 'flipOutY' );
            } else if ( opts.tooltipHideanimation == 'center_flip' ) {
            } else {
                jQuery( tooltipWrapper ).stop().fadeOut( tooltipWrapper.timer ).fadeTo( 0, 0 );
            }
        }
    };
    return tooltipApi;
};

CM_Tooltip.glossaryTip = null;

/*
 * Inside this closure we use $ instead of jQuery in case jQuery is reinitialized again before document.ready()
 */
( function ( $ ) {
    "use strict";
    $.fn.glossaryTooltip = function ( options ) {
        var opts = {
            top: 3,
            left: 23,
            maxw: 400,
            minw: 200,
            speed: 10,
            timer: 500,
            delay: 500,
            endalpha: 95,
            borderStyle: 'none',
            borderWidth: '0px',
            borderColor: '#000',
            borderRadius: '6px',
            padding: '2px 12px 3px 7px',
            clickable: true,
            close_button: false,
            only_on_button: false,
            close_on_moveout: true,
            touch_anywhere: false,
            placement: 'horizontal'
        };
        opts = $.extend( { }, opts, options );
        CM_Tooltip.glossaryTip = CM_Tooltip.gtooltip( opts );

        if ( this.length )
        {
            if ( this[0].tagName === 'A' && CM_Tools.Modernizr.touch )
            {
                $( document ).on( 'click', this.selector, function ( e ) {
                    e.preventDefault();
                } );
            }

            opts.mobile = 0;

            return this.each( function () {
                var tooltipContent, tooltipContentMaybeHash, $inputCopy;

                tooltipContentMaybeHash = $( this ).data( 'cmtooltip' );
                if ( typeof window.cmtt_data.cmtooltip_definitions !== 'undefined' && typeof window.cmtt_data.cmtooltip_definitions[tooltipContentMaybeHash] !== 'undefined' ) {
                    tooltipContent = jQuery( '<div>' + window.cmtt_data.cmtooltip_definitions[tooltipContentMaybeHash] + '</div>' ).html();
                } else {
                    tooltipContent = tooltipContentMaybeHash;
                }

                if ( this.tagName === 'A' && CM_Tools.Modernizr.touch )
                {
                    opts.mobile = 1;
                    /*
                     * Add link at the bottom of the tooltip
                     */
                    if ( window.cmtt_data !== undefined && window.cmtt_data.mobile_support === "1" ) {
                        $inputCopy = $( this ).clone();
                        $inputCopy.removeAttr( 'data-cmtooltip' );
                        tooltipContent += $( '<div class="mobile-link"/>' ).append( $inputCopy )[0].outerHTML;
                    }

                    /*
                     * Proper support for touch devices
                     */
                    $( this ).on( 'touchstart', function ( e ) {
                        CM_Tooltip.glossaryTip.show( tooltipContent, this );

                        setTimeout( function () {
                            CM_Tooltip.glossaryTip.pos( e );
                        }, opts.delay + 100 );

                        e.preventDefault();
                        return false;
                    } );

                    $( this ).on( 'touchmove', function ( e ) {
                        e.preventDefault();
                        return false;
                    } );

                    $( this ).on( 'touchend', function ( e ) {
                        e.preventDefault();
                        return false;
                    } );

                    /*
                     * Support for touch+mouse devices
                     */
                    $( this ).on( 'click', function ( e ) {
                        CM_Tooltip.glossaryTip.show( tooltipContent, this );
                        setTimeout( function () {
                            CM_Tooltip.glossaryTip.pos( e );
                        }, opts.delay + 100 );
                        return false;
                    } );
                } else
                {
                    /*
                     * Display tooltips on click and not on hover
                     */
                    if ( window.cmtt_data !== undefined && window.cmtt_data.tooltip_on_click === "1" ) {
                        $( this ).on( 'click', function ( e ) {
                            CM_Tooltip.glossaryTip.show( tooltipContent, this );
                            CM_Tooltip.glossaryTip.pos( e );
                            return false;
                        } );
                    } else
                    {
                        /*
                         * Display tooltips on hover
                         */
                        $( this ).on( 'mouseenter focusin', function ( e ) {
                            clearTimeout( CM_Tooltip.timeoutId );
                            CM_Tooltip.glossaryTip.show( tooltipContent, this );
                            CM_Tooltip.glossaryTip.pos( e );
                        } ).on( 'mouseleave focusout', function () {
                            if ( opts.close_on_moveout ) {
                                clearTimeout( CM_Tooltip.delayId );
                                CM_Tooltip.timeoutId = setTimeout( function () {
                                    CM_Tooltip.glossaryTip.hide();
                                }, opts.timer );
                            }
                        } );
                    }
                }
            } );
        }
    };

    $( document ).ready( function () {

        setTimeout( function () {
            $( document ).trigger( 'glossaryTooltipReady' );
        }, 5 );

        $( document ).on( "glossaryTooltipReady", function () {
            if ( window.cmtt_data !== undefined && window.cmtt_data.cmtooltip ) {

                /*
                 * Mobile detected and tooltips hidden
                 */
                if ( CM_Tools.Modernizr.touch && window.cmtt_data.mobile_disable_tooltips === "1" )
                {
                    return;
                }

                if ( window.cmtt_data !== undefined && window.cmtt_data.tooltip_on_click === "1" ) {
                    /*
                     * If tooltips are opened onclick they have to be clickable
                     */
                    window.cmtt_data.cmtooltip.clickable = true;
                }

                $( "[data-cmtooltip]" ).glossaryTooltip( window.cmtt_data.cmtooltip );
            }
        } );

        /*
         * CM Glossary Theme toggle
         */
        $( document ).on( 'click', 'a.cmtt-glossary-theme-toggle', function () {
            var $this, $body, bodyClass;

            $this = $( this );
            $body = $( document ).find( 'body' );
            bodyClass = $this.data( 'bodyclass' );

            if ( bodyClass.length )
            {
                $body.toggleClass( bodyClass );
            }
        } );

        /*
         * Update tooltips on Datatables redraw
         */
        if ( typeof jQuery.fn.dataTable !== 'undefined' )
        {
            jQuery( document ).on( "draw.dt", function () {
                jQuery( document ).trigger( 'glossaryTooltipReady' );
            } );
        }

        /*
         * CM Tooltip Custom Code
         */
        $( '.cmtt-post-tags' ).on( 'click', 'a', function () {
            var $this, $url, $parent, $tempform;
            $this = $( this );
            $parent = $this.closest( '.cmtt-post-tags' );
            $url = $parent.data( 'glossary-url' );
            $tempform = $( '<form/>', {
                'action': $url,
                'method': 'post'
            } );
            $tempform.append( $( '<input type="hidden" name="gtags[]" value="' + $this.data( 'tagid' ) + '" />' ) );
            $parent.append( $tempform );
            $tempform.submit();
        } );

        /*
         * CM Tooltip Custom Code
         */
        $( '.cmtt-taxonomy-single' ).on( 'click', 'a', function () {
            var $this, $url, $parent, $tempform;
            $this = $( this );
            $parent = $this.closest( '.cmtt-taxonomy-single' );
            $url = $parent.data( 'glossary-url' );
            $tempform = $( '<form/>', {
                'action': $url,
                'method': 'post'
            } );
            $tempform.append( $( '<input type="hidden" name="' + $this.data( 'taxonomy-name' ) + '" value="' + $this.data( 'tagid' ) + '" />' ) );
            $parent.append( $tempform );
            $tempform.submit();
        } );

        if ( window.cmtt_data !== undefined && window.cmtt_data.doubleclick_api === "1" ) {
            $( document ).on( 'dblclick', function ( e ) {
                window.cmtt_data.dblclick_timeout = setTimeout( function () {
                    var $this = $( e.target );
                    var selection = ( document.selection && document.selection.createRange().text ) || ( window.getSelection && window.getSelection().toString() );

                    var data = {
                        action: 'cmtt_api_call',
                        selection: selection
                    };

                    $.post( window.cmtt_data.ajaxurl, data, function ( response ) {
                        /*
                         * Show response if there is any
                         */
                        if ( response && response.length > 2 )
                        {
                            CM_Tooltip.glossaryTip.show( response );
                        } else
                        {
                            CM_Tooltip.glossaryTip.hide();
                        }
                    } );

                    CM_Tooltip.glossaryTip.show( 'Loading...' );
                }, 300 );
            } );
        }

        CM_Tooltip.parseAudioPlayer = function () {
            var settings = {
                // if the <video width> is not specified, this is the default
                defaultVideoWidth: 480,
                // if the <video height> is not specified, this is the default
                defaultVideoHeight: 270,
                // if set, overrides <video width>
                videoWidth: -1,
                // if set, overrides <video height>
                videoHeight: -1,
                // width of audio player
                audioWidth: '95.5%',
                // height of audio player
                audioHeight: 30,
                // initial volume when the player starts
                startVolume: 0.8,
                // useful for <audio> player loops
                loop: false,
                // enables Flash and Silverlight to resize to content size
                enableAutosize: true,
                // the order of controls you want on the control bar (and other plugins below)
                features: [ 'playpause', 'progress', 'current', 'duration', 'tracks', 'volume', 'fullscreen' ],
                // Hide controls when playing and mouse is not over the video
                alwaysShowControls: false,
                // force iPad's native controls
                iPadUseNativeControls: false,
                // force iPhone's native controls
                iPhoneUseNativeControls: false,
                // force Android's native controls
                AndroidUseNativeControls: false,
                // forces the hour marker (##:00:00)
                alwaysShowHours: false,
                // show framecount in timecode (##:00:00:00)
                showTimecodeFrameCount: false,
                // used when showTimecodeFrameCount is set to true
                framesPerSecond: 25,
                // turns keyboard support on and off for this instance
                enableKeyboard: true,
                // when this player starts, it will pause other players
                pauseOtherPlayers: true,
                // array of keyboard commands
                keyActions: [ ]
            };

            if ( typeof _wpmejsSettings !== 'undefined' )
                settings.pluginPath = _wpmejsSettings.pluginPath;

            if ( $( '.cmtt-audio-shortcode' ).length )
            {
                if ( typeof $.fn.mediaelementplayer === 'undefined' )
                {
                    console.log( 'The jQuery doesn\'t have the function required for displaying the AudioPlayer. \n\ This is probably because the jQuery has been reinitialized after the "mediaplayerelement" has already been added.' );
                } else
                {
                    $( '.cmtt-audio-shortcode' ).mediaelementplayer( settings );
                }
            }

        };

        CM_Tooltip.parseAudioPlayer();

        /*
         * Sharing Box
         */
        CM_Tooltip.shareBox = function () {

            /*
             * We will assume that if we don't have the box we don't need this scripts
             */
            if ( $( ".cmtt-social-box" ).length === 0 ) {
                return;
            }

            /*
             * We will assume that if we have one type of button we have them all
             */
            if ( $( ".twitter-share-button" ).length === 0 ) {
                return;
            }

            if ( typeof ( twttr ) !== 'undefined' && typeof ( twttr.widgets ) !== 'undefined' ) {
                twttr.widgets.load();
            } else {
                $.getScript( '//platform.twitter.com/widgets.js' );
            }

            //Linked-in
            if ( typeof ( IN ) !== 'undefined' ) {
                IN.parse();
            } else {
                $.getScript( "//platform.linkedin.com/in.js" );
            }

            ( function () {
                var po = document.createElement( 'script' );
                po.type = 'text/javascript';
                po.async = true;
                po.src = '//apis.google.com/js/plusone.js';
                var s = document.getElementsByTagName( 'script' )[0];
                s.parentNode.insertBefore( po, s );
            } )();

            ( function ( d, s, id ) {
                if ( typeof window.fbAsyncInit === 'undefined' )
                {
                    window.fbAsyncInit = function () {
                        // Don't init the FB as it needs API_ID just parse the likebox
                        FB.XFBML.parse();
                    };

                    var js, fjs = d.getElementsByTagName( s )[0];
                    if ( d.getElementById( id ) )
                        return;
                    js = d.createElement( s );
                    js.id = id;
                    js.src = "//connect.facebook.net/en_US/all.js";
                    fjs.parentNode.insertBefore( js, fjs );
                }
            }( document, 'script', 'facebook-jssdk' ) );
        };
        CM_Tooltip.shareBox();

        $( '.cmtt-embed-btn' ).click( function ( ev ) {
            ev.preventDefault();
            var overlay = CM_Tooltip.Utils.overlay( $( document ).find( '.cmtt-embed-modal' ) );
            overlay.find( '.cmtt-embed-modal' ).show();
            overlay.find( '.cmtt-embed-modal textarea' ).click( function () {
                this.select();
            } );
            $( ".cmtt-embed-copy-btn", overlay ).click( function ( e ) {
                e.preventDefault();
                var wrapper = $( this ).parents( '.cmtt-embed-modal' );
                wrapper.find( "textarea" ).select();
                document.execCommand( 'copy' );
            } );
        } );
    } );

    window.CM_Tooltip.Utils = {
        addSingleHandler: function ( handlerName, selector, action, func ) {
            var obj;
            if ( typeof selector == 'string' )
                obj = $( selector );
            else
                obj = selector;
            obj.each( function () {
                var obj = $( this );
                if ( obj.data( handlerName ) != '1' ) {
                    obj.data( handlerName, '1' );
                    obj.on( action, func );
                }
            } );
        },
        leftClick: function ( func ) {
            return function ( e ) {
                // Allow to use middle-button to open thread in a new tab:
                if ( e.which > 1 || e.shiftKey || e.altKey || e.metaKey || e.ctrlKey )
                    return;
                func.apply( this, [ e ] );
                return false;
            }
        },
        toast: function ( msg, className, duration ) {
            if ( typeof className != 'string' )
                className = 'info';
            if ( typeof duration == 'undefined' )
                duration = 5;
            var toast = $( '<div/>', { "class": "cmtt-toast " + className, "style": "display:none" } );
            toast.text( msg );
            $( 'body' ).append( toast );
            toast.fadeIn( 500, function () {
                setTimeout( function () {
                    toast.fadeOut( 500 );
                }, duration * 1000 );
            } );
        },
        overlay: function ( content ) {
            var overlay = $( '<div>', { "class": 'cmtt-overlay' } );
            var contentOuter = $( '<div>', { "class": 'cmtt-overlay-content-outer' } );
            var contentWrapper = $( '<div>', { "class": 'cmtt-overlay-content' } );
            var closeButton = $( '<span>', { "class": 'cmtt-overlay-close' } )
            closeButton.html( '&times;' );
            $( 'body' ).append( overlay );
            overlay.append( contentOuter );
            contentOuter.append( contentWrapper );
            if ( typeof content == 'string' )
                contentWrapper.html( content );
            else
                contentWrapper.append( content.clone() );
            contentWrapper.append( closeButton );
            overlay.fadeIn( 'fast' );
            var close = function () {
                overlay.fadeOut( 'fast', function () {
                    overlay.remove();
                } );
            };
            overlay.click( function ( ev ) {
                var target = $( ev.target );
                if ( target.hasClass( 'cmtt-overlay' ) ) {
                    close();
                }
            } );
            closeButton.click( close );
            $( window ).keydown( function ( ev ) {
                if ( ev.keyCode == 27 ) {
                    close();
                }
            } );
            return overlay;
        }

    };

}( jQuery ) );

/**
 * WordPress inline HTML embed
 *
 * @since 4.4.0
 *
 * This file cannot have ampersands in it. This is to ensure
 * it can be embedded in older versions of WordPress.
 * See https://core.trac.wordpress.org/changeset/35708.
 */
( function ( window, document ) {
    'use strict';

    var supportedBrowser = false,
        loaded = false;

    if ( document.querySelector ) {
        if ( window.addEventListener ) {
            supportedBrowser = true;
        }
    }

    /** @namespace wp */
    window.wp = window.wp || { };

    window.wp.receiveEmbedMessage = null;

    if ( !!window.wp.receiveEmbedMessage ) {
        return;
    }

    window.wp.receiveEmbedMessage = function ( e ) {
        var data = e.data;
        if ( !( data.secret || data.message || data.value ) ) {
            return;
        }

        if ( typeof data.secret === 'undefined' ) {
            return;
        }

        if ( /[^a-zA-Z0-9]/.test( data.cmsecret ) ) {
            return;
        }
        if ( /[^a-zA-Z0-9]/.test( data.secret ) ) {
            return;
        }

        var cmsecret = data.secret.replace( "__cm__", "" );
        var iframes = document.querySelectorAll( 'iframe[data-cmsecret="' + cmsecret + '"]' ),
            blockquotes = document.querySelectorAll( 'blockquote[data-cmsecret="' + cmsecret + '"]' ),
            i, source, height, sourceURL, targetURL;

        for ( i = 0; i < blockquotes.length; i++ ) {
            blockquotes[ i ].style.display = 'none';
        }

        for ( i = 0; i < iframes.length; i++ ) {
            source = iframes[ i ];

            if ( e.source !== source.contentWindow ) {
                continue;
            }

//			source.removeAttribute( 'style' );

            /* Resize the iframe on request. */
            if ( 'height' === data.message ) {
                height = parseInt( data.value, 10 );
                if ( height > 1000 ) {
                    height = 1000;
                } else if ( ~~height < 200 ) {
                    height = 200;
                }

                source.height = height;
            }

            /* Link to a specific URL on request. */
            if ( 'link' === data.message ) {
                sourceURL = document.createElement( 'a' );
                targetURL = document.createElement( 'a' );

                sourceURL.href = source.getAttribute( 'src' );
                targetURL.href = data.value;

                /* Only continue if link hostname matches iframe's hostname. */
                if ( targetURL.host === sourceURL.host ) {
                    if ( document.activeElement === source ) {
                        var win = window.open( data.value, '_blank' );
                        win.focus();
//                        window.top.location.href = data.value;
                    }
                }
            }
        }
    };

    function onLoad() {
        if ( loaded ) {
            return;
        }

        loaded = true;

        var isIE10 = -1 !== navigator.appVersion.indexOf( 'MSIE 10' ),
            isIE11 = !!navigator.userAgent.match( /Trident.*rv:11\./ ),
            iframes = document.querySelectorAll( 'iframe.cm-embedded-content' ),
            iframeClone, i, source, secret;

        for ( i = 0; i < iframes.length; i++ ) {
            source = iframes[ i ];

            if ( !source.getAttribute( 'data-cmsecret' ) ) {
                /* Add secret to iframe */
                secret = Math.random().toString( 36 ).substr( 2, 10 );
                source.src += '#?cmsecret=' + secret;
                source.setAttribute( 'data-secret', '__cm__' + secret );
                source.setAttribute( 'data-cmsecret', secret );
            }

            /* Remove security attribute from iframes in IE10 and IE11. */
            if ( ( isIE10 || isIE11 ) ) {
                iframeClone = source.cloneNode( true );
                iframeClone.removeAttribute( 'security' );
                source.parentNode.replaceChild( iframeClone, source );
            }
        }
    }

    if ( supportedBrowser ) {
        window.addEventListener( 'message', window.wp.receiveEmbedMessage, false );
        document.addEventListener( 'DOMContentLoaded', onLoad, false );
        window.addEventListener( 'load', onLoad, false );
    }
} )( window, document );