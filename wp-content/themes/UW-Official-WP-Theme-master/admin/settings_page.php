<?php

function uw_theme_options() {
         // do necessary validation
         $options = uw_theme_options_validation();
         
?>
        <div class="wrap">
            <?php screen_icon('themes'); ?> <h2>UW Theme Options</h2>
            <form method="post" name="uw_theme_settings" target="_self">
               
                <h3>Dropdown Menus</h3>
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="nav_update_frequency">Update frequency </label>
                            </th>
                            <td>
                                <select name="nav_update_frequency" id="nav_update_frequency">
                                    <?php 
                                        $day = $options['_nav_update_frequency'] / 86400;
                                        for ( $i = 1; $i < 30; $i++ ) {
                                            $plural   = ( $i > 1 ) ? 's' : '';
                                            $selected = ( $i == $day ) ? 'selected="selected"' : '';
                                            echo "<option value='$i' $selected>$i day$plural</option>";
                                        }; 
                                        //$selected = ( $day < 0 ) ? 'selected="selected"' : '';
                                        //echo "option value='-1' $selected> Never </option>"; 
                                    ?>
                                </select><br/>
                                <span class="description"> Frequency the dropdown navigation is synced with the <a href="http://uw.edu">UW homepage</a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h3>Theme Preferences</h3>
                    <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="theme_update_frequency">Update frequency</label>
                            </th>
                            <td>
                                <select name="theme_update_frequency" id="theme_update_frequency">
                                    <?php 
                                        $week = $options['_theme_update_frequency'] / 604800;
                                        for ( $i = 1; $i < 5; $i++ ) {
                                            $plural = ( $i > 1 ) ? 's' : '';
                                            $selected = ( $i == $week ) ? 'selected="selected"' : '';
                                            echo "<option value='$i' $selected>$i week$plural</option>";
                                        } 
                                        //$selected = ( $week < 0 ) ? 'selected="selected"' : '';
                                        //echo "option value='never' $selected> Never </option>"; 
                                    ?>
                                </select><br/>
                                <span class="description"> Frequency the theme will check for a new version. </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

            <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"></p>
            </form>
        </div>

<?php } ?>
<?php

function uw_theme_options_validation() {

        $options = get_option( 'uw_theme_settings' );
        if( isset( $_POST[ 'submit' ] ) ) {

            $class   = 'updated';
            $errors  = array();

            extract( $_POST );

            if( is_numeric( $nav_update_frequency ) && ( int ) $nav_update_frequency > 0 ) {
                //convert the supplied days to seconds
                $options[ '_nav_update_frequency' ] = $nav_update_frequency * 86400; 
            } else {
                $class = 'error';
                array_push( $errors, 'Navigation frequency' );
            }


            if( is_numeric( $theme_update_frequency ) && ( int ) $theme_update_frequency > 0 ) {
                //convert the supplied weeks to seconds
                $options['_theme_update_frequency'] = $theme_update_frequency * 604800; 
            } else {
                $class = 'error';
                array_push( $errors, 'Theme frequency' );
            }

            if( $class == 'updated') {
                update_option( 'uw_theme_settings', $options ); 
                $str  = __( 'Options Saved', 'mt_trans_domain' );
                $html = "<div class='$class'><p><strong>$str</strong></p></div>";
            } else {
                $fields = implode( ', ', $errors );
                $str  = __( "There was an error with the following: $fields ", 'mt_trans_domain' );
                $html = "<div class='$class'><p><strong>$str</strong></p></div>";
            }

            echo $html;

        }
        return $options;
} ?>
