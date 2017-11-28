<?php
if ( !function_exists( 'cminds_parse_php_info' ) ) {

    function cminds_parse_php_info() {
        $obstartresult = ob_start();
        if ( $obstartresult ) {
            $phpinforesult = phpinfo( INFO_MODULES );
            if ( $phpinforesult == FALSE ) {
                return array();
            }
            $s = ob_get_clean();
        } else {
            return array();
        }

        $s        = strip_tags( $s, '<h2><th><td>' );
        $s        = preg_replace( '/<th[^>]*>([^<]+)<\/th>/', "<info>\\1</info>", $s );
        $s        = preg_replace( '/<td[^>]*>([^<]+)<\/td>/', "<info>\\1</info>", $s );
        $vTmp     = preg_split( '/(<h2>[^<]+<\/h2>)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE );
        $vModules = array();
        for ( $i = 1; $i < count( $vTmp ); $i++ ) {
            if ( preg_match( '/<h2>([^<]+)<\/h2>/', $vTmp[ $i ], $vMat ) ) {
                $vName = trim( $vMat[ 1 ] );
                $vTmp2 = explode( "\n", $vTmp[ $i + 1 ] );
                foreach ( $vTmp2 AS $vOne ) {
                    $vPat  = '<info>([^<]+)<\/info>';
                    $vPat3 = "/$vPat\s*$vPat\s*$vPat/";
                    $vPat2 = "/$vPat\s*$vPat/";
                    if ( preg_match( $vPat3, $vOne, $vMat ) ) { // 3cols
                        $vModules[ $vName ][ trim( $vMat[ 1 ] ) ] = array( trim( $vMat[ 2 ] ), trim( $vMat[ 3 ] ) );
                    } elseif ( preg_match( $vPat2, $vOne, $vMat ) ) { // 2cols
                        $vModules[ $vName ][ trim( $vMat[ 1 ] ) ] = trim( $vMat[ 2 ] );
                    }
                }
            }
        }
        return $vModules;
    }

}

if ( !function_exists( 'cminds_file_exists_remote' ) ) {

    /**
     * Checks whether remote file exists
     * @param type $url
     * @return boolean
     */
    function cminds_file_exists_remote( $url ) {
        if ( !function_exists( 'curl_version' ) ) {
            return false;
        }

        $curl   = curl_init( $url );
        curl_setopt( $curl, CURLOPT_NOBODY, true );
        /*
         * Don't wait more than 5s for a file
         */
        curl_setopt( $curl, CURLOPT_TIMEOUT, 5 );
        //Check connection only
        $result = curl_exec( $curl );
        //Actual request
        $ret    = false;
        if ( $result !== false ) {
            $statusCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
            //Check HTTP status code
            if ( $statusCode == 200 ) {
                $ret = true;
            }
        }
        curl_close( $curl );
        return $ret;
    }

}

if ( !function_exists( 'cminds_sort_WP_posts_by_title_length' ) ) {

    function cminds_sort_WP_posts_by_title_length( $a, $b ) {
        $sortVal = 0;
        if ( property_exists( $a, 'post_title' ) && property_exists( $b, 'post_title' ) ) {
            $sortVal = strlen( $b->post_title ) - strlen( $a->post_title );
        }
        return $sortVal;
    }

}

if ( !function_exists( 'cminds_strip_only' ) ) {

    /**
     * Strips just one tag
     * @param type $str
     * @param type $tags
     * @param type $stripContent
     * @return type
     */
    function cminds_strip_only( $str, $tags, $stripContent = false ) {
        $content = '';
        if ( !is_array( $tags ) ) {
            $tags = (strpos( $str, '>' ) !== false ? explode( '>', str_replace( '<', '', $tags ) ) : array( $tags ));
            if ( end( $tags ) == '' ) {
                array_pop( $tags );
            }
        }
        foreach ( $tags as $tag ) {
            if ( $stripContent ) {
                $content = '(.+</' . $tag . '[^>]*>|)';
            }
            $str = preg_replace( '#</?' . $tag . '[^>]*>' . $content . '#is', '', $str );
        }
        return $str;
    }

}

if ( !function_exists( 'cminds_truncate' ) ) {

    /**
     * From: http://stackoverflow.com/a/2398759/2107024
     * @param type $text
     * @param type $length
     * @param type $ending
     * @param type $exact
     * @param type $considerHtml
     * @return string
     */
    function cminds_truncate( $text, $length = 100, $ending = '...', $exact = false, $considerHtml = true ) {
        if ( is_array( $ending ) ) {
            extract( $ending );
        }
        if ( $considerHtml ) {
            if ( mb_strlen( preg_replace( '/<.*?>/', '', $text ) ) <= $length ) {
                return $text;
            }
            $totalLength = mb_strlen( $ending );
            $openTags    = array();
            $truncate    = '';
            $tags        = array(); //inistialize empty array
            preg_match_all( '/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER );
            foreach ( $tags as $tag ) {
                if ( !preg_match( '/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[ 2 ] ) ) {
                    $closeTag = array();

                    if ( preg_match( '/<[\w]+[^>]*>/s', $tag[ 0 ] ) ) {
                        array_unshift( $openTags, $tag[ 2 ] );
                    } else if ( preg_match( '/<\/([\w]+)[^>]*>/s', $tag[ 0 ], $closeTag ) ) {
                        $pos = array_search( $closeTag[ 1 ], $openTags );
                        if ( $pos !== false ) {
                            array_splice( $openTags, $pos, 1 );
                        }
                    }
                }
                $truncate .= $tag[ 1 ];

                $contentLength = mb_strlen( preg_replace( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[ 3 ] ) );
                if ( $contentLength + $totalLength > $length ) {
                    $left           = $length - $totalLength;
                    $entitiesLength = 0;
                    $entities       = array();
                    if ( preg_match_all( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[ 3 ], $entities, PREG_OFFSET_CAPTURE ) ) {
                        foreach ( $entities[ 0 ] as $entity ) {
                            if ( $entity[ 1 ] + 1 - $entitiesLength <= $left ) {
                                $left--;
                                $entitiesLength += mb_strlen( $entity[ 0 ] );
                            } else {
                                break;
                            }
                        }
                    }

                    $truncate .= mb_substr( $tag[ 3 ], 0, $left + $entitiesLength );
                    break;
                } else {
                    $truncate .= $tag[ 3 ];
                    $totalLength += $contentLength;
                }
                if ( $totalLength >= $length ) {
                    break;
                }
            }
        } else {
            if ( mb_strlen( $text ) <= $length ) {
                return $text;
            } else {
                $truncate = mb_substr( $text, 0, $length - strlen( $ending ) );
            }
        }
        if ( !$exact ) {
            $spacepos = mb_strrpos( $truncate, ' ' );
            if ( isset( $spacepos ) ) {
                if ( $considerHtml ) {
                    $bits        = mb_substr( $truncate, $spacepos );
                    $droppedTags = array();
                    preg_match_all( '/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER );
                    if ( !empty( $droppedTags ) ) {
                        foreach ( $droppedTags as $closingTag ) {
                            if ( !in_array( $closingTag[ 1 ], $openTags ) ) {
                                array_unshift( $openTags, $closingTag[ 1 ] );
                            }
                        }
                    }
                }
                $truncate = mb_substr( $truncate, 0, $spacepos );
            }
        }

        $truncate .= $ending;

        if ( $considerHtml ) {
            foreach ( $openTags as $tag ) {
                $truncate .= '</' . $tag . '>';
            }
        }

        return $truncate;
    }

}

if ( !function_exists( 'cminds_show_message' ) ) {

    /**
     * Generic function to show a message to the user using WP's
     * standard CSS classes to make use of the already-defined
     * message colour scheme.
     *
     * @param $message The message you want to tell the user.
     * @param $errormsg If true, the message is an error, so use
     * the red message style. If false, the message is a status
     * message, so use the yellow information message style.
     */
    function cminds_show_message( $message, $errormsg = false ) {
        if ( $errormsg ) {
            echo '<div id="message" class="error">';
        } else {
            echo '<div id="message" class="updated fade">';
        }

        echo "<p><strong>$message</strong></p></div>";
    }

}

if ( !function_exists( 'cminds_units2bytes' ) ) {

    /**
     * Converts the Apache memory values to number of bytes ini_get('upload_max_filesize') or ini_get('post_max_size')
     * @param type $str
     * @return type
     */
    function cminds_units2bytes( $str ) {
        $units      = array( 'B', 'K', 'M', 'G', 'T' );
        $unit       = preg_replace( '/[0-9]/', '', $str );
        $unitFactor = array_search( strtoupper( $unit ), $units );
        if ( $unitFactor !== false ) {
            return preg_replace( '/[a-z]/i', '', $str ) * pow( 2, 10 * $unitFactor );
        }
    }

}


if ( !function_exists( 'cminds_cmtt_settings_tooltip_tab_content_after' ) ) {

    function cminds_cmtt_settings_tooltip_tab_content_after( $content ) {
        ob_start();
        ?>
        <div class="block">
            <h3>Tooltip - Styling</h3>
            <table class="floated-form-table form-table">
                <tr valign="top">
                    <th scope="row">Tooltip font</th>
                    <td>
                        <?php
                        $fontsArray = array(
                            'default',
                            'Droid Sans',
                            'Roboto',
                            'Lekton',
                            'Economica',
                            'Ropa Sans',
                            'Istok Web',
                            'Arimo',
                            'Gudea',
                            'Exo',
                            'Cousine',
                            'Open Sans',
                            'Open Sans Condensed',
                            'Cuprum',
                        );
                        ?>
                        <select name="cmtt_tooltipFontStyle">
                            <?php foreach ( $fontsArray as $font ) : ?>
                                <option value="<?php echo $font ?>" <?php selected( $font, get_option( 'cmtt_tooltipFontStyle' ) ); ?>> <?php echo $font ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Set the font of the tooltip</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Is clickable?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltipIsClickable" value="0" />
                        <input type="checkbox" name="cmtt_tooltipIsClickable" <?php checked( true, get_option( 'cmtt_tooltipIsClickable', 1 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">With this option you can choose:<br/>
                        <strong>TRUE</strong> - the tooltip should be stationary and clickable<br/>
                        <strong>FALSE</strong> - the tooltip should be floating and unclickable(like in Tooltip Free)<br/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Show "Close" icon</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltipShowCloseIcon" value="0" />
                        <input type="checkbox" name="cmtt_tooltipShowCloseIcon" <?php checked( true, get_option( 'cmtt_tooltipShowCloseIcon', 1 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">With this option you can choose:<br/>
                        <strong>TRUE</strong> - the close icon will be displayed<br/>
                        <strong>FALSE</strong> - there won't be the close icon<br/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Show stem?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltipShowStem" value="0" />
                        <input type="checkbox" name="cmtt_tooltipShowStem" <?php checked( true, get_option( 'cmtt_tooltipShowStem', 1 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">With this option you can choose:<br/>
                        <strong>TRUE</strong> - the stem will be displayed (only if tooltip location is Top/down)<br/>
                        <strong>FALSE</strong> - there won't be no stem<br/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Stem color</th>
                    <td><input type="text" class="colorpicker" name="cmtt_tooltipStemColor" value="<?php echo get_option( 'cmtt_tooltipStemColor', '#fff' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">Set color of tooltip stem</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip placement</th>
                    <td>
                        <?php
                        $positionsArray = array(
                            'horizontal'   => 'Left/right',
                            'vertical'   => 'Top/bottom',
                        );
                        ?>
                        <select name="cmtt_tooltipPlacement">
                            <?php foreach ( $positionsArray as $position => $positionLabel ) : ?>
                                <option value="<?php echo $position ?>" <?php selected( $position, get_option( 'cmtt_tooltipPlacement', 'horizontal' ) ); ?>> <?php echo $positionLabel ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Choose the location of the tooltip.</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Close icon color</th>
                    <td><input type="text" class="colorpicker" name="cmtt_tooltipCloseColor" value="<?php echo get_option( 'cmtt_tooltipCloseColor', '#222' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">Set color of tooltip close icon</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Close icon size</th>
                    <td><input type="number" name="cmtt_tooltipCloseSize" value="<?php echo get_option( 'cmtt_tooltipCloseSize', 20 ); ?>" step="1" min="0" max="50"/>px</td>
                    <td colspan="2" class="cmtt_field_help_container">Set the size of the tooltip close icon</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Close icon symbol</th>
                    <td>
                        <input type="text" name="cmtt_tooltipCloseSymbol" value="<?php echo get_option( 'cmtt_tooltipCloseSymbol', 'dashicons-no' ); ?>" />
                        Preview: <span class="dashicons <?php echo esc_attr( get_option( 'cmtt_tooltipCloseSymbol', 'dashicons-no' ) ); ?>"></span>
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">Set the symbol for the closed icons. You can use any of the <a href="https://developer.wordpress.org/resource/dashicons/#no" target="_blank">WordPress dashicons</a>. You just need to copy the dashicon slug. Default: 'dashicons-no'.</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip background color</th>
                    <td><input type="text" class="colorpicker" name="cmtt_tooltipBackground" value="<?php echo get_option( 'cmtt_tooltipBackground' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">Set color of tooltip background</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip text color</th>
                    <td><input type="text" class="colorpicker" name="cmtt_tooltipForeground" value="<?php echo get_option( 'cmtt_tooltipForeground' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">Set color of tooltip text color</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Force override all text colors in tooltip?</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltipForegroundOverride" value="0" />
                        <input type="checkbox" name="cmtt_tooltipForegroundOverride" <?php checked( true, get_option( 'cmtt_tooltipForegroundOverride', 0 ) ); ?> value="1" />
                    </td>
                    <td colspan="2" class="cmtt_field_help_container">If you select this option then all of the texts in the tooltip (except for the special text and links) will have their colors overridden by the "Tooltip text color"
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip title's text color</th>
                    <td><input type="text" class="colorpicker" name="cmtt_tooltipTitleColor_text" value="<?php echo get_option( 'cmtt_tooltipTitleColor_text' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">Set color of term title in the tooltip. (Works only if the option "Add term title to the tooltip content?" is set)</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip title's background color</th>
                    <td><input type="text" class="colorpicker" name="cmtt_tooltipTitleColor_background" value="<?php echo get_option( 'cmtt_tooltipTitleColor_background' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">Set color of the title's background in the tooltip. (Works only if the option "Add term title to the tooltip content?" is set)</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip border</th>
                    <td>Style: <select name="cmtt_tooltipBorderStyle">
                            <option value="none" <?php selected( 'none', get_option( 'cmtt_tooltipBorderStyle' ) ); ?>>None</option>
                            <option value="solid" <?php selected( 'solid', get_option( 'cmtt_tooltipBorderStyle' ) ); ?>>Solid</option>
                            <option value="dotted" <?php selected( 'dotted', get_option( 'cmtt_tooltipBorderStyle' ) ); ?>>Dotted</option>
                            <option value="dashed" <?php selected( 'dashed', get_option( 'cmtt_tooltipBorderStyle' ) ); ?>>Dashed</option>
                        </select><br />
                        Width: <input type="number" name="cmtt_tooltipBorderWidth" value="<?php echo get_option( 'cmtt_tooltipBorderWidth' ); ?>" step="1" min="0" max="10"/>px<br />
                        Color: <input type="text" class="colorpicker" name="cmtt_tooltipBorderColor" value="<?php echo get_option( 'cmtt_tooltipBorderColor' ); ?>" />
                    </td>

                    <td colspan="2" class="cmtt_field_help_container">Set border styling (style, width, color)</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip rounded corners radius</th>
                    <td><input type="number" name="cmtt_tooltipBorderRadius" value="<?php echo get_option( 'cmtt_tooltipBorderRadius' ); ?>" step="1" min="0" max="50"/>px</td>
                    <td colspan="2" class="cmtt_field_help_container">Set rounded corners radius</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip opacity</th>
                    <td><input type="number" name="cmtt_tooltipOpacity" value="<?php echo get_option( 'cmtt_tooltipOpacity', 90 ); ?>" step="1" min="1" max="100"/></td>
                    <td colspan="2" class="cmtt_field_help_container">Set opacity of tooltip (100=fully opaque, 0=transparent)</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip sizing</th>
                    <td>Min. width: <input type="number" style="width:50px" name="cmtt_tooltipWidthMin" value="<?php echo get_option( 'cmtt_tooltipWidthMin', 200 ); ?>" step="1"/>px<br />
                        Max. width: <input type="number" style="width:50px" name="cmtt_tooltipWidthMax" value="<?php echo get_option( 'cmtt_tooltipWidthMax', 400 ); ?>" step="1"/>px
                    </td>

                    <td colspan="2" class="cmtt_field_help_container">Set the minimal size of the tooltip in pixels. </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip positioning</th>
                    <td>Vertical: <input type="number" style="width:50px" name="cmtt_tooltipPositionTop" value="<?php echo get_option( 'cmtt_tooltipPositionTop' ); ?>" step="1"/>px<br />
                        Horizontal: <input type="number" style="width:50px" name="cmtt_tooltipPositionLeft" value="<?php echo get_option( 'cmtt_tooltipPositionLeft' ); ?>" step="1"/>px
                    </td>

                    <td colspan="2" class="cmtt_field_help_container">Set distance of tooltip's bottom left corner from cursor pointer</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip font size</th>
                    <td><input type="number" style="width:50px" name="cmtt_tooltipFontSize" value="<?php echo get_option( 'cmtt_tooltipFontSize' ); ?>" step="1"/>px
                    </td>

                    <td colspan="2" class="cmtt_field_help_container">Set size of font inside tooltip</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip padding</th>
                    <td><input type="text" name="cmtt_tooltipPadding" value="<?php echo get_option( 'cmtt_tooltipPadding' ); ?>"/>
                    </td>

                    <td colspan="2" class="cmtt_field_help_container">Set internal padding: top, right, bottom, left</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip shadow</th>
                    <td>
                        <input type="hidden" name="cmtt_tooltipShadow" value="0" />
                        <input type="checkbox" name="cmtt_tooltipShadow" <?php checked( true, get_option( 'cmtt_tooltipShadow', 1 ) ); ?> value="1" />
                    </td>

                    <td colspan="2" class="cmtt_field_help_container">Select this option if you like to show the shadow for the tooltip</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip shadow color</th>
                    <td>
                        <input type="text" class="colorpicker" name="cmtt_tooltipShadowColor" value="<?php echo get_option( 'cmtt_tooltipShadowColor', '#666666' ); ?>"/>
                    </td>

                    <td colspan="2" class="cmtt_field_help_container">Set the color of the shadow of the tooltip</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip display delay</th>
                    <td>
                        <input type="text" name="cmtt_tooltipDisplayDelay" value="<?php echo get_option( 'cmtt_tooltipDisplayDelay', 0 ); ?>"/>
                    </td>

                    <td colspan="2" class="cmtt_field_help_container">Set the delay (in miliseconds (1000ms = 1s)) before the tooltip appears</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip hide delay</th>
                    <td>
                        <input type="text" name="cmtt_tooltipHideDelay" value="<?php echo get_option( 'cmtt_tooltipHideDelay', 0 ); ?>"/>
                    </td>

                    <td colspan="2" class="cmtt_field_help_container">Set the delay (in miliseconds (1000ms = 1s)) before the tooltip fades out</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip internal link color</th>
                    <td><input type="text" class="colorpicker" name="cmtt_tooltipInternalLinkColor" value="<?php echo get_option( 'cmtt_tooltipInternalLinkColor' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">Set the color of the links inside the tooltip.</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip edit link color</th>
                    <td><input type="text" class="colorpicker" name="cmtt_tooltipInternalEditLinkColor" value="<?php echo get_option( 'cmtt_tooltipInternalEditLinkColor' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">Set the color of the edit links in the tooltip. (Added only when the "Add term editlink to the tooltip content?" is enabled) </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tooltip mobile link color</th>
                    <td><input type="text" class="colorpicker" name="cmtt_tooltipInternalMobileLinkColor" value="<?php echo get_option( 'cmtt_tooltipInternalMobileLinkColor' ); ?>" /></td>
                    <td colspan="2" class="cmtt_field_help_container">Set color of link to the term page in the tooltip. (Added only when the mobile support is enabled and on mobile device)</td>
                </tr>

            </table>
        </div>
        <?php
        $content = ob_get_clean();
        return $content;
    }

}

if ( !function_exists( 'array_column' ) ) {

    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column( $input = null, $columnKey = null, $indexKey = null ) {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc   = func_num_args();
        $params = func_get_args();

        if ( $argc < 2 ) {
            trigger_error( "array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING );
            return null;
        }

        if ( !is_array( $params[ 0 ] ) ) {
            trigger_error( 'array_column() expects parameter 1 to be array, ' . gettype( $params[ 0 ] ) . ' given', E_USER_WARNING );
            return null;
        }

        if ( !is_int( $params[ 1 ] ) && !is_float( $params[ 1 ] ) && !is_string( $params[ 1 ] ) && $params[ 1 ] !== null && !(is_object( $params[ 1 ] ) && method_exists( $params[ 1 ], '__toString' ))
        ) {
            trigger_error( 'array_column(): The column key should be either a string or an integer', E_USER_WARNING );
            return false;
        }

        if ( isset( $params[ 2 ] ) && !is_int( $params[ 2 ] ) && !is_float( $params[ 2 ] ) && !is_string( $params[ 2 ] ) && !(is_object( $params[ 2 ] ) && method_exists( $params[ 2 ], '__toString' ))
        ) {
            trigger_error( 'array_column(): The index key should be either a string or an integer', E_USER_WARNING );
            return false;
        }

        $paramsInput     = $params[ 0 ];
        $paramsColumnKey = ($params[ 1 ] !== null) ? (string) $params[ 1 ] : null;

        $paramsIndexKey = null;
        if ( isset( $params[ 2 ] ) ) {
            if ( is_float( $params[ 2 ] ) || is_int( $params[ 2 ] ) ) {
                $paramsIndexKey = (int) $params[ 2 ];
            } else {
                $paramsIndexKey = (string) $params[ 2 ];
            }
        }

        $resultArray = array();

        foreach ( $paramsInput as $row ) {

            $key      = $value    = null;
            $keySet   = $valueSet = false;

            if ( $paramsIndexKey !== null && array_key_exists( $paramsIndexKey, $row ) ) {
                $keySet = true;
                $key    = (string) $row[ $paramsIndexKey ];
            }

            if ( $paramsColumnKey === null ) {
                $valueSet = true;
                $value    = $row;
            } elseif ( is_array( $row ) && array_key_exists( $paramsColumnKey, $row ) ) {
                $valueSet = true;
                $value    = $row[ $paramsColumnKey ];
            }

            if ( $valueSet ) {
                if ( $keySet ) {
                    $resultArray[ $key ] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }

        return $resultArray;
    }

}