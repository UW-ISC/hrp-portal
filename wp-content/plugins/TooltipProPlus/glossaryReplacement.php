<?php

class CMTT_Glossary_Replacement
{
    protected static $filePath = '';
    protected static $cssPath = '';
    protected static $jsPath = '';

    /**
     * Adds the hooks
     */
    public static function init()
    {
        self::$filePath = plugin_dir_url(__FILE__);
        self::$cssPath = self::$filePath . 'assets/css/';
        self::$jsPath = self::$filePath . 'assets/js/';

        add_action('cmtt_save_options_after_on_save', array(__CLASS__, 'saveReplacement'));
        add_action('the_content', array(__CLASS__, 'doCustomReplacement'));

        add_action('wp_ajax_cmtt_add_replacement', array(__CLASS__, 'ajaxAddReplacement'));
        add_action('wp_ajax_cmtt_delete_replacement', array(__CLASS__, 'ajaxDeleteReplacement'));
        add_action('wp_ajax_cmtt_update_replacement', array(__CLASS__, 'ajaxUpdateReplacement'));
    }

    /**
     * Adds the replacements with AJAX
     */
    public static function ajaxAddReplacement()
    {
        $repl = get_option('cmtt_glossary_replacements', array());

        if( empty($repl) )
        {
            $repl = array();
        }

        $case = filter_input(INPUT_POST, 'replace_case');

        $r['from'] = filter_input(INPUT_POST, 'replace_from');
        $r['to'] = filter_input(INPUT_POST, 'replace_to');
        $r['case'] = !empty($case) ? 1 : 0;

        $repl[] = $r;

        update_option('cmtt_glossary_replacements', $repl);
        self::_outputReplacements($repl);
        die();
    }

    /**
     * Updates the replacements with AJAX
     */
    public static function ajaxUpdateReplacement()
    {
        $repl = get_option('cmtt_glossary_replacements', array());

        if( empty($repl) )
        {
            $repl = array();
        }

        $id = filter_input(INPUT_POST, 'replace_id');
        if( isset($repl[$id]) )
        {
            $r['from'] = filter_input(INPUT_POST, 'replace_from');
            $r['to'] = filter_input(INPUT_POST, 'replace_to');
            $r['case'] = filter_input(INPUT_POST, 'replace_case');

            $repl[$id] = $r;
        }

        update_option('cmtt_glossary_replacements', $repl);
        self::_outputReplacements($repl);
        die();
    }

    /**
     * Deletes the replacement with AJAX
     */
    public static function ajaxDeleteReplacement()
    {
        $repl = get_option('cmtt_glossary_replacements', array());
        unset($repl[$_POST['id']]);
        update_option('cmtt_glossary_replacements', $repl);
        self::_outputReplacements($repl);
        die();
    }

    /**
     * Outputs the replacements table
     * @param type $repl
     * @param bool $addRow
     */
    public static function _outputReplacements($repl, $addRow = false)
    {
        ?>
        <table class="form-table cmtt_replacements_list">
            <thead>
                <tr>
                    <th>From:</th>
                    <th>To:</th>
                    <th colspan="2">Case sensative?</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if( !empty($repl) && is_array($repl) )
                {
                    foreach($repl as $k => $r)
                    {
                        self::_outputReplacementRow($r, $k);
                    }
                }
                else
                {
                    echo '<tr><td colspan="3">' . __('No replacements. Please add using the form below.', 'cm-tooltip-glossary') . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
        <?php
        if( $addRow ) :
            ?>
            <div class="cmtt-glossary-replacement-add">
                <table class="form-table">
                    <?php echo CMTT_Glossary_Replacement::_outputAddingRow(); ?>
                </table>
            </div>
            <?php
        endif;
    }

    /**
     * Outputs the single replacement row
     * @param type $replacementRow
     * @param type $rowKey
     */
    public static function _outputAddingRow()
    {
        ?>
        <tr valign="top">
            <td style="width: 200px">
                <input type="text" placeholder="From" name="cmtt_glossary_from_new" value="" />
            </td>
            <td style="width: 200px">
                <input type="text" placeholder="To" name="cmtt_glossary_to_new" value="" />
            </td>
            <td style="width: 100px">
                <input type="checkbox" name="cmtt_glossary_case_new" value="1" />
            </td>
            <td>
                <input type="button" class="button-primary" value="Add another replacement row" id="cmtt-glossary-add-replacement-btn">
            </td>
        </tr>
        <?php
    }

    /**
     * Outputs the single replacement row
     * @param type $replacementRow
     * @param type $rowKey
     */
    public static function _outputReplacementRow($replacementRow = array(), $rowKey = '')
    {
        // VKost - changed the form name attributes to get all data in an array, for value updates
        $from = (isset($replacementRow['from'])) ? stripslashes($replacementRow['from']) : '';
        $to = (isset($replacementRow['to'])) ? stripslashes($replacementRow['to']) : '';
        $checked = (isset($replacementRow['case']) && $replacementRow['case'] == 1) ? 'checked' : '';
        ?>
        <tr valign="top">
            <td style="width: 200px">
                <input type="text" placeholder="From" name="cmtt_glossary_from[<?php echo $rowKey; ?>]" value="<?php echo $from; ?>" />
            </td>
            <td style="width: 200px">
                <input type="text" placeholder="To" name="cmtt_glossary_to[<?php echo $rowKey; ?>]" value="<?php echo $to ?>" />
            </td>
            <td style="width: 100px"><input type="checkbox" name="cmtt_glossary_case[<?php echo $rowKey; ?>]" value="1" <?php echo $checked ?> /></td>
            <td>
                <input type="button" value="delete" class="cmtt-glossary-delete-replacement" data-rid="<?php echo $rowKey ?>" />
                <input type="button" value="update" class="cmtt-glossary-update-replacement" data-uid="<?php echo $rowKey ?>" />
            </td>
        </tr>
        <?php
    }

    /**
     * Save the info about replaced terms
     */
    public static function saveReplacement($post)
    {
        /*
         * Added code to update replacements while updating other options
         */
        if( isset($post['cmtt_glossary_from']) && isset($post['cmtt_glossary_to']) && isset($post['cmtt_glossary_case']) )
        {
            if( is_array($post['cmtt_glossary_from']) && is_array($post['cmtt_glossary_to']) && is_array($post['cmtt_glossary_case']) )
            {
                $replacement_from = $post['cmtt_glossary_from'];
                $replacement_to = $post['cmtt_glossary_to'];
                $replacement_case = $post['cmtt_glossary_case'];
                $repl_array = array();
                foreach($replacement_from as $key => $value)
                {
                    if( $replacement_from[$key] != '' && $replacement_to[$key] != '' )
                    {
                        $repl_array[$key] = array(
                            'from' => $replacement_from[$key],
                            'to'   => $replacement_to[$key],
                            'case' => (isset($replacement_case[$key]) ? 1 : 0));
                    }
                }
                update_option('cmtt_glossary_replacements', $repl_array);
            }
        }
    }

    /**
     * Replaces the words within the text
     * @param type $content
     * @return type
     */
    public static function doCustomReplacement($content)
    {
        $repl = get_option('cmtt_glossary_replacements', array());
        if( !empty($repl) && is_array($repl) )
        {
            foreach($repl as $r)
            {
                if( !empty($r['from']) )
                {
                    $content = ($r['case'] == 1) ? str_replace($r['from'], $r['to'], $content) : str_ireplace($r['from'], $r['to'], $content);
                }
            }
        }
        return $content;
    }

}