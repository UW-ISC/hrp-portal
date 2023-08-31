<!-- template="page" -->
<a name="backtotop"></a>
&nbsp;
<div class="wrap">
<h1 class="wp-heading-inline">[+plugin_title+] [+version+] Settings</h1>
[+messages+]
[+tablist+]
[+tab_content+]
</div><!-- wrap -->

<!-- template="tablist" -->
<h2 class="nav-tab-wrapper">
[+tablist+]
</h2>
<!-- template="tablist-item" -->
<a data-tab-id="[+data-tab-id+]" class="nav-tab [+nav-tab-active+]" href="?page=[+settings-page+]&amp;mla_tab=[+data-tab-id+]">[+title+]</a>

<!-- template="messages" -->
<div class="[+mla_messages_class+]">
<p>
[+messages+]
</p>
</div>

<!-- template="select-option" -->
		<option [+selected+] value="[+value+]">[+text+]</option>

<!-- template="checklist-item" -->
                 <li id="[+checklist_name+]_[+value+]"><label class="selectit"><input type="checkbox" name="[+slug_prefix+]_tools[[+checklist_name+]][]" id="box_[+checklist_name+]_[+value+]" [+checked+] value="[+value+]"> [+text+]</label></li>

<!-- template="page-level-options" -->
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[perform_mapping]" id="[+slug_prefix+]_options_perform_mapping" type="checkbox" [+perform_mapping_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Perform Mapping</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to run MLA's mapping rules after the new item is "uploaded".</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[duplicate_terms]" id="[+slug_prefix+]_options_duplicate_terms" type="checkbox" [+duplicate_terms_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Duplicate Terms</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to copy term assignments to the new item.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[duplicate_custom_fields]" id="[+slug_prefix+]_options_duplicate_custom_fields" type="checkbox" [+duplicate_custom_fields_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Duplicate Custom Fields</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to copy custom field values to the new item.</div>
  </td>
</tr>

<!-- template="general-tab" --> 
<style type='text/css'>
.mla-settings-help {
	font-size: 8pt;
	padding-bottom: 5px
}

.mla-page-level-options-form {
	margin-left: 0px;
	margin-top: 10px;
	padding-bottom: 10px;
	border-bottom:thin solid #888888;
}

span.submit.mla-settings-submit,
p.submit.mla-settings-submit {
	padding-bottom: 0px
}

ul.mla_settings { 
	list-style-type: disc;
	list-style-position: outside;
}

ul.mla_settings li { 
	margin-left: 2em;
}
</style>
<h2>Plugin Options</h2>
<p>In this tab you can set the options you need.</p>
<p>You can find more information about using the features of this plugin in the Documentation tab on this screen.</p>
<div class="mla-page-level-options-form" style="display:inline">
	<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-general-options-tab">
		<table class="optiontable">
		<tbody>
			[+options_list+]
		</tbody>
		</table>
		<span class="submit mla-settings-submit">
		<input name="[+slug_prefix+]_options_save" class="button-primary" id="[+slug_prefix+]_options_save" type="submit" value="Save Changes" />
		<input name="[+slug_prefix+]_options_reset" class="button-primary alignright" id="[+slug_prefix+]_options_reset" type="submit" value="Delete Settings, Restore Defaults" />
		</span>
		[+_wpnonce+]
	</form>
</div>
<div class="mla-page-level-tools-form" style="display:none">
<h3>Plugin Tool(s)</h3>
<p>Click a button below to perform some custion plugin action.</p>
	<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-general-tools-form">
	  <table>
	  <tbody>
        <tr>
          <td valign="top">
            <strong>Dropdown Example</strong>
      	  </td>
		  <td valign="top">
		  &nbsp;&nbsp;&nbsp;&nbsp;
      	  </td>
		  <td valign="top">
            <strong>Checklist Example</strong>
          </td>
        </tr>
	    <tr>
          <td valign="top">
            <select name="[+slug_prefix+]_tools[dropdown_example]" id="[+slug_prefix+]_tools_dropdown_example">
[+dropdown_example+]
            </select>
      	  </td>
		  <td valign="top">
		  &nbsp;&nbsp;&nbsp;&nbsp;
      	  </td>
		  <td valign="top">
            <div id="[+slug_prefix+]_tools_checklist_example_div" class="categorydiv">
		       <input type="hidden" name="[+slug_prefix+]_tools[checklist_example][]" value="0">
		       <ul class="cat-checklist attachment_categorychecklist form-no-clear" id="[+slug_prefix+]_tools_checklist_example_ul" data-wp-lists="list:checklist_example">
[+checklist_example+]
               </ul>
             </div><br />
           </td>
         </tr>
	   </tbody>
	</table>
		<span class="submit mla-settings-submit">
		<input name="[+slug_prefix+]_tools_custom_plugin_action" class="button-primary" id="[+slug_prefix+]_tools_custom_plugin_action" type="submit" value="Custom Plugin Action" />
		</span>
		[+_wpnonce+]
	</form>
</div>

<!-- template="documentation-tab" -->
<style type='text/css'>
.mla-doc-toc-list {
	list-style-position:inside;
	list-style:disc;
	line-height: 15px;
	padding-left: 20px
}

.mla-doc-hook-label {
	text-align: right;
	padding: 0 1em 2em 0;
	vertical-align: top;
	font-weight:bold
}

.mla-doc-hook-definition {
	vertical-align: top;
}

.mla-doc-table-label {
	text-align: right;
	padding-right: 10px;
	vertical-align: top;
	font-weight:bold
}

.mla-doc-table-sublabel {
	padding-right: 10px;
	vertical-align: top
}

.mla-doc-table-reverse {
	text-align: right;
	padding-right: 10px;
	vertical-align:top
}

.mla-doc-table-definition {
	vertical-align: top;
}

.mla-doc-bold-link {
	font-size:14px;
	font-weight:bold
}

ul.mla_settings { 
	list-style-type: disc;
	list-style-position: outside;
}

ul.mla_settings li { 
	margin-left: 2em;
}
</style>
<h2>Plugin Documentation. In this tab, jump to:</h2>
<div class="mla-display-settings-page" id="mla-display-settings-documentation-tab" style="width:700px">
<ul class="mla-doc-toc-list">
<li><a href="#introduction"><strong>Introduction</strong></a></li>
<li><a href="#processing"><strong>How the Plugin Works</strong></a></li>
<li><a href="#general-options"><strong>General Plugin Options</strong></a></li>
<li><a href="#debugging"><strong>Debugging and Troubleshooting</strong></a></li>
</ul>
<p>
&nbsp;
<a name="introduction"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Introduction</h3>
<p>
This is a straightforward plugin that performs one task. It adds a "Duplicate" element to the item rollover actions on the Media/Assistant submenu table. When you click the "Duplicate" action this plugin: 1) simulates uploading a fresh copy of the attached file, 2) runs the MLA mapping rules on the new file,3) copies the assigned taxonomy terms and 4) copies custom field values from the original item to the new item. You can omit any of steps 2), 3) and/or 4) by unchecking the corresponding box in the General tab.
</p>
<p>
You should read the next section carefully to fully understand the copy process. In particular, think about the interaction between the MLA mapping rule processing and the two "Duplicate..." options. Mapping rules are run first, and their results will be <strong>replaced</strong> by the "Duplicate..." processing that follows. Make sure the resulting term assignments and custom field values are what your application needs.
</p>
<p>
&nbsp;
<a name="processing"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>How the Plugin Works</h3>
<p>
When the plugin is initialized it hooks two of the filters provided by MLA:
</p>
<ul class="mla_settings">
<li><strong>mla_list_table_build_rollover_actions</strong> - the handler for this filter adds the Duplicate action, building a link with a URL that includes the item ID and <code>mla_admin_action="duplicate_item"</code>.</li>
<li><strong>mla_list_table_custom_single_action</strong> - the handler for this filter detects the "duplicate_item" action and calls a plugin function to perform the copy.</li>
</ul>
<p>
The <code>MLADuplicateItem::mdi_duplicate_item( $original_item_id )</code> function performs the task. The function is public, so you can call it from your own code if you like.
</p>
<p>
The function first find the full path and name of the original item's attached file. It then copies the file to a new, temporary file. The function calls a WordPress function, <code>wp_handle_sideload()</code> to move the file into the Media Library, adjusting the file name as necessary.
</p>
<p>
With the new file in place, the function copies relevant values from the original item and calls a WordPress function, <code>wp_insert_attachment()</code> to create the database entry in the Media Library. Note that if the original item was attached to a parent post/page, the new item will also be attached to that parent post/page. Then, the WordPress "atttachment_metadata" array is added to the item by calling <code>wp_generate_attachment_metadata()</code> and <code>wp_update_attachment_metadata()</code>. MLA's mapping rules are executed during this atep unless this plugin's "Perform Mapping" option is disabled.
</p>
<p>
If you have checked the "Duplicate Terms" option, terms assigned to the original item are copied to the new item. This process has three steps. First, the taxonomies that have the  "Support" option checked (in the Settings/Media Library Assistant General tab Taxonomy Support section) are retrieved. For each taxonomy, the next step retrieves the assigned terms using the WordPress <code>get_object_term_cache()</code> or <code>wp_get_object_terms()</code> functions. The final step extracts the term slugs and passes them to <code>wp_set_object_terms()</code> to assign the terms to the new item. Note that this process <strong>replaces</strong> all of the assigned terms, including terms assgined by an MLA mapping rule if you use one.
</p>
<p>
If you have checked the "Duplicate Custom Fields" option, custom field values from the original item are copied to the new item. This process uses an MLA function, <code>MLAQuery::mla_fetch_attachment_metadata( $original_item_id )</code>, to retrieve values from the original item. The MLA function filters out most of the "hidden" values added by WordPress and some other plugins; this may cause problems for your application. Hidden values for the Advanced Cstom Fields plugin are preserved. If this is a problem, open a support request and I will investigate. The original item's values are copied to the new item one by one using the WordPress <code>update_metadata()</code> function. Note that this process <strong>replaces</strong> any values created by an MLA mapping rule if you use one.
</p>
<p>
If you have unchecked/disabled the "Duplicate Custom Fields" option, two WordPress values are copied anyway: <code>_thumbnail_id</code> and <code>_wp_attachment_image_alt</code>. These are the item's "ALT Text" and "Featured Image" values.
</p>
<p>
&nbsp;
<a name="general-options"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>General Plugin Options</h3>
<p>
The plugin's three options are straightforward. As noted in the Introduction, think about the interaction between the MLA mapping rule processing and the two "Duplicate..." options. Mapping rules are run first, and their results will be <strong>replaced</strong> by the "Duplicate..." processing that follows. Make sure the resulting term assignments and custom field values are what your application needs.
</p>
<ul class="mla_settings">
<li><strong>Perform Mapping</strong> - run the MLA mapping rules after the item is created and before the two "Duplicate..." operations.</li>
<li><strong>Duplicate Terms</strong> - copy assigned terms from the original item to the new item, replacing any terms assigned by an MLA mapping rule.</li>
<li><strong>Duplicate Custom Fields</strong> - copy custom fields from the original item to the new item, replacing any values created by an MLA mapping rule.</li>
</ul>
<p>
&nbsp;
<a name="debugging"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Debugging and Troubleshooting</h3>
<p>
If you are not getting the results you expect from the plugin carefully inspecting the results of the process can be a valuable exercise. Adding &ldquo;0x8000&rdquo; to the MLA Reporting value will generate useful information.
</p>
<p>
To activate MLA&rsquo;s debug logging:
</p>
<ol>
<li>Navigate to the Settings/Media Library Assistant Debug tab.</li>
<li>Scroll down to the &ldquo;MLA Reporting&rdquo; text box and enter &ldquo;0x8001&rdquo;. This will turn on MLA debug logging for messages specific to this example plugin.</li>
<li>Click the Save Changes button to record your new setting.</li>
<li>Optionally, scroll to the bottom of the screen and click &ldquo;Reset&rdquo; to clear the error log. You may not want to do this depending on how you manage your error log.</li>
</ol>
<p>
Once that&rsquo;s done you can run a test. The debug log can be very detailed, so restricting the test as best you can will be very helpful. When you&rsquo;ve finished testing, go back to the Debug screen and:
</p>
<ol>
<li>Enter &ldquo;0&rdquo; in the MLA Reporting text box to turn debug logging off.</li>
<li>Click the Save Changes button to record your new setting.</li>
<li>Scroll to the bottom and click &ldquo;Download&rdquo; to get the log content in a text file.</li>
<li>Optionally, scroll to the bottom of the screen and click &ldquo;Reset&rdquo; to clear the error log.</li>
</ol>
<p>
There may be a lot of messages written to the log, so limit the amount of activity during the logging period. You should see messages in the log like these:
</p>
<blockquote> 
[21-Jul-2023 23:07:39 UTC] 709 MLACore::mla_plugins_loaded_action() MLA 3.09 (20230717) mla_debug_level 0x8001<br />
[21-Jul-2023 23:07:39 UTC] 285 MLADuplicateItem::mdi_duplicate_item( 9081 ) file_info = array (
  'name' => 'wwii1354.jpg',
  'type' => 'image/jpeg',
  'tmp_name' => 'C:\\WINDOWS\\TEMP/64bb0fbb89946-6TJQdW.tmp',
  'error' => 0,
  'size' => 96194,
)<br />
[21-Jul-2023 23:07:39 UTC] 291 MLADuplicateItem::mdi_duplicate_item( 9081 ) sideload results = array (
  'file' => 'D:\\mladev/wp-content/uploads/2023/07/wwii1354.jpg',
  'url' => 'http://l.mladev/wp-content/uploads/2023/07/wwii1354.jpg',
  'type' => 'image/jpeg',
)
</blockquote>
<p>
Of course, the details will be different. If you discover a defect in the plugin (or in MLA) you can <a href="http://wordpress.org/support/plugin/media-library-assistant" target="_blank">open a support topic</a> or <a href="http://davidlingren.com/#two" target="_blank">contact me at my web site</a> so it can be investigated further. I may ask for a copy of the log file from your tests.
</p>
</div>