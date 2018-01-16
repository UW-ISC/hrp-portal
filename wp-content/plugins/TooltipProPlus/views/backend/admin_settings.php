<?php if ( !empty( $messages ) ): ?>
    <div class="updated" style="clear:both"><p><?php echo $messages; ?></p></div>
<?php endif; ?>

<br/>

<br/>

<?php echo do_shortcode( '[cminds_free_ads id="cmtt"]' ); ?>

<div class="cminds_settings_description">
    <p>
        <?php
        global $cmindsPluginPackage;
        $shortcodesPage = $cmindsPluginPackage[ 'cmtt' ]->licensingApi->getPageSlug( 'shortcodes' );
        ?>
        <strong>Supported Shortcodes:</strong> <a href="<?php echo get_admin_url( '', 'admin.php?page=' . esc_attr( $shortcodesPage ) ); ?>">See list</a>
    </p>

    <p>
        <?php
        $glossaryId     = CMTT_Glossary_Index::getGlossaryIndexPageId();
        if ( $glossaryId > 0 ) :

            $glossaryIndexPageEditLink = admin_url( 'post.php?post=' . $glossaryId . '&action=edit' );
            $glossaryIndexPageLink     = get_page_link( $glossaryId );
            ?>
            <strong>Link to the Glossary Index Page:</strong> <a href="<?php echo $glossaryIndexPageLink; ?>" target="_blank"><?php echo $glossaryIndexPageLink; ?></a> (<a title="Edit the Glossary Index Page" href="<?php echo $glossaryIndexPageEditLink; ?>">edit</a>)
            <?php
        endif;
        ?>
    </p>
    <p>
        <strong>Example of Glossary Term link:</strong> <?php echo trailingslashit( home_url( get_option( 'cmtt_glossaryPermalink' ) ) ) . 'sample-term' ?>
    </p>
    <form method="post">
        <div>
            <div class="cmtt_field_help_container">Warning! This option will completely erase all of the data stored by the CM Tooltip Glossary in the database: terms, options, synonyms etc. <br/> It will also remove the Glossary Index Page. <br/> It cannot be reverted.</div>
            <input onclick="return confirm( 'All options of CM Tooltip Glossary will be erased. This cannot be reverted.' )" type="submit" name="cmtt_removeAllOptions" value="Remove all options" class="button cmtt-cleanup-button"/>
            <input onclick="return confirm( 'All terms of CM Tooltip Glossary will be erased. This cannot be reverted.' )" type="submit" name="cmtt_removeAllItems" value="Remove all items" class="button cmtt-cleanup-button"/>
            <span style="display: inline-block;position: relative;"></span>
        </div>
    </form>

    <?php
// check permalink settings
    if ( get_option( 'permalink_structure' ) == '' ) {
        echo '<span style="color:red">Your WordPress Permalinks needs to be set to allow plugin to work correctly. Please Go to <a href="' . admin_url() . 'options-permalink.php" target="new">Settings->Permalinks</a> to set Permalinks to Post Name.</span><br><br>';
    }
    ?>

</div>

<br/>
<div class="clear"></div>

<form method="post">
    <?php wp_nonce_field( 'update-options' ); ?>
    <input type="hidden" name="action" value="update" />


    <div id="cmtt_tabs" class="glossarySettingsTabs">
        <div class="glossary_loading"></div>

        <?php
        CMTT_Pro::renderSettingsTabsControls();

        CMTT_Pro::renderSettingsTabs();
        ?>

        <div id="tabs-1">
            <div class="block">
                <h3>General Settings</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top" class="whole-line">
                        <th scope="row">Glossary Index Page ID</th>
                        <td>
                            <?php wp_dropdown_pages( array( 'name' => 'cmtt_glossaryID', 'selected' => (int) get_option( 'cmtt_glossaryID', -1 ), 'show_option_none' => '-None-', 'option_none_value' => '0' ) ) ?>
                            <br/><input type="checkbox" name="cmtt_glossaryID" value="-1" /> Generate page for Glossary Index
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select the page ID of the page you would like to use as the Glossary Index Page. If you select "-None-" terms will still be highlighted in relevant posts/pages but there won't be a central list of terms (Glossary Index Page). If you check the checkbox a new page would be generated automatically. WARNING! You have to manually remove old pages!</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Roles allowed to add/edit terms:</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryRoles" value="0" />
                            <?php
                            echo CMTT_Pro::outputRolesList();
                            ?>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select the custom post types where you'd like the Glossary Terms to be highlighted.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Create Glossary Term Pages:</th>
                        <td>
                            <input type="hidden" name="cmtt_createGlossaryTermPages" value="0" />
                            <input type="checkbox" name="cmtt_createGlossaryTermPages" <?php checked( true, get_option( 'cmtt_createGlossaryTermPages', TRUE ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Uncheck this if you don't want the Glossary Term pages to be created. <strong>After disabling this all of the links to the Glossary Term pages will be removed.</strong></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Exclude Glossary Term Pages from search:</th>
                        <td>
                            <input type="hidden" name="cmtt_excludeGlossaryTermPagesFromSearch" value="0" />
                            <input type="checkbox" name="cmtt_excludeGlossaryTermPagesFromSearch" <?php checked( true, get_option( 'cmtt_excludeGlossaryTermPagesFromSearch', '0' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Uncheck this if you don't want the Glossary Term pages to be displayed in the search results.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Glossary Terms Permalink</th>
                        <td><input type="text" name="cmtt_glossaryPermalink" value="<?php echo get_option( 'cmtt_glossaryPermalink' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Enter the name you would like to use for the permalink to the Glossary Terms.
                            By default this is "glossary", however you can update this if you wish.
                            If you are using a parent please indicate this in path eg. "/path/glossary", otherwise just leave glossary or the name you have chosen.
                            <br/><br/>
                            The permalink of the Glossary Index Page will change automatically, but you can change it manually (if you like) using the "edit" link near the "Link to the Glossary Index Page" above.
                            <br/><br/>WARNING! If you already use this permalink the plugin's behavior may be unpredictable.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Glossary Categories Permalink</th>
                        <td><input type="text" name="cmtt_glossaryCategoriesPermalink" value="<?php echo get_option( 'cmtt_glossaryCategoriesPermalink', 'glossary-categories' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Enter the name you would like to use for the permalink for the Glossary Categories.
                            By default this is "glossary-categories", however you can update this if you wish.
                            If you are using a parent please indicate this in path eg. "/path/glossary-categories", otherwise just leave glossary-categories or the name you have chosen.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Glossary Tags Permalink</th>
                        <td><input type="text" name="cmtt_glossaryTagsPermalink" value="<?php echo get_option( 'cmtt_glossaryTagsPermalink', 'glossary-tags' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Enter the name you would like to use for the permalink to the Glossary Tags.
                            By default this is "glossary-tags", however you can update this if you wish.
                            If you are using a parent please indicate this in path eg. "/path/glossary-tags", otherwise just leave glossary-tags or the name you have chosen.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Enable RTL Support</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryRTL" value="0" />
                            <input type="checkbox" name="cmtt_glossaryRTL" <?php checked( true, get_option( 'cmtt_glossaryRTL', '0' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Enable right to left text for CM Tooltip</td>
                    </tr>
                </table>
                <div class="clear"></div>
            </div>
            <div class="block">
                <h3>Abbreviations Settings</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Abbreviations brackets</th>
                        <td>
                            <select name="cmtt_roundBracketsAbbr">
                                <option value="square" <?php selected( 'square', get_option( 'cmtt_roundBracketsAbbr' ) ); ?>>Square[]</option>
                                <option value="round" <?php selected( 'round', get_option( 'cmtt_roundBracketsAbbr' ) ); ?>>Round()</option>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Change abbreviation square brackets to round</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Disable glossary abbreviations</th>
                        <td>
                            <input type="hidden" name="cmtt_disableGlossaryAbbr" value="0" />
                            <input type="checkbox" name="cmtt_disableGlossaryAbbr" <?php checked( true, get_option( 'cmtt_disableGlossaryAbbr', '0' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Disable showing abbreviations in the Glossary</td>
                    </tr>
                </table>
                <div class="clear"></div>
            </div>
            <div class="block">
                <h3>Advanced Custom Fields Settings</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Highlight terms in ACF fields?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryParseACFFields" value="0" />
                            <input type="checkbox" name="cmtt_glossaryParseACFFields" <?php checked( true, get_option( 'cmtt_glossaryParseACFFields' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container"> Select this option if you wish to highlight Glossary Terms in ALL of the "Advanced Custom Fields" fields.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Types of fields to highlight:</th>
                        <td>
                            <input type="hidden" name="cmtt_acf_parsed_field_types" value="0" />
                            <?php
                            echo CMTT_Pro::outputACFTypesList();
                            ?>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select the types of ACF fields in which you'd like the Glossary Terms to be highlighted.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Types of fields to remove the WP functions:</th>
                        <td>
                            <input type="hidden" name="cmtt_acf_remove_filters_for_type" value="0" />
                            <?php
                            echo CMTT_Pro::outputACFTypesList( 'cmtt_acf_remove_filters_for_type', array( 'text' ) );
                            ?>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select the types of ACF fields for which the built in WP filters adding paragraphs and newlines should be removed.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Don't use the DOM parser for ACF fields?</th>
                        <td>
                            <input type="hidden" name="cmtt_disableDOMParserForACF" value="0" />
                            <input type="checkbox" name="cmtt_disableDOMParserForACF" <?php checked( true, get_option( 'cmtt_disableDOMParserForACF', FALSE ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to parse the ACF fields using the simple parser (preg_replace) instead of DOM parser. Warning! May break content.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" ><?php _e( 'Excluded ACF Field IDs', 'cm-tooltip-glossary' ); ?>:</th>
                        <td>
                            <input type="text" name="cmtt_disableACFfields" value="<?php echo get_option( 'cmtt_disableACFfields' ); ?>" placeholder="<?php _e( 'field_id,field_2_id', 'cm-tooltip-glossary' ); ?>"/>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">You can put here the comma separated list of IDs of the ACF fields you would like to exclude from being parsed.</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Term higlighting</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Highlight terms on given post types:</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryOnPosttypes" value="0" />
                            <?php
                            echo CMTT_Pro::outputCustomPostTypesList();
                            ?>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select the custom post types where you'd like the Glossary Terms to be highlighted.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Only show terms on single posts/pages (not Homepage, authors etc.)?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryOnlySingle" value="0" />
                            <input type="checkbox" name="cmtt_glossaryOnlySingle" <?php checked( true, get_option( 'cmtt_glossaryOnlySingle' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish to only highlight glossary terms when viewing a single page/post.
                            This can be used so terms aren't highlighted on your homepage, or author pages and other taxonomy related pages.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Highlight terms in bbPress replies?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryParseBBPressFields" value="0" />
                            <input type="checkbox" name="cmtt_glossaryParseBBPressFields" <?php checked( true, get_option( 'cmtt_glossaryParseBBPressFields' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container"> Select this option if you wish to highlight Glossary Terms in ALL of the "bbPress" replies.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Highlight terms on BuddyPress pages?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryParseBuddyPressPages" value="0" />
                            <input type="checkbox" name="cmtt_glossaryParseBuddyPressPages" <?php checked( true, get_option( 'cmtt_glossaryParseBuddyPressPages', 1 ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container"> Select this option if you wish to highlight Glossary Terms in ALL of the "bbPress" replies.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Highlight first term occurance only?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryFirstOnly" value="0" />
                            <input type="checkbox" name="cmtt_glossaryFirstOnly" <?php checked( true, get_option( 'cmtt_glossaryFirstOnly' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to only highlight the first occurance of each term on a page/post.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Highlight only space separated terms?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryOnlySpaceSeparated" value="0" />
                            <input type="checkbox" name="cmtt_glossaryOnlySpaceSeparated" <?php checked( true, get_option( 'cmtt_glossaryOnlySpaceSeparated' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to only search for the terms separated from other words (usually by space).</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Highlight the terms in comments</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTermsInComments" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTermsInComments" <?php checked( true, get_option( 'cmtt_glossaryTermsInComments' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to highlight the glossary terms in the comments.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Highlight the terms in Text Widget</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryParseTextWidget" value="0" />
                            <input type="checkbox" name="cmtt_glossaryParseTextWidget" <?php checked( true, get_option( 'cmtt_glossaryParseTextWidget', 1 ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to highlight the glossary terms in the Text Widget built in WordPress.</td>
                    </tr>
                </table>
                <div class="clear"></div>
            </div>
            <div class="block">
                <h3>Performance &amp; Debug</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Add RSS feeds?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryAddFeeds" value="0" />
                            <input type="checkbox" name="cmtt_glossaryAddFeeds" <?php checked( true, get_option( 'cmtt_glossaryAddFeeds', true ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            <strong>Warning: Don't change this setting unless you know what you're doing</strong><br/>
                            Select this option if you want to have the RSS feeds for your glossary terms.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Load the scripts in footer?</th>
                        <td>
                            <input type="hidden" name="cmtt_script_in_footer" value="0" />
                            <input type="checkbox" name="cmtt_script_in_footer" <?php checked( true, get_option( 'cmtt_script_in_footer' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            <strong>Warning: Don't change this setting unless you know what you're doing</strong><br/>
                            Select this option if you want to load the plugin's js files in the footer.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Only highlight on "main" WP query?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryOnMainQuery" value="0" />
                            <input type="checkbox" name="cmtt_glossaryOnMainQuery" <?php checked( 1, get_option( 'cmtt_glossaryOnMainQuery' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            <strong>Warning: Don't change this setting unless you know what you're doing</strong><br/>
                            Select this option if you wish to only highlight glossary terms on main glossary query.
                            Unchecking this box may fix problems with highlighting terms on some themes which manipulate the WP_Query.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Run the function outputting the Glossary Index Page only once</th>
                        <td>
                            <input type="hidden" name="cmtt_removeGlossaryCreateListFilter" value="0" />
                            <input type="checkbox" name="cmtt_removeGlossaryCreateListFilter" <?php checked( 1, get_option( 'cmtt_removeGlossaryCreateListFilter' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            <strong>Warning: Don't change this setting unless you know what you're doing</strong><br/>
                            Select this option if you wish to remove the filter responsible for outputting the Glossary Index. <br/>
                            When this option is selected the function responsible for rendering the Glossary Index page (hooked to "the_content" filter) <br/>
                            will run only once and then it will be removed. It's known that this conflicts with some translation plugins (e.g. qTranslate, Jetpack, PageBuilder).
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Enable the caching mechanisms</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryEnableCaching" value="0" />
                            <input type="checkbox" name="cmtt_glossaryEnableCaching" <?php checked( true, get_option( 'cmtt_glossaryEnableCaching', FALSE ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to use the internal caching mechanisms.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Disable the "Hide term from Glossary Index" functionality</th>
                        <td>
                            <input type="hidden" name="cmtt_enableHidingFromIndex" value="0" />
                            <input type="checkbox" name="cmtt_enableHidingFromIndex" <?php checked( true, get_option( 'cmtt_enableHidingFromIndex', FALSE ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to disable the functionality. Doing this solves the performance problems with long query on some hostings.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Enable embedded mode?</th>
                        <td>
                            <input type="hidden" name="cmtt_enableEmbeddedMode" value="0" />
                            <input type="checkbox" name="cmtt_enableEmbeddedMode" <?php checked( true, get_option( 'cmtt_enableEmbeddedMode', false ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            <strong>Warning: Don't change this setting unless you know what you're doing</strong><br/>
                            Select this option if you want to embedd the WordPress pages on other platform - eg. using Magento FishPig (it changes the way JS files are loaded)</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Remove the parsing of the excerpts?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryRemoveExcerptParsing" value="0" />
                            <input type="checkbox" name="cmtt_glossaryRemoveExcerptParsing" <?php checked( true, get_option( 'cmtt_glossaryRemoveExcerptParsing', 1 ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Uncheck this option if you'd like to parse the excerpts in search for the glossary terms.</td>
                    </tr>
                </table>
                <div class="clear"></div>
            </div>
            <div class="block">
                <h3>Backup</h3>
                <p>Easily backup your glossary to the file. You can create/download a backup on the <a href="<?php echo admin_url( 'admin.php?page=cmtt_importexport' ); ?>">Import/Export</a> page.</p>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" >PIN Protect</th>
                        <td>
                            <input type="text" name="cmtt_glossary_backup_pinprotect" value="<?php echo get_option( 'cmtt_glossary_backup_pinprotect' ); ?>"/>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Fill this field with a PIN code which will be required to get the backup. Leave empty to disable PIN Protection.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" >Secure Backup</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_backup_secure" value="0" />
                            <input type="checkbox" name="cmtt_glossary_backup_secure" <?php checked( true, get_option( 'cmtt_glossary_backup_secure', true ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this field if you want to use the secure WP Filesystem API for the file saves. Note: This may require the FTP/SSH credentials.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Backup rebuild interval:</th>
                        <td>
                            <select name="cmtt_glossary_backupCronInterval" >
                                <?php
                                $types            = wp_get_schedules();
                                $selectedInterval = get_option( 'cmtt_glossary_backupCronInterval', 'none' );
                                ?>
                                <option value="none" <?php selected( 'none', $selectedInterval ) ?>><?php _e( 'Never', 'cm-tooltip-glossary' ) ?></option>
                                <?php foreach ( $types as $typeName => $type ): ?>
                                    <option value="<?php echo $typeName; ?>" <?php selected( $typeName, $selectedInterval ) ?>><?php echo $type[ 'display' ]; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Choose how often the backup of the glossary is saved. Choose 'none' to disable automatic saves.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Backup rebuild hour:</th>
                        <td><input type="time" placeholder="00:00" size="5" name="cmtt_glossary_backupCronHour" value="<?php echo get_option( 'cmtt_glossary_backupCronHour' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Choose the hour when the Glossary Index Backup save should take place. The hour should be properly formatted string eg. 23:00 or 1 AM</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Edit Screen Elements</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" >&quot;CM Tooltip - Disables&quot; metabox</th>
                        <td>
                            <input type="hidden" name="cmtt_disable_metabox_all_post_types" value="0" />
                            <input type="checkbox" name="cmtt_disable_metabox_all_post_types" <?php checked( true, get_option( 'cmtt_disable_metabox_all_post_types' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to display the metabox allowing to disable tooltips on all post types.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" >&quot;CM Tooltip - Allowed Terms&quot; metabox</th>
                        <td>
                            <input type="hidden" name="cmtt_allowed_terms_metabox_all_post_types" value="0" />
                            <input type="checkbox" name="cmtt_allowed_terms_metabox_all_post_types" <?php checked( true, get_option( 'cmtt_allowed_terms_metabox_all_post_types' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to display the metabox allowing to set allowed terms list on all post types.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" >Show Visual Editor additional buttons?</th>
                        <td>
                            <input type="hidden" name="cmtt_add_richedit_buttons" value="0" />
                            <input type="checkbox" name="cmtt_add_richedit_buttons" <?php checked( true, get_option( 'cmtt_add_richedit_buttons', '1' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to display plugin's additional buttons in Visual Editor.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" ><?php _e( 'Synonym Suggestions API', 'cm-tooltip-glossary' ); ?>:</th>
                        <td>
                            <input type="text" name="cmtt_glossarySynonymSuggestionsAPI" value="<?php echo get_option( 'cmtt_glossarySynonymSuggestionsAPI' ); ?>" placeholder="<?php _e( 'Affiliate Code', 'cm-tooltip-glossary' ); ?>"/>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">To get the API Key please go to <a href="https://words.bighugelabs.com/getkey.php" target="_blank">Big Huge Thesaurus</a></td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Referrals</h3>
                <p>Refer new users to any of the CM Plugins and you'll receive a minimum of <strong>15%</strong> of their purchase! For more information please visit CM Plugins <a href="http://www.cminds.com/referral-program/" target="new">Affiliate page</a></p>
                <table>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" >Enable referrals:</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryReferral" value="0" />
                            <input type="checkbox" name="cmtt_glossaryReferral" <?php checked( 1, get_option( 'cmtt_glossaryReferral' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Enable referrals link at the bottom of the question and the answer page<br><br></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" valign="middle" align="left" ><?php _e( 'Affiliate Code', 'cm-tooltip-glossary' ); ?>:</th>
                        <td>
                            <input type="text" name="cmtt_glossaryAffiliateCode" value="<?php echo get_option( 'cmtt_glossaryAffiliateCode' ); ?>" placeholder="<?php _e( 'Affiliate Code', 'cm-tooltip-glossary' ); ?>"/>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container"><?php _e( 'Please add your affiliate code in here.', 'cm-tooltip-glossary' ); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div id="tabs-2">
            <div class="block">
                <h3>Glossary Index Page Settings</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Display style:</th>
                        <td><select name="cmtt_glossaryDisplayStyle">
                                <option value="classic" <?php selected( 'classic', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Classic</option>
                                <option value="classic-definition" <?php selected( 'classic-definition', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Classic + definition</option>
                                <option value="classic-excerpt" <?php selected( 'classic-excerpt', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Classic + excerpt</option>
                                <option value="small-tiles" <?php selected( 'small-tiles', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Small Tiles</option>
                                <option value="big-tiles" <?php selected( 'big-tiles', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Big Tiles</option>
                                <option value="classic-table" <?php selected( 'classic-table', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Classic table</option>
                                <option value="modern-table" <?php selected( 'modern-table', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Modern table</option>
                                <option value="sidebar-termpage" <?php selected( 'sidebar-termpage', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Sidebar + term page</option>
                                <option value="expand-style" <?php selected( 'expand-style', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Expand + description</option>
                                <option value="grid-style" <?php selected( 'grid-style', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Grid + terms</option>
                                <option value="cube-style" <?php selected( 'cube-style', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Cube</option>
                                <option value="image-tiles-view" <?php selected( 'image-tiles-view', get_option( 'cmtt_glossaryDisplayStyle' ) ); ?>>Image Tiles View</option>
                            </select><br />
                        <td colspan="2" class="cmtt_field_help_container">Set display style of the Glossary Index page. By default the "Classic" style is selected.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show featured image thumbnail?</th>
                        <td>
                            <input type="hidden" name="cmtt_showFeaturedImageThumbnail" value="0" />
                            <input type="checkbox" name="cmtt_showFeaturedImageThumbnail" <?php checked( true, get_option( 'cmtt_showFeaturedImageThumbnail' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select this option if you want to display the thumbnails of the featured image on the Glossary Index (when available).
                            <br/><i>Works only on "Classic + definition", "Classic + excerpt"</i>
                        </td>
                    </tr>
                    <!-- Image Uploader
                    Can be used anywhere, just replace name
                    -->
                    <tr valign="top">
                        <?php wp_enqueue_media(); ?>
                        <th scope="row">Choose an image for posts without thumbnail</th>
                        <td class="CM_Media_Uploader">
                            <?php
                            if ( class_exists( 'CMTT_Glossary_Plus' ) ) {
                                echo CMTT_Glossary_Plus::_image_uploader( 'cmtt_glossary_no_thumb' );
                            }
                            ?>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Upload the image if you want to replace the default one showing when no image thumbnail is set. Click on the uploaded image to remove it.<br/><br/>
                            Preview the defaut image
                            <br/><img src="<?php echo plugin_dir_url( __FILE__ ); ?>../../assets/no_image.jpg" width="100" height="100">
                        </td>
                    </tr> <!-- End -->
                    <tr valign="top">
                        <th scope="row">Remove the HTML tags from definition?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTooltipDescStripTags" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTooltipDescStripTags" <?php checked( true, get_option( 'cmtt_glossaryTooltipDescStripTags', 1 ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select this option if you want to remove the html characters from the definition.
                            <br/><i>Works only on "Classic + definition", "Classic + excerpt" and "Modern table"</i>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Limit the definition length</th>
                        <td>
                            <script>
                                jQuery( document ).ready( function () {
                                    jQuery( '.toggleLenghTester' ).on( 'click', function () {
                                        jQuery( '#cmtt_definitionLengthTester' ).toggle( 'fast' );
                                    } );
                                    jQuery( '#cmtt_definitionLengthTester' ).hide();
                                    jQuery( 'input[name="cmtt_glossaryTooltipDescLength"]' ).on( 'change', function () {
                                        var value = jQuery( this ).val();
                                        jQuery( '#cmtt_definitionLengthTester' ).attr( 'maxlength', value );
                                    } );
                                } )
                            </script>
                            <style>
                                #cmtt_definitionLengthTester{display: block; margin: 1px; padding: 3px 5px; width: 193px;}
                            </style>
                            <input type="text" name="cmtt_glossaryTooltipDescLength" value="<?php echo get_option( 'cmtt_glossaryTooltipDescLength', 300 ); ?>" />
                            <div class="button toggleLenghTester">Toggle Length Tester</div>
                            <textarea type="text" placeholder="You can test length visually by typing or pasting here." id="cmtt_definitionLengthTester" maxlength="<?php echo get_option( 'cmtt_glossaryTooltipDescLength', 300 ); ?>"></textarea>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select this option if you want to show only a limited number of chars of the decinition and add "(...)" at the end. Minimum is 30 chars.
                            <br/><i>Works only on "Classic + definition", "Classic + excerpt" and "Modern table"</i>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show the "Read More" link after the truncated definition</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryIndexDescReadMore" value="0" />
                            <input type="checkbox" name="cmtt_glossaryIndexDescReadMore" <?php checked( true, get_option( 'cmtt_glossaryIndexDescReadMore', 0 ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select this option if you want to show a link to the glossary term page after displaying the limited number of the characters from the description.
                            <br/><i>Works only on "Classic + definition", "Classic + excerpt" and "Modern table"</i>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Small tiles tile width</th>
                        <td><input type="text" name="cmtt_glossarySmallTileWidth" value="<?php echo get_option( 'cmtt_glossarySmallTileWidth', '85px' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select the width of the single tile in the "Small tiles" view
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Big tiles tile width</th>
                        <td><input type="text" name="cmtt_glossaryBigTileWidth" value="<?php echo get_option( 'cmtt_glossaryBigTileWidth', '179px' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select the width of the single tile in the "Big tiles" view
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Strip the shortcodes from definition?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryIndexDescStripShortcode" value="0" />
                            <input type="checkbox" name="cmtt_glossaryIndexDescStripShortcode" <?php checked( true, get_option( 'cmtt_glossaryIndexDescStripShortcode' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select this option if you want to strip the shortcodes from the definition displayed on the Glossary Index page.
                            <br/><i>Works only on "Classic + definition", "Classic + excerpt" and "Modern table"</i>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Run the API calls on the Glossary Index page?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryRunApiCalls" value="0" />
                            <input type="checkbox" name="cmtt_glossaryRunApiCalls" <?php checked( true, get_option( 'cmtt_glossaryRunApiCalls' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to call the APIs on the Glossary Index page. <br/>
                            <strong>Warning!</strong> Enabling this option can slow the loading time of the Glossary Index page drastically. </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Remove the tooltips on the Glossary Index Page?</th>
                        <td>&nbsp;</td>
                        <?php
                        $link             = admin_url( 'post.php?post=' . get_option( 'cmtt_glossaryID' ) . '&action=edit' );
                        ?>
                        <td colspan="2" class="cmtt_field_help_container">If you want to remove the tooltip from the Glossary Index page, you should edit the page using Wordpress's Pages menu (or clicking <a href="<?php echo $link; ?>" target="_blank">this link</a>)<br/>
                            And in the <strong>"Tooltip Plugin"</strong> tab select the option <strong>"Exclude this page from Tooltip plugin"</strong></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Mark terms not older than X days as "New"</th>
                        <td><input type="text" name="cmtt_glossaryNewItemMaxDays" value="<?php echo get_option( 'cmtt_glossaryNewItemMaxDays', '0' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">
                            If this setting contains a positive number then Glossary Terms not older than this number will be marked as "New". 0 turns off the feature.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Title for the mark indicating "New" terms</th>
                        <td><input type="text" name="cmtt_glossaryNewItemMarkTitle" value="<?php echo get_option( 'cmtt_glossaryNewItemMarkTitle', __( 'New!', 'cm-tooltip-glossary' ) ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">
                            You can select the title which will appear as a title on hover over the star indicating that the term is "New".
                        </td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Links</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Remove the link from Glossary Index to the Glossary Term pages?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryListTermLink" value="0" />
                            <input type="checkbox" name="cmtt_glossaryListTermLink" <?php checked( true, get_option( 'cmtt_glossaryListTermLink' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you do not want to show links to the glossary term pages on the Glossary Index page. Keep in mind that the plugin use a <strong>&lt;span&gt;</strong> tag instead of a link tag and if you are using a custom CSS you should take this into account</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Style links differently?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryDiffLinkClass" value="0" />
                            <input type="checkbox" name="cmtt_glossaryDiffLinkClass" <?php checked( true, get_option( 'cmtt_glossaryDiffLinkClass' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish for the links in the Glossary Index page to be styled differently than the regular way glossary terms links are styled.  By selecting this option you will be able to use the class 'glossaryLinkMain' to style only the links on the Glossary Index page otherwise they will retain the class 'glossaryLink' and will be identical to the linked terms on all other pages.</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Sharing box</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Show the sharing box on the Glossary Index Page?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryShowShareBox" value="0" />
                            <input type="checkbox" name="cmtt_glossaryShowShareBox" <?php checked( true, get_option( 'cmtt_glossaryShowShareBox' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish to show the "Share This" box on the Glossary Index Page with links to Facebook, Twitter, Google+ and LinkedIn.</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Search, Categories &amp; Tags</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Show the search on the Glossary Index page</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_showSearch" value="0" />
                            <input type="checkbox" name="cmtt_glossary_showSearch" <?php checked( true, get_option( 'cmtt_glossary_showSearch' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you like the "search" functionality to appear on the Glossary Index page.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Only show items on search?</th>
                        <td>
                            <input type="hidden" name="cmtt_showOnlyOnSearch" value="0" />
                            <input type="checkbox" name="cmtt_showOnlyOnSearch" <?php checked( true, get_option( 'cmtt_showOnlyOnSearch', false ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select this option if you want to display the glossary items only after search is used.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Use Fast-live-Filter?</th>
                        <td>
                            <input type="hidden" name="cmtt_indexFastFilter" value="0" />
                            <input type="checkbox" name="cmtt_indexFastFilter" <?php checked( true, get_option( 'cmtt_indexFastFilter', false ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select this option if you want to use the JS based fast filtering on the Glossary Index.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Category selection method:</th>
                        <td><select name="cmtt_glossaryCategoriesDisplayType">
                                <option value="0" <?php echo selected( '0', get_option( 'cmtt_glossaryCategoriesDisplayType' ) ); ?>>Dropdown</option>
                                <option value="1" <?php echo selected( '1', get_option( 'cmtt_glossaryCategoriesDisplayType' ) ); ?>>Links</option>
                            </select></td>
                        <td colspan="2" class="cmtt_field_help_container">Select the way how categories are displayed on the Glossary Index Page </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show only relevant categories?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_onlyRelevantCats" value="0" />
                            <input type="checkbox" name="cmtt_glossary_onlyRelevantCats" <?php checked( true, get_option( 'cmtt_glossary_onlyRelevantCats', '0' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">If this option is selected only the categories matching the currently displayed elements will be shown.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Disable all categories?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_disableAllCats" value="0" />
                            <input type="checkbox" name="cmtt_glossary_disableAllCats" <?php checked( true, get_option( 'cmtt_glossary_disableAllCats', '0' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">If this option is selected the "All Categories" option will be disabled.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Save the users last selection in the session?</th>
                        <td>
                            <input type="hidden" name="cmtt_index_sessionSave" value="0" />
                            <input type="checkbox" name="cmtt_index_sessionSave" <?php checked( true, get_option( 'cmtt_index_sessionSave', '1' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you like to remember the last user search/letter selection in the session.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Search only from:</th>
                        <td><select name="cmtt_glossarySearchFromOptions">
                                <option value="0" <?php echo selected( '0', get_option( 'cmtt_glossarySearchFromOptions' ) ); ?>>Title</option>
                                <option value="1" <?php echo selected( '1', get_option( 'cmtt_glossarySearchFromOptions' ) ); ?>>Description</option>
                                <option value="2" <?php echo selected( '2', get_option( 'cmtt_glossarySearchFromOptions' ) ); ?>>Both</option>
                            </select></td>
                        <td colspan="2" class="cmtt_field_help_container">Select rather to search only in the titles, only in the descriptions, or in both</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Search only for the exact term/phrase?</th>
                        <td>
                            <input type="hidden" name="cmtt_index_searchExact" value="0" />
                            <input type="checkbox" name="cmtt_index_searchExact" <?php checked( true, get_option( 'cmtt_index_searchExact', '0' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">If this option is selected the search will only look for exact term/phrase. And will not return the phrases containing it.</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Pagination</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Paginate Glossary Index page (items per page)</th>
                        <td><input type="text" name="cmtt_perPage" value="<?php echo get_option( 'cmtt_perPage' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">How many elements per page should be displayed (0 to disable pagination)</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Pagination type</th>
                        <td><select name="cmtt_glossaryServerSidePagination">
                                <option value="0" <?php echo selected( 0, get_option( 'cmtt_glossaryServerSidePagination' ) ); ?>>Client-side</option>
                                <option value="1" <?php echo selected( 1, get_option( 'cmtt_glossaryServerSidePagination' ) ); ?>>Server-side</option>
                            </select></td>
                        <td colspan="2" class="cmtt_field_help_container">Client-side: longer loading, fast page switch (with additional alphabetical index)<br />
                            Server-side: faster loading, slower page switch </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Pagination position (Server-side only)</th>
                        <td><select name="cmtt_glossaryPaginationPosition">
                                <option value="bottom" <?php echo selected( 'bottom', get_option( 'cmtt_glossaryPaginationPosition' ) ); ?>>Bottom</option>
                                <option value="top" <?php echo selected( 'top', get_option( 'cmtt_glossaryPaginationPosition' ) ); ?>>Top</option>
                                <option value="both" <?php echo selected( 'both', get_option( 'cmtt_glossaryPaginationPosition' ) ); ?>>Both</option>
                            </select></td>
                        <td colspan="2" class="cmtt_field_help_container">Choose where you would like the pagination to appear on the Index Page (only for the Server-side pagination). For the client side the pagination is always on the bottom. </td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Alphabetic index</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Display Alphabetical Index</th>
                        <td>
                            <input type="hidden" name="cmtt_index_enabled" value="0" />
                            <input type="checkbox" name="cmtt_index_enabled" <?php checked( true, get_option( 'cmtt_index_enabled', 1 ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">If you uncheck this option the alphabetical index will not be displayed on the Glossary Index Page</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Letters in alphabetic index</th>
                        <td><input type="text" class="cmtt_longtext" name="cmtt_index_letters" value="<?php echo esc_attr( implode( ',', get_option( 'cmtt_index_letters', array() ) ) ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Which letters should be shown in alphabetic index (separate by commas)</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Size of the letters in alphabetic index</th>
                        <td>
                            <select name="cmtt_indexLettersSize">
                                <option value="small" <?php selected( 'small', get_option( 'cmtt_indexLettersSize' ) ); ?>>Small</option>
                                <option value="medium" <?php selected( 'medium', get_option( 'cmtt_indexLettersSize' ) ); ?>>Medium</option>
                                <option value="large" <?php selected( 'large', get_option( 'cmtt_indexLettersSize' ) ); ?>>Large</option>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select the size of the letters in the alphabetic index: small(7pt), medium(10pt), large(14pt)</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show numeric [0-9] in alphabetic index?</th>
                        <td>
                            <input type="hidden" name="cmtt_index_includeNum" value="0" />
                            <input type="checkbox" name="cmtt_index_includeNum" <?php checked( true, get_option( 'cmtt_index_includeNum' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish to show [0-9] option in alphabetical index.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show all [ALL] in alphabetic index?</th>
                        <td>
                            <input type="hidden" name="cmtt_index_includeAll" value="0" />
                            <input type="checkbox" name="cmtt_index_includeAll" <?php checked( true, get_option( 'cmtt_index_includeAll' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish to show [All] option in alphabetical index.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show matching elements counts in alphabetic index?</th>
                        <td>
                            <input type="hidden" name="cmtt_index_showCounts" value="0" />
                            <input type="checkbox" name="cmtt_index_showCounts" <?php checked( true, get_option( 'cmtt_index_showCounts', '1' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show the number of elements matching each letter on hover.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show empty letters in alphabetic index?</th>
                        <td>
                            <input type="hidden" name="cmtt_index_showEmpty" value="0" />
                            <input type="checkbox" name="cmtt_index_showEmpty" <?php checked( true, get_option( 'cmtt_index_showEmpty' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish to display empty letters (they will be grayed out). Uncheck to hide.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">What letter should be preselected in alphabetic index?</th>
                        <td><input type="text" size="1" name="cmtt_index_initLetter" value="<?php echo get_option( 'cmtt_index_initLetter', '' ) ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">You can choose which letter should be preselected. e.g. &quot;b&quot;(without quotes) would mean "B" will be preselected each time user visits Glossary Index page. If you leave this field empty the leftmost item on the alphabetic index is selected.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Consider non-latin letters separate from their latin base?</th>
                        <td>
                            <input type="hidden" name="cmtt_index_nonLatinLetters" value="0" />
                            <input type="checkbox" name="cmtt_index_nonLatinLetters" <?php checked( true, get_option( 'cmtt_index_nonLatinLetters', '1' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">With this setting you can control how the non-latin letters used in many national character sets should be displayed on the Glossary Index alphabetical list. When this setting is unchecked the terms starting with: "A" and "Á" will be displayed for "A".</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">What locale should be used for sorting?</th>
                        <td><input type="text" size="4" name="cmtt_index_locale" value="<?php echo get_option( 'cmtt_index_locale', get_locale() ) ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container"> You can specify the locale which should be used for sorting the items on Glossary Index eg. 'de_DE', 'it_IT'. If left empty the locale of the Wordpress installation will be used.
                            <br/><i>Works only if the "intl" library is installed (see "Server Information" tab).</i></td>
                    </tr>
                </table>
            </div>
        </div>
        <div id="tabs-3">
            <div class="block">
                <h3>Glossary Term - Display</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Use custom template for terms?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryUseTemplate" value="0" />
                            <input type="checkbox" name="cmtt_glossaryUseTemplate" <?php checked( true, get_option( 'cmtt_glossaryUseTemplate' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">If you select this option then the plugin will search for the custom template for the glossary term page. <br/>
                            If you want to customize it, you can copy the file from: <br/>
                            <strong><?php echo CMTT_PLUGIN_DIR; ?>theme/Tooltip/single-glossary.php</strong> to <br/>
                            <strong><?php echo get_stylesheet_directory() ?>/Tooltip/single-glossary.php</strong> <br/>
                            (If the plugin doesn't find the template in your theme's folder it will use the default one)
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Choose the template for glossary term?</th>
                        <td>
                            <select name="cmtt_glossaryPageTermTemplate">
                                <?php
                                $selectedTemplate = get_option( 'cmtt_glossaryPageTermTemplate', 0 );
                                $templates        = CMTT_Custom_Templates::getPageTemplatesOptions();
                                ?>
                                <?php foreach ( $templates as $templateKey => $template ): ?>
                                    <option value="<?php echo $templateKey; ?>" <?php selected( $templateKey, $selectedTemplate ) ?>><?php echo $template; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Choose the page template of the current theme or set default.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show the sharing box on the Glossary Term Page?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryShowShareBoxTermPage" value="0" />
                            <input type="checkbox" name="cmtt_glossaryShowShareBoxTermPage" <?php checked( true, get_option( 'cmtt_glossaryShowShareBoxTermPage' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish to show the "Share This" box on the Glossary Index Page with links to Facebook, Twitter, Google+ and LinkedIn.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show back link on the top</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_addBackLink" value="0" />
                            <input type="checkbox" name="cmtt_glossary_addBackLink" <?php checked( true, get_option( 'cmtt_glossary_addBackLink' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show link back to Glossary Index from glossary term page</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show back link on the bottom</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_addBackLinkBottom" value="0" />
                            <input type="checkbox" name="cmtt_glossary_addBackLinkBottom" <?php checked( true, get_option( 'cmtt_glossary_addBackLinkBottom' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show link back to Glossary Index from glossary term page</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Remove comments from term page</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryRemoveCommentsTermPage" value="0" />
                            <input type="checkbox" name="cmtt_glossaryRemoveCommentsTermPage" <?php checked( true, get_option( 'cmtt_glossaryRemoveCommentsTermPage' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to remove the comments support form the term pages.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Display alphabetical list on top of Term Page?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTermShowListnav" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTermShowListnav" <?php checked( true, get_option( 'cmtt_glossaryTermShowListnav' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to display the alphabetical list on top of Glossary Term Page.</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Glossary Term - Links</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Remove link to the glossary term page?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTermLink" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTermLink" <?php checked( true, get_option( 'cmtt_glossaryTermLink' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you do not want to show links from posts or pages to the glossary term pages. This will only apply to Post / Pages and not to the Glossary Index page, for Glossary Index page please visit index page tab in settings. Keep in mind that the plugin use a <strong>&lt;span&gt;</strong> tag instead of a link tag and if you are using a custom CSS you should take this into account</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Open glossary term page in a new windows/tab?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryInNewPage" value="0" />
                            <input type="checkbox" name="cmtt_glossaryInNewPage" <?php checked( true, get_option( 'cmtt_glossaryInNewPage' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want glossary term page to open in a new window/tab.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show HTML "title" attribute for glossary links</th>
                        <td>
                            <input type="hidden" name="cmtt_showTitleAttribute" value="0" />
                            <input type="checkbox" name="cmtt_showTitleAttribute" <?php checked( true, get_option( 'cmtt_showTitleAttribute' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to use glossary name as HTML "title" for link</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Link underline</th>
                        <td>Style: <select name="cmtt_tooltipLinkUnderlineStyle">
                                <option value="none" <?php selected( 'none', get_option( 'cmtt_tooltipLinkUnderlineStyle' ) ); ?>>None</option>
                                <option value="solid" <?php selected( 'solid', get_option( 'cmtt_tooltipLinkUnderlineStyle' ) ); ?>>Solid</option>
                                <option value="dotted" <?php selected( 'dotted', get_option( 'cmtt_tooltipLinkUnderlineStyle' ) ); ?>>Dotted</option>
                                <option value="dashed" <?php selected( 'dashed', get_option( 'cmtt_tooltipLinkUnderlineStyle' ) ); ?>>Dashed</option>
                            </select><br />
                            Width: <input type="number" name="cmtt_tooltipLinkUnderlineWidth" value="<?php echo get_option( 'cmtt_tooltipLinkUnderlineWidth' ); ?>" step="1" min="0" max="10"/>px<br />
                            Color: <input type="text" class="colorpicker" name="cmtt_tooltipLinkUnderlineColor" value="<?php echo get_option( 'cmtt_tooltipLinkUnderlineColor' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Set style of glossary link underline</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Link underline (hover)</th>
                        <td>Style: <select name="cmtt_tooltipLinkHoverUnderlineStyle">
                                <option value="none" <?php selected( 'none', get_option( 'cmtt_tooltipLinkHoverUnderlineStyle' ) ); ?>>None</option>
                                <option value="solid" <?php selected( 'solid', get_option( 'cmtt_tooltipLinkHoverUnderlineStyle' ) ); ?>>Solid</option>
                                <option value="dotted" <?php selected( 'dotted', get_option( 'cmtt_tooltipLinkHoverUnderlineStyle' ) ); ?>>Dotted</option>
                                <option value="dashed" <?php selected( 'dashed', get_option( 'cmtt_tooltipLinkHoverUnderlineStyle' ) ); ?>>Dashed</option>
                            </select><br />
                            Width: <input type="number" name="cmtt_tooltipLinkHoverUnderlineWidth" value="<?php echo get_option( 'cmtt_tooltipLinkHoverUnderlineWidth' ); ?>" step="1" min="0" max="10"/>px<br />
                            Color: <input type="text" class="colorpicker" name="cmtt_tooltipLinkHoverUnderlineColor" value="<?php echo get_option( 'cmtt_tooltipLinkHoverUnderlineColor' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Set style of glossary link underline on mouse hover</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Link text color</th>
                        <td><input type="text" class="colorpicker" name="cmtt_tooltipLinkColor" value="<?php echo get_option( 'cmtt_tooltipLinkColor' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Set color of glossary link text color</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Link text color (hover)</th>
                        <td><input type="text" class="colorpicker" name="cmtt_tooltipLinkHoverColor" value="<?php echo get_option( 'cmtt_tooltipLinkHoverColor' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Set color of glossary link text color on mouse hover</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Glossary Term - Related Articles &amp; Terms</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Index rebuild interval:</th>
                        <td>
                            <select name="cmtt_glossary_relatedCronInterval" >
                                <?php
                                $types            = wp_get_schedules();
                                $selectedInterval = get_option( 'cmtt_glossary_relatedCronInterval', 'daily' );
                                ?>
                                <option value="none" <?php selected( 'none', $selectedInterval ) ?>><?php _e( 'Never', 'cm-tooltip-glossary' ) ?></option>
                                <?php foreach ( $types as $typeName => $type ): ?>
                                    <option value="<?php echo $typeName; ?>" <?php selected( $typeName, $selectedInterval ) ?>><?php echo $type[ 'display' ]; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Choose how often the related articles index is being rebuilt.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Index rebuild hour:</th>
                        <td><input type="time" placeholder="00:00" size="5" name="cmtt_glossary_relatedCronHour" value="<?php echo get_option( 'cmtt_glossary_relatedCronHour' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">Choose the hour when the Related Articles Rebuild should take place. The hour should be properly formatted string eg. 23:00 or 1 AM</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show related articles</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_showRelatedArticles" value="0" />
                            <input type="checkbox" name="cmtt_glossary_showRelatedArticles" <?php checked( true, get_option( 'cmtt_glossary_showRelatedArticles' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show list of related articles (posts, pages) on glossary term description page</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show custom related articles</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_showCustomRelatedArticles" value="0" />
                            <input type="checkbox" name="cmtt_glossary_showCustomRelatedArticles" <?php checked( true, get_option( 'cmtt_glossary_showCustomRelatedArticles', true ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show list of custom related articles</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Order of the related articles by:</th>
                        <td>
                            <select name="cmtt_glossary_relatedArticlesOrder">
                                <option value="menu_order" <?php selected( 'menu_order', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Menu Order</option>
                                <option value="post_title" <?php selected( 'post_title', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Post Title</option>
                                <option value="post_date DESC" <?php selected( 'post_date DESC', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Publising Date DESC</option>
                                <option value="post_date ASC" <?php selected( 'post_date ASC', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Publising Date ASC</option>
                                <option value="post_modified DESC" <?php selected( 'post_modified DESC', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Last Modified DESC</option>
                                <option value="post_modified ASC" <?php selected( 'post_modified ASC', get_option( 'cmtt_glossary_relatedArticlesOrder' ) ); ?>>Last Modified ASC</option>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">How the related articles should be ordered?</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Disable related terms on glossary term pages:</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryDisableRelatedTermsForTerms" value="0" />
                            <input type="checkbox" name="cmtt_glossaryDisableRelatedTermsForTerms" <?php checked( true, get_option( 'cmtt_glossaryDisableRelatedTermsForTerms' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you don't want to show list of related terms on glossary term pages</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show related glossary terms in a separate list</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_showRelatedArticlesMerged" value="0" />
                            <input type="checkbox" name="cmtt_glossary_showRelatedArticlesMerged" <?php checked( true, get_option( 'cmtt_glossary_showRelatedArticlesMerged' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show list of related glossary terms in the separate list.
                            If this option is not checked, the list of related articles and glossary terms will be the same list.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Number of related articles:</th>
                        <td><input type="number" name="cmtt_glossary_showRelatedArticlesCount" value="<?php echo get_option( 'cmtt_glossary_showRelatedArticlesCount' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">How many related articles should be shown?</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Number of related glossary terms:</th>
                        <td><input type="number" name="cmtt_glossary_showRelatedArticlesGlossaryCount" value="<?php echo get_option( 'cmtt_glossary_showRelatedArticlesGlossaryCount' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">How many related glossary terms should be shown? Works only if "Show related articles and glossary terms together" is enabled</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Post types to index:</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_showRelatedArticlesPostTypesArr" value="" />
                            <select multiple name="cmtt_glossary_showRelatedArticlesPostTypesArr[]" >
                                <?php
                                $types = get_option( 'cmtt_glossary_showRelatedArticlesPostTypesArr' );
                                foreach ( get_post_types() as $type ):
                                    ?>
                                    <option value="<?php echo $type; ?>" <?php if ( is_array( $types ) && in_array( $type, $types ) ) echo 'selected'; ?>><?php echo $type; ?></option>
                                <?php endforeach; ?>
                            </select></td>
                        <td colspan="2" class="cmtt_field_help_container">Which post types should be indexed? (select more by holding down ctrl key)</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Related articles index rebuild chunk size:</th>
                        <td>
                            <input type="text" name="cmtt_glossary_relatedArticlesCrawlChunkSize" value="<?php echo esc_attr( get_option( 'cmtt_glossary_relatedArticlesCrawlChunkSize', 500 ) ); ?>"/>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Since rebuilding the Glossary Index requires a lot of resources, both memory and time.
                            It has to be done in chunks. The optimal size of the chunk depends on your server.
                            If after clicking the button page goes blank, try to make this value much smaller and try to rebuild it again.
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Refresh related articles index:</th>
                        <td>
                            <input type="submit" name="cmtt_glossaryRelatedRefresh" value="Rebuild Index!" class="button"/>
                            <br/>
                            <?php if ( CMTT_Related::showContinueButton() ) : ?>
                                <input type="submit" name="cmtt_glossaryRelatedRefreshContinue" value="Continue indexing" class="button"/>
                                <br/>
                            <?php endif; ?>
                            <span><?php echo CMTT_Related::getRemainingArticlesCount(); ?></span>
                            <span style="color:red;display:inline-block;"><?php echo CMTT_Related::getParsingProblems(); ?></span>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">The index for relations between articles (posts, pages) and glossary terms is being rebuilt on daily basis. Click this button if you want to do it manually (it may take a while)</td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Show linked glossary terms list under post/page?</th>
                        <td>
                            <input type="hidden" name="cmtt_showRelatedTermsList" value="0" />
                            <input type="checkbox" name="cmtt_showRelatedTermsList" <?php checked( true, get_option( 'cmtt_showRelatedTermsList' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show the widget containing a list of all glossary items found in the post/page</td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Open normal related articles in new tab?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_relatedArticlesNewTab" value="0" />
                            <input type="checkbox" name="cmtt_glossary_relatedArticlesNewTab" <?php checked( true, get_option( 'cmtt_glossary_relatedArticlesNewTab', '0' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to open related articles in new tab.</td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Open custom related articles in new tab?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_customRelatedArticlesNewTab" value="0" />
                            <input type="checkbox" name="cmtt_glossary_customRelatedArticlesNewTab" <?php checked( true, get_option( 'cmtt_glossary_customRelatedArticlesNewTab', '1' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to open the custom related articles in new tab.</td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Display the related article's excerpt?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_relatedShowExcerpt" value="0" />
                            <input type="checkbox" name="cmtt_glossary_relatedShowExcerpt" <?php checked( true, get_option( 'cmtt_glossary_relatedShowExcerpt', '1' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to display the excerpts of the related articles.</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Glossary Term - Synonyms</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Show synonyms list</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_addSynonyms" value="0" />
                            <input type="checkbox" name="cmtt_glossary_addSynonyms" <?php checked( true, get_option( 'cmtt_glossary_addSynonyms' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show list of synonyms of the term on glossary term description page</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show synonyms list in tooltip</th>
                        <td>
                            <input type="hidden" name="cmtt_glossary_addSynonymsTooltip" value="0" />
                            <input type="checkbox" name="cmtt_glossary_addSynonymsTooltip" <?php checked( true, get_option( 'cmtt_glossary_addSynonymsTooltip' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show the list of synonyms of the term tooltip</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show synonyms in Glossary Index Page</th>
                        <td>
                            <input type="hidden" name="cmtt_glossarySynonymsInIndex" value="0" />
                            <input type="checkbox" name="cmtt_glossarySynonymsInIndex" <?php checked( true, get_option( 'cmtt_glossarySynonymsInIndex' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show synonyms as terms in Glossary Index Page</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Glossary Term - Taxonomies</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Show categories on Glossary Term page?</th>
                        <td>
                            <input type="hidden" name="cmtt_term_show_taxonomy_glossary-categories" value="0" />
                            <input type="checkbox" name="cmtt_term_show_taxonomy_glossary-categories" <?php checked( true, get_option( 'cmtt_term_show_taxonomy_glossary-categories', false ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show list of categories of the term on glossary term description page</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Position of categories on Glossary Term page?</th>
                        <td>
                            <select name="cmtt_term_position_taxonomy_glossary-categories">
                                <option value="top" <?php selected( 'top', get_option( 'cmtt_term_position_taxonomy_glossary-categories', 'top' ) ); ?>>Top</option>
                                <option value="bottom" <?php selected( 'bottom', get_option( 'cmtt_term_position_taxonomy_glossary-categories', 'top' ) ); ?>>Bottom</option>
                            </select>
                            <br />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Set the position of the Categories displayed on the Glossary Term page.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Show tags on Glossary Term page?</th>
                        <td>
                            <input type="hidden" name="cmtt_term_show_taxonomy_glossary-tags" value="0" />
                            <input type="checkbox" name="cmtt_term_show_taxonomy_glossary-tags" <?php checked( true, get_option( 'cmtt_term_show_taxonomy_glossary-tags', get_option( 'cmtt_glossaryTermShowTags', false ) ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to show list of tags of the term on glossary term description page</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Position of tags on Glossary Term page?</th>
                        <td>
                            <select name="cmtt_term_position_taxonomy_glossary-tags">
                                <option value="top" <?php selected( 'top', get_option( 'cmtt_term_position_taxonomy_glossary-tags', 'top' ) ); ?>>Top</option>
                                <option value="bottom" <?php selected( 'bottom', get_option( 'cmtt_term_position_taxonomy_glossary-tags', 'top' ) ); ?>>Bottom</option>
                            </select>
                            <br />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Set the position of the Categories displayed on the Glossary Term page.</td>
                    </tr>
                </table>
            </div>

        </div>
        <div id="tabs-4">
            <div class="block">
                <h3>Tooltip - Content</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Show tooltip when the user hovers over the term?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTooltip" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTooltip" <?php checked( true, get_option( 'cmtt_glossaryTooltip' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you wish for the definition to show in a tooltip when the user hovers over the term.  The tooltip can be styled differently using the tooltip.css and tooltip.js files in the plugin folder.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Add term title to the tooltip content?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryAddTermTitle" value="0" />
                            <input type="checkbox" name="cmtt_glossaryAddTermTitle" <?php checked( true, get_option( 'cmtt_glossaryAddTermTitle' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want the term title to appear in the tooltip content.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Add term editlink to the tooltip content?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryAddTermEditlink" value="0" />
                            <input type="checkbox" name="cmtt_glossaryAddTermEditlink" <?php checked( true, get_option( 'cmtt_glossaryAddTermEditlink' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want the term editlink to appear in the tooltip content (only for logged in users with "edit_posts" capability).</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Strip the shortcodes?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTooltipStripShortcode" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTooltipStripShortcode" <?php checked( true, get_option( 'cmtt_glossaryTooltipStripShortcode' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to strip the shortcodes from the glossary page description/excerpt before showing the tooltip.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Limit tooltip length?</th>
                        <td><input type="text" name="cmtt_glossaryLimitTooltip" value="<?php echo get_option( 'cmtt_glossaryLimitTooltip' ); ?>"  /></td>
                        <td colspan="2" class="cmtt_field_help_container">
                            Select this option if you want to show only a limited number of characters (minimum is 30) and add "<?php echo get_option( 'cmtt_glossaryTermDetailsLink' ); ?>" at the end of the tooltip text.<br/>
                            <strong>The tooltip has to be clickable for users to be able to click this link.</strong>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Add term page link to the end of the tooltip content?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryAddTermPagelink" value="0" />
                            <input type="checkbox" name="cmtt_glossaryAddTermPagelink" <?php checked( true, get_option( 'cmtt_glossaryAddTermPagelink', false ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want the term page link to appear in the tooltip content.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Open term page link in new tab?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryTermPageLinkTargetBlank" value="0" />
                            <input type="checkbox" name="cmtt_glossaryTermPageLinkTargetBlank" <?php checked( true, get_option( 'cmtt_glossaryTermPageLinkTargetBlank', false ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want the term page link to be opened in new tab.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Symbol indicating the tooltip content has been limited</th>
                        <td><input type="text" name="cmtt_glossaryLimitTooltipSymbol" value="<?php echo get_option( 'cmtt_glossaryLimitTooltipSymbol', '(...)' ); ?>"  /></td>
                        <td colspan="2" class="cmtt_field_help_container">
                            This option allows you to change the symbol which will be displayed in place where the tooltip content has been cut when it reaches the tooltip length limit.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Remove all tooltip filters</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryNoFilters" value="0" />
                            <input type="checkbox" name="cmtt_glossaryNoFilters" <?php checked( true, get_option( 'cmtt_glossaryNoFilters', 0 ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to remove all tooltip content filters. Warning: This overrides the options below.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Clean tooltip text?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryFilterTooltip" value="0" />
                            <input type="checkbox" name="cmtt_glossaryFilterTooltip" <?php checked( true, get_option( 'cmtt_glossaryFilterTooltip' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to remove extra spaces and special characters from tooltip text.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Leave the &lt;a&gt; tags?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryFilterTooltipA" value="0" />
                            <input type="checkbox" name="cmtt_glossaryFilterTooltipA" <?php checked( true, get_option( 'cmtt_glossaryFilterTooltipA' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to preserve the html anchor tags in tooltip text.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Leave the &lt;img&gt; tags?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryFilterTooltipImg" value="0" />
                            <input type="checkbox" name="cmtt_glossaryFilterTooltipImg" <?php checked( true, get_option( 'cmtt_glossaryFilterTooltipImg' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to preserve the images in tooltip text.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Use term excerpt for hover?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryExcerptHover" value="0" />
                            <input type="checkbox" name="cmtt_glossaryExcerptHover" <?php checked( true, get_option( 'cmtt_glossaryExcerptHover' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to use the term excerpt (if it exists) as hover text.
                            <br/>NOTE: You have to manually create the excerpts for term pages using the "Excerpt" field.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Avoid parsing protected tags?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryProtectedTags" value="0" />
                            <input type="checkbox" name="cmtt_glossaryProtectedTags" <?php checked( true, get_option( 'cmtt_glossaryProtectedTags' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want to avoid using the glossary for the following tags: Script, A, H1, H2, H3, PRE, Object.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Terms case-sensitive?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryCaseSensitive" value="0" />
                            <input type="checkbox" name="cmtt_glossaryCaseSensitive" <?php checked( '1', get_option( 'cmtt_glossaryCaseSensitive' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select this option if you want glossary terms to be case-sensitive.</td>
                    </tr>
                </table>
            </div>
            <div class="block">
                <h3>Tooltip - Mobile Support & Activation</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row">Enable the mobile support?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryMobileSupport" value="0" />
                            <input type="checkbox" name="cmtt_glossaryMobileSupport" <?php checked( true, get_option( 'cmtt_glossaryMobileSupport' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">If this option is enabled then on the mobile devices a link to the term page will appear on the bottom of the tooltip.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Disable tooltips on mobile devices?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryMobileDisableTooltips" value="0" />
                            <input type="checkbox" name="cmtt_glossaryMobileDisableTooltips" <?php checked( true, get_option( 'cmtt_glossaryMobileDisableTooltips' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">If this option is enabled then on the mobile devices the tooltips will not appear.</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Display tooltips on click?</th>
                        <td>
                            <input type="hidden" name="cmtt_glossaryShowTooltipOnClick" value="0" />
                            <input type="checkbox" name="cmtt_glossaryShowTooltipOnClick" <?php checked( true, get_option( 'cmtt_glossaryShowTooltipOnClick', '0' ) ); ?> value="1" />
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">If this option is enabled then on the tooltips will be displayed only when term is clicked not on hover (default).</td>
                    </tr>
                </table>
            </div>

            <div class="block">
                <h3>Tooltip - Featured Images</h3>
                <table class="floated-form-table form-table">
                    <tr valign="top">
                        <th scope="row"> Show featured image in tooltip?</th>
                        <td>
                            <select name="cmtt_glossary_tooltip_featuredImageDisplay">
                                <option value="no" <?php selected( 'no', get_option( 'cmtt_glossary_tooltip_featuredImageDisplay' ) ); ?>>No</option>
                                <option value="above_content" <?php selected( 'above_content', get_option( 'cmtt_glossary_tooltip_featuredImageDisplay' ) ); ?>>Above content</option>
                                <option value="below_content" <?php selected( 'below_content', get_option( 'cmtt_glossary_tooltip_featuredImageDisplay' ) ); ?>>Below Content</option>
                                <option value="left_aligned" <?php selected( 'left_aligned', get_option( 'cmtt_glossary_tooltip_featuredImageDisplay' ) ); ?>>Left Aligned</option>
                                <option value="right_aligned" <?php selected( 'right_aligned', get_option( 'cmtt_glossary_tooltip_featuredImageDisplay' ) ); ?>>Right Aligned</option>
                            </select>
                        </td>
                        <td colspan="2" class="cmtt_field_help_container">Select the way you want the image to be displayed in the tooltip</td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Image width:</th>
                        <td><input type="text" name="cmtt_glossary_tooltip_imageWidth" value="<?php echo get_option( 'cmtt_glossary_tooltip_imageWidth' ); ?>" /></td>
                        <td colspan="2" class="cmtt_field_help_container">The image's width in the tooltip</td>
                    </tr>
                </table>
            </div>

            <?php
            $additionalTooltipTabContent = apply_filters( 'cmtt_settings_tooltip_tab_content_after', '' );
            echo $additionalTooltipTabContent;
            ?>
        </div>
    </div>
    <p class="submit" style="clear:left">
        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'cm-tooltip-glossary' ) ?>" name="cmtt_glossarySave" />
    </p>
</form>