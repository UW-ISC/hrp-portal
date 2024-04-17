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
    <input name="[+slug_prefix+]_options[replace_item_thumbnail]" id="[+slug_prefix+]_options_replace_item_thumbnail" type="checkbox" [+replace_item_thumbnail_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Replace Item Thumbnail</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to replace Media/Assistant item thumbnail image source with inline data.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[block_edit_media_page]" id="[+slug_prefix+]_options_block_edit_media_page" type="checkbox" [+block_edit_media_page_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Block Edit Media Page</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to block access to the Media/Edit Media page from the Media/Assistant item thumbnail image and rollover action.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[block_assistant_downloads]" id="[+slug_prefix+]_options_block_assistant_downloads" type="checkbox" [+block_assistant_downloads_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Block Media/Assistant Downloads</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to disable download actions in the Media/Assistant page.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[allow_admin_access]" id="[+slug_prefix+]_options_allow_admin_access" type="checkbox" [+allow_admin_access_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Add Media/Assistant Toggle File Acess bulk action</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to add the "Toggle MLF File Access" bulk action to the Media/Assistant page.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[add_toggle_access]" id="[+slug_prefix+]_options_add_toggle_access" type="checkbox" [+add_toggle_access_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Allow Administrator Access</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to allow access to blocked items for Administrators.</div>
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
<p>In this tab you can configure access, handling and display of items protected by the "Block Direct Access" feature of the <a href="https://wordpress.org/plugins/media-library-plus/" target="_blank">Media Library Folders</a> plugin.</p>
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
</ul>
<p>
<a name="introduction"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Introduction</h3>
<p>
The <a href="https://wordpress.org/plugins/media-library-plus/" target="_blank">Media Library Folders</a> plugin lets you create and manage file system directories within the WordPress Uploads directory for Media Library files. It also provides a "Block Direct Access" feature.The Block Direct Access feature allows an administrator to block viewing and download of selected media files. Files to be blocked are moved to a protected folder in the Media Library.
</p>
<p>
This example plugin lets you configure how files protected by Block Direct Access are handled on the Media/Assistant admin screen. Each of the plugin's options are described in the General Plugin Options section below.
</p>
<p>
<a name="processing"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>How the Plugin Works</h3>
<p>
This plugin uses seven of the many actions and filters (hooks) MLA provides to tailor the Media/Assistant admin screen for your application. The filters and their functions are:
</p>
<ul class="mla_settings">
<li><strong>mla_list_table_custom_bulk_action</strong> - Process the Media/Assistant "Toggle MLF File Access" bulk action.</li>
<li><strong>mla_list_table_get_bulk_actions</strong> - Add the Media/Assistant "Toggle MLF File Access" bulk action.</li>
<li><strong>mla_list_table_bulk_action_initial_request</strong> - Remove blocked items from the Media/Assistant "Download" bulk action.</li>
<li><strong>mla_list_table_build_rollover_actions</strong> - Disable the Media/Assistant "Download" and "Edit" rollover actions for blocked items.</li>
<li><strong>mla_list_table_build_inline_data</strong> - In the Quick Edit area, replace the protected item thumbnail image link with an inline copy of the image.</li>
<li><strong>mla_list_table_primary_column_link</strong> - Disable the Media/Assistant link to Media/Edit Media around the item thumbnail image.</li>
<li><strong>mla_list_table_primary_column_content</strong> - Replace the protected item thumbnail image link with an inline copy of the image.</li>
</ul>
<p>
<a name="general-options"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>General Plugin Options</h3>
<h4>Replace Item Thumbnail</h4>
<p>
The item thumbnail images that appear in the Media/Assistant submenu table primary column are suppressed by MLF Block Direct Access. This option replaces the blocked hyperlink with an inline copy of the image to restore the display. This is more attractive but the inline data can be bulky, i.e., it requires more memory, data transfer and processing time.
</p>
<h4>Block Edit Media Page</h4>
<p>
Clicking on the item thumbnail image or the "Edit" rollover action will take you from the Media/Assistant screen to the Media/Edit Media screen for the item. You can prevent this by selecting this option. Of course, the WordPress Media/Library admin screen will still allow access, but you can remove that screen from the Admin menu with the "Display Media/Library" MLA General tab option.
</p>
<h4>Block Media/Assistant Downloads</h4>
<p>
MLA has a "Download" rollover action for single items and a "Download" Bulk action to create a ZIP archive with multiple items. You can check this option to disable the rollover action and remove blocked items from the Bulk action ZIP archive.
</p>
<h4>Add Media/Assistant Toggle File Acess bulk action</h4>
<p>
The Media Library Folders/Folders & Files screen has a "Block/Unblock File Access" bulk action to toggle the procected state of one or more items. This option adds a similar feature to the Media/Assistant Bulk actions array.
</p>
<h4>Allow Administrator Access</h4>
<p>
The above options will be enforced for all WordPress user roles. You can use this option to bypass the above options and make blocked items fully accessible to users with the Administrator role. However, the Replace Item Thumbnail option is not affected by this option and will always be enforced.
</p>
</div>