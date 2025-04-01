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

<!-- template="page-level-options" -->
<tr valign="top">
  <td class="textright">&nbsp;
    
  </td>
  <td>&nbsp;
    
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
<p>There are no General option settings in this version of the plugin.</p>
<p>You can find more information about using all of the features of this plugin in the Documentation tab on this page.</p>
<div class="mla-page-level-options-form" style="display:none">
	<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="[+slug_prefix+]_options_general_tab">
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
This plugin adds MLA enhanced taxonomy elements to the Attachment Details area of the ACF Gallery field type. The ACF Gallery field type is included in the ACF Pro plugin version. This plugin does nothing unless the MLA and ACF Pro plugins are installed and active.
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
When the plugin is initialized it first checks that MLA and ACF Pro are active. If so, the plugin checks three MLA General option settings before doing anything. The "Enable Media Manager Enhancements" option must be checked, and one or both of the "Media Manager Checklist meta boxes" and "Media Manager Flat meta boxes" options must be checked. If all of these tests are passed the plugin proceeds by hooking five WordPress/MLA filters to do its work.
</p>
<p>
On the server side, plugin operation has two parts. First, three filters ('mla_media_modal_settings', 'mla_media_modal_strings', 'wp_enqueue_media') add settings, strings, CSS styles and scripts to admin-mode screens that contain the WordPress Media Manager Modal Window (MMMW). Second, when one item in the ACF Gallery field is selected (performing the 'acf/fields/gallery/get_attachment' AJAX asction), two filters ('get_media_item_args', 'attachment_fields_to_edit') add HTML markup for MLA-supported taxonomies to the bottom of the Attachment Details area of the Gallery field(s). The plugin adds this HTML to the 'acf-form-data' element if it exists or generates the 'acf-form-data' from scratch if it is not already present.
</p>
<p>
On the browser side, the plugin's JavaScript extends the ACF <code>acf.models.GalleryField</code> object and hooks the object's <code>onClickSelect</code> event. When the event is triggered, the plugin calls the original ACF handler, then hooks the <code>ajaxSuccess</code> event to wait for the selected item's data to come back from the server. When the AJAX operation is complete, the script calls the MLA <code>mlaModal.utility.hookCompatTaxonomies</code> function to connect MLA's "Click to toggle" handler to the supported taxonomy elements.
</p>
<p>
Once the MLA "Click to toggle" handler is in place, existing MLA scripts take over the tasks of filling the taxonomy element(s) and managing term assignment changes. The MLA "Media Manager auto-fill meta boxes" and "Media Manager auto-open meta boxes" options are also handled by the existing scripts.
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
There are no General option settings in this version of the plugin.
</p>
<p>
&nbsp;
<a name="debugging"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Debugging and Troubleshooting</h3>
<p>
If you are not getting the results you expect from the plugin carefully inspecting the results of each filter can be a valuable exercise. 
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
<pre style="width: 800px; background-color: rgba(0, 0, 0, 0.07); color: rgb(60, 67, 74); font-family: Consolas, Monaco, monospace"> 
[12-Mar-2025 17:41:04 UTC] 735 MLACore::mla_plugins_loaded_action() MLA 3.24 (20250310) mla_debug_level 0x8001
[12-Mar-2025 17:41:04 UTC] 242 MLAACFSupport::mla_media_view_settings_filter( 1108 ) settings = array (
... (standard WordPress settings)
  'mla_acf_settings' => 
  array (
    'placeholder' => 'Placeholder',
  ),
)
[12-Mar-2025 17:41:04 UTC] 264 MLAACFSupport::mla_media_view_strings_filter( 1108 ) strings = array (
... (standard WordPress strings)
  'mla_acf_strings' => 
  array (
    'placeholder' => 'Placeholder',
  ),
)
[12-Mar-2025 17:41:04 UTC] 296 MLAACFSupport::mla_wp_enqueue_media_action(
 http://site/wp-content/plugins/mla-acf-support/, 1.02.20250310,  )
[12-Mar-2025 18:38:56 UTC] 321 MLAACFSupport::mla_get_media_item_args_filter args = array (
  'errors' => NULL,
  'in_modal' => true,
  'send' => true,
)
[12-Mar-2025 18:38:56 UTC] 321 MLAACFSupport::mla_get_media_item_args_filter args = array (
  'errors' => NULL,
  'in_modal' => false,
  'send' => true,
)
[12-Mar-2025 18:38:56 UTC] 428 MLAACFSupport::mla_attachment_fields_to_edit_filter( 1083 ) adding
 to acf-form-data for taxonomies = array (
  0 => 'category',
  1 => 'post_tag',
  2 => 'attachment_category',
  3 => 'attachment_tag',
)
[12-Mar-2025 18:38:56 UTC] 469 MLAACFSupport::mla_attachment_fields_to_edit_filter form_fields = array (
  'acf-form-data' => 
  array (
    'label' => '',
    'input' => 'html',
    'html' => '	&lt;div id="acf-form-data" class="acf-hidden">
... (HTML markup for ACF and MLA form elements)
&lt;tr class="compat-field-acf-blank"><td>',
  ),
)
</pre>
<p>
Of course, the details will be different. If you discover a defect in the plugin (or in MLA) you can <a href="http://wordpress.org/support/plugin/media-library-assistant" target="_blank">open a support topic</a> or <a href="http://davidlingren.com/#two" target="_blank">contact me at my web site</a> so it can be investigated further. I may ask for a copy of the log file from your tests.
</p>
</div>