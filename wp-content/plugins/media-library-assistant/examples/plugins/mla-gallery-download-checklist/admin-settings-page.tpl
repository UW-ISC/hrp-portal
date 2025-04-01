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
<li><a href="#download-checklist-shortcode"><strong>Download Checklist Shortcode</strong></a></li>
<li><a href="#examples"><strong>Examples</strong></a></li>
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
This plugin provides a way to create a ZIP archive of Media Library items from a list of item IDs, typically composed by checking one or more boxes in a form surrounding a gallery created by the <code>[mla_gallery]</code> shortcode. An application including this plugin has four elements:</p>
<ul class="mla-doc-toc-list">
<li>A custom markup template with checkbox elements for selecting items,</li>
<li>An HTML form in the post/page content,</li>
<li>An <code>[mla_gallery]</code> shortcode, using the markup template, to display the items,</li>
<li>An <code>[mla_download_checklist]</code> shortcode that supplies download parameters and a Submit button for the form.</li>
</ul>
<p>
The shortcode is described in the <a href="#download-checklist-shortcode"><strong>Download Checklist Shortcode</strong></a> section. The <a href="#examples"><strong>Examples</strong></a> section shows how the shortcode works with the markup template, gallery shortcode and form to complete the application.
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
The plugin operation has two parts. First, there is a shortcode that processes parameters which customize the Submit button display and the ZIP archive that will contain the downloaded items. Second, there is a WordPress "action" that processes an AJAX request to generate the archive.
</p>
<p>
The shortcode processing adds three or four hidden input elements to the HTML form surrounding it. The first element is an "action" identifier that WordPress will use to find this plugin's AJAX processor. Two additional elements give the ID of the post/page that includes the shortcode ans an index of the shortcode within the post/page. The index allows you to have two or more forms and shortcodes on the post/page if your application requires that. The optional fourth element lets you retain the archive file after the download operation is finished. Following the hidden elements the shortcode adds a Submit button to the form that, when clicked, starts the download process by submitting the form content to the WordPress AJAX handler.
</p>
<p>
The shortcode has a parameter that lets you suppress adding the form elements if the corresponding gallery display is empty, i.e., has no items. You can supply an optional "empty archive" text that replaces the elements. If you use the "empty archive" parameter the shortcode must contain all of the data selection parameters from the corresponding <code>[mla_gallery]</code> shortcode so this plugin can detect the empty condition. This is shown in the examples below. Note that using this option means the database query for selecting the gallery items is performed twice; once for the gallery display and a second time in this plugin to decide whether or not to add the Submit button.
</p>
<p>
Note that the added input elements do not include all of the information required by the AJAX action that processes form submission. The post/page ID and shortcode index allow the AJAX action to load the appropriate post/page content and find the shortcode instance within it. The other parameters required to process the request are then extracted from the shortcode text.
</p>
<p>
The AJAX action responds to the HTML form submission. The process starts by finding the array in the form payload that specifies the item IDs with which to compose the archive. If the array is not found, no boxes have been checked and no archive file will be returned to the client. If the array is present, the action uses PHP functions to create the archive, add the selected items to it and encode the archive file in an HTML download response.
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
<a name="download-checklist-shortcode"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Download Checklist Shortcode</h3>
<p>
The shortcode adds a few hidden elements and a Submit button to the HTML form in which it appears. The following parameters customize the button and subsequent AJAX processing.
</p>
<ul class="mla-doc-toc-list">
<li><strong>archive_name</strong> - Default: 'MLA-Checklist-Archive'. Provides a prefix for the filename of the ZIP archive, the <code>id</code> attributes of the form elements, and a substitute for empty <code>button_text</code> values.</li>
<li><strong>input_array_name</strong> - Default: 'mla-checklist-archive-items'. Must match the <code>name</code> attribute of the HTML checkbox elements in the enclosing form. The <code>name</code> attribute must end wih brackets to denote an array, e.g., <code>name="mla-checklist-archive-items[]"</code>, but do not add the brackets to this parameter.</li>
<li><strong>button_attributes</strong> - Default: empty. Additional attributes added to the front of the Submit button HTML tag.</li>
<li><strong>button_class</strong> - Default: empty. One or more values for the <code>class</code> attribute of the Submit button HTML tag. Do not add the attribute name to this parameter, e.g., use something like <code>button_class="class1 class2"</code>.</li>
<li><strong>button_text</strong> - Default: 'Download'. The content of the <code>value</code> attribute of the Submit button HTML tag. Supplies the text that will appear inside the button.</li>
<li><strong>allow_empty_gallery</strong> - Default: 'true'. Controls the display of the Submit button when the corresponding <code>[mla_gallery]</code> is empty, i.e., contains no items. If this parameter is 'false', the button is suppressed when the gallery is empty and the <code>empty_text</code> value is returned instead. See also the note just below.</li>
<li><strong>disposition</strong> - Default: 'delete'. Controls the retention of the ZIP file on the server after the download. If this parameter is 'keep', the file is retained; this can be useful for some debugging purposes. Any other value causes the file to be deleted from the server after the download is complete.</li>
<li><strong>empty_text</strong> - Default: empty. The text that replaces the HTML form Submit button when the corresponding <code>[mla_gallery]</code> is empty, i.e., contains no items.</li>
</ul>
<p>
<strong>Note:</strong> In addition to the above parameters, if you code <code>allow_empty_gallery=false</code>, you must add all of the data selection parameters used in the corresponding <code>[mla_gallery]</code> shortcode that generates the gallery with the checkboxes. If those parameters return no items, the Submit button is suppressed and the <code>empty_text</code> is returned instead. 
</p>
<p>
There are several examples below that clarify the shortcode parameters and the other parts of the application.
</p>
<p>
&nbsp;
<a name="examples"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Examples</h3>
<p>
As outlined in the Introduction, this plugin is part of an application that has four parts. Each of those parts is described in a subsection below.
</p>
<h4>Custom Markup Template</h4>
<p>
The <code>[mla_gallery]</code> shortcode uses templates for the CSS styles and HTML markup that control the look and content of the gasllery display. The <a href="[+settingsURL+]?page=mla-settings-menu-shortcodes&mla_tab=shortcodes" target="_blank">Shortcodes tab</a> in the Settngs/Media Library Assistant page lets you create custom style and markup templates to alter the display. To use this plugin you must define a custom markup template and add an HTML input tag to add a checkbox to each item in the display.
</p>
<p>
You can create a new template from scratch or copy and modify an existing template. To create a new custom markup template:
</p>
<ol>
	<li>Navigate to the Settings/Media Library Assistant Shortcodes tab.</li>
	<li>Click the "Add New Template" button to the left of the table views.</li>
	<li>Change the "select template type" dropdown control to "Markup".</li>
	<li>Change the "select template shortcode" dropdown control to "Gallery".</li>
	<li>Fill in the sections with the content for your application.</li>
	<li>Scroll to the bottom and click "Add Template".</li>
</ol>
<p>
For this example it's easier to copy and modify the default markup template, adding the checkbox to the markup in the "Item" section. Here is the default Item section with the added input tag, between the <code>[+captiontag_content+]</code> and <code>[+itemtag+]</code> tags.
</p>
<pre style="width: 800px; background-color: rgba(0, 0, 0, 0.07); color: rgb(60, 67, 74); font-family: Consolas, Monaco, monospace"> 
  <[+itemtag+] class='gallery-item [+last_in_row+]'>
	<[+icontag+] class='gallery-icon [+orientation+]'>
		[+link+]
	&lt;/[+icontag+]&gt;
	[+captiontag_content+]
        &lt;input type="checkbox" name="mla-checklist-archive-items[]" value="[+attachment_ID+]"&gt;
   &lt;/[+itemtag+]&gt;
</pre>
<p>
The <code>name</code> attribute value must match the value used in the <code>[mla_download_checklist]</code> shortcode; this example uses the default value. You must include the square brackets after the name to create an array of checked items. The <code>value</code> attribute must contain the ID of the item in the gallery, which the <code>[+attachment_ID+]</code> substitution parameter supplies. To copy and modify the default markup template:
</p>
<ol>
	<li>Navigate to the Settings/Media Library Assistant Shortcodes tab.</li>
	<li>Enter "default" in the Search Templates text box and click the button to the right of the box.</li>
	<li>Find the "default (default) Markup Gallery" table entry and hover over the template Name, then click the "Copy" rollover action below the name.</li>
	<li>Change the "default-copy" template name to something like "gallery-checklist" (no spaces in the name).</li>
	<li>Change the Description section, if you like, to document the purpose of this template.</li>
	<li>Find the Item section and add the item tag as shown above.</li>
	<li>Scroll to the bottom and click "Update".</li>
</ol>
<p>
Once that's done you are ready to use the new template in your <code>[mla_gallery]</code> shortcode.
</p>
<h4>HTML form</h4>
<p>
The centerpiece of the application is a form that displays a gallery of Media Library items with checkboxes for each item and a Submit button to perform the download of the selected items. The gallery display and Submit button are generated by shortcodes. Here is a simple example form.
</p>
<pre style="width: 800px; background-color: rgba(0, 0, 0, 0.07); color: rgb(60, 67, 74); font-family: Consolas, Monaco, monospace"> 
&lt;form action="http://mysite.com/wp-admin/admin-ajax.php" method="post" id="mla-checklist-download-form"&gt;
[mla_gallery]
attachment_category=abc
mla_markup=gallery-checklist
[/mla_gallery]
[mla_download_checklist]
attachment_category=abc
archive_name=selections
button_text="Download ALL Selections"
allow_empty_gallery=false
empty_text="No Selections"
[/mla_download_checklist]
&lt/form&gt;
</pre>
<p>
The form's <code>action="http://mysite.com/wp-admin/admin-ajax.php"</code> attribute directs form submission to the WorkPress AJAX handler. You must replace the "mysite.com" portion with the top-level URL for your site. The <code>id="mla-checklist-download-form"</code> attribute is optional; you can use any value you want or leave the attribute out. The form content is generated by the two shortcodes; each shortcode is described in a subsection below. The example uses the alternative "enclosing shortcode" syntax to put each parameter on a separate line for readability. If your application uses the alternative syntax for the <code>[mla_download_checklist]</code> shortcode it must also be used for the <code>[mla_gallery]</code> shortcode due to WordPress shortcode parsing limitations.
</p>
<p>
The <code>[mla_gallery]</code> shortcode can precede or follow the <code>[mla_download_checklist]</code> shortcode, but both of the shortcodes must be within the enclosing HTML form so the checkbox items are sent to the server when the Submit button is clicked.
</p>
<h4><code>[mla_gallery]</code> shortcode</h4>
<p>
For this example we are composing a gallery filtered by just one data selection parameter, <code>attachment_category=abc</code>. <strong>You must replace this parameter</strong> with the data selection parameters you are using in the <code>[mla_gallery]</code> that contains the checkbox elements. You can use as many data selection parameters as you need for your application, but if you use <code>allow_empty_gallery=false</code> in the <code>[mla_download_checklist]</code> shortcode make sure to add exactly the same parameters to both shortcodes in the form. The <code>mla_markup=gallery-checklist</code> parameter links the shortcode to the custom markup template created for this application, as described above.
</p>
<h4><code>[mla_download_checklist]</code> shortcode</h4>
<p>
For this example include <code>allow_empty_gallery=false</code> to substitute a text value ("No Selections") for the Submit button when the gallery is empty. To detect an empty gallery we add one data selection parameter, <code>attachment_category=abc</code>, to this shortcode. We are also changing the default archive name to "selections" and the text that appears inside the Submit button to "Download ALL Selections".
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
If you are not getting the results you expect from the plugin carefully inspecting the results of parsing the specification and generating the query can be a valuable exercise. You can add the <code>mla_debug=true</code> or <code>mla_debug=log</code> parameters to the <code>[mla_gallery]</code> shortcode, run a test and inspect the log file or the screen messages for more information about what's going on.
</p>
<p>
The <code>[mla_download_checklist]</code> shortcode added by this example plugin is very straightforward but the debug log entries might be helpful. For this shortcode adding &ldquo;0x8000&rdquo; to the MLA Reporting value will generate useful information.
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
<pre style="width: 800px; background-color: rgba(0, 0, 0, 0.07); color: rgb(60, 67, 74); font-family: Consolas, Monaco, monospace"> 
[22-Jan-2025 20:40:25 UTC] 726 MLACore::mla_plugins_loaded_action() MLA 3.25 mla_debug_level 0x8001
[22-Jan-2025 20:40:28 UTC] 391 MLAGalleryDownloadChecklist::mla_download_checklist_shortcode $attr = array (
  'attachment_category' => 'abc',
  'archive_name' => 'selections',
  'button_text' => 'Download ALL Selections',
  'allow_empty_gallery' => 'false',
  'empty_text' => 'No Selections',
  'button_attributes' => 'alt="alternate"',
  'button_class' => 'button class',
)
[22-Jan-2025 20:40:28 UTC] 392 MLAGalleryDownloadChecklist::mla_download_checklist_shortcode $content = ''
</pre>
<p>
Of course, the details will be different. If you discover a defect in the plugin (or in MLA) you can <a href="http://wordpress.org/support/plugin/media-library-assistant" target="_blank">open a support topic</a> or <a href="http://davidlingren.com/#two" target="_blank">contact me at my web site</a> so it can be investigated further. I may ask for a copy of the log file from your tests.
</p>
</div>