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
                 <li id="[+checklist_name+]_[+value+]"><label class="selectit"><input type="checkbox" name="[+checklist_name+][]" id="box_[+checklist_name+]_[+value+]" [+checked+] value="[+value+]"> [+text+]</label></li>

<!-- template="page-level-options" -->
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[checkbox_slug]" id="[+slug_prefix+]_options_checkbox_slug" type="checkbox" [+checkbox_slug_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Checkbox Field</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to activate the Checkbox field.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Text Field</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[text_slug]" id="[+slug_prefix+]_options_text_slug" type="text" size="40" maxlength="80" value="[+text_slug+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter some text.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Select Field (Static)</strong>
  </td>
  <td>
    <select name="[+slug_prefix+]_options[static_select_slug]" id="[+slug_prefix+]_options_static_select_slug">
		<option [+option_0_selected+] value="0">&mdash; Select an option &mdash;</option>
		<option [+option_one_selected+] value="one">Option one</option>
		<option [+option_two_selected+] value="two">Option two</option>
    </select>
    <div class="mla-settings-help">&nbsp;&nbsp;Select one of the static options.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Select Field (Dynamic)</strong>
  </td>
  <td>
    <select name="[+slug_prefix+]_options[dynamic_select_slug]" id="[+slug_prefix+]_options_dynamic_select_slug">
[+dynamic_select_options+]
    </select>
    <div class="mla-settings-help">&nbsp;&nbsp;Select one of the dynamic options.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Text Area</strong>
  </td>
  <td>
    <textarea name="[+slug_prefix+]_options[textarea_slug]" id="[+slug_prefix+]_options_textarea_slug" rows="4" cols="80" >[+textarea_slug+]</textarea>
    <div class="mla-settings-help">&nbsp;&nbsp;Enter some data; one entry per line.<br />&nbsp;&nbsp;See Documentation tab for details.</div>
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
<div class="mla-display-settings-page" id="mla-display-settings-general-tab" style="width:600px">
<p>There are no General option settings in this version of the plugin.</p>
<p>You can find more information about using all of the features of this plugin in the Documentation tab on this page.</p>
<div class="mla-page-level-options-form" style="display:none">
	<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-general-options-form">
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
<div class="mla-page-level-tools-form" style="display:inline">
  <h3>Plugin Tool(s)</h3>
  <p>The plugin tools let you copy option values or term definitions from a source MLA site to one or more destination sites. Pick your source and destination sites, then configure and run one of the tools below.</p>
  <form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-general-tools-form">
    <table style="border: 1px solid; width: 100%">
      <tbody>
        <tr>
          <td valign="top" width="300px"><strong>Source Site</strong></td>
          <td valign="top">&nbsp;&nbsp;&nbsp;&nbsp; </td>
          <td valign="top"><strong>Destination Site(s)</strong></td>
        </tr>
        <tr>
          <td valign="top" width="300px">
            <select name="[+slug_prefix+]_tools[copy_options_source]" id="[+slug_prefix+]_tools_copy_options_source">
[+copy_options_source+]
            </select></td>
          <td valign="top">&nbsp;&nbsp;&nbsp;&nbsp; </td>
          <td valign="top"><div id="[+slug_prefix+]_tools_copy_options_destinations_div" class="categorydiv">
              <input type="hidden" name="[+slug_prefix+]_tools[copy_options_destinations][]" value="0">
              <ul class="cat-checklist attachment_categorychecklist form-no-clear" id="[+slug_prefix+]_tools_copy_options_destinations_ul" data-wp-lists="list:copy_options_destinations">
[+copy_options_destinations+]
              </ul>
            </div>
            <br /></td>
        </tr>
      </tbody>
    </table>
    <p>The "Copy Settings" tool lets you copy all the non-default option values from a source MLA site to one or more destination sites.</p>
    <table style="border: 1px solid; width: 100%">
      <tbody>
        <tr>
          <td valign="top" width="300px">
            <table>
              <tbody>
                <tr>
                  <td valign="top" class="textright"><input name="[+slug_prefix+]_tools[copy_defaults]" id="[+slug_prefix+]_tools_copy_defaults" type="checkbox" [+copy_defaults_checked+] value="1" /></td>
                  <td valign="top">&nbsp;<strong>Copy Defaults</strong></td>
                </tr>
              </tbody>
            </table>
          </td>
          <td valign="top">&nbsp;&nbsp;&nbsp;&nbsp; </td>
          <td valign="top"><span class="submit mla-settings-submit">
            <input name="[+slug_prefix+]_tools_copy_settings" class="button-primary" id="[+slug_prefix+]_tools_copy_settings" type="submit" value="Copy Settings" />
            </span>
            <div class="mla-settings-help">&nbsp;<br />
              Check the Copy Defaults box to copy <strong>ALL</strong> settings,<br />
              &nbsp;&nbsp;not just non-default settings.
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <p>The "Copy Terms" tool lets you copy term definitions from a source MLA site to one or more destination sites.</p>
    <table style="border: 1px solid; width: 100%">
      <tbody>
        <tr>
          <td valign="top" width="300px">
            <table>
              <tbody>
                <tr>
                  <td valign="top">&nbsp;<strong>Source Taxonomies</strong>
                    <div id="[+slug_prefix+]_tools_copy_terms_taxonomies_div" class="categorydiv">
                      <input type="hidden" name="[+slug_prefix+]_tools[copy_terms_taxonomies][]" value="0">
                      <ul class="cat-checklist attachment_categorychecklist form-no-clear" id="[+slug_prefix+]_tools_copy_terms_taxonomies_ul" data-wp-lists="list:copy_terms_taxonomies">
[+copy_terms_taxonomies+]
                      </ul>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table></td>
          <td valign="top">&nbsp;&nbsp;&nbsp;&nbsp;</td>
          <td valign="top"><span class="submit mla-settings-submit">
            <input name="[+slug_prefix+]_tools_copy_terms" class="button-primary" id="[+slug_prefix+]_tools_copy_terms" type="submit" value="&nbsp;&nbsp;Copy Terms&nbsp;&nbsp;" />
            </span>
            <div class="mla-settings-help">&nbsp;<br />
              Select one or more Source Site taxonomies<br />
              to copy term definitions from.</div>
            </td>
        </tr>
      </tbody>
    </table>
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
<li><a href="#general-tools"><strong>Plugin Tools</strong></a></li>
<li><a href="#shortcodes"><strong>Plugin Shortcodes</strong></a></li>
<li><a href="#api-functions"><strong>Plugin API Functions</strong></a></li>
<li><a href="#site-id-parameter"><strong>The "site_id=id[,id...]|all" parameter</strong></a></li>
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
The MLA Multisite Extensions example plugin adds three enhancements to the MLA core features:
</p>
<ul class="mla_settings">
<li>Adds the <code>site_id=id[,id...]|all</code> parameter to MLA shortcodes, combining items from one or more sites in your network in shortcode results.</li>
<li>Supports the <a href="https://github.com/bueltge/Multisite-Global-Media" target="_blank">Multisite Global Media</a> plugin, </li>
<li>Adds a Settings screen tool that allows copying MLA option settings from a source site to one or more destination sites.</li>
</ul>
<p>
The example plugin requires WP 4.6+ and uses the terminology of Network and Site (not Blog) introduced in the WP 4.6.0 update.
<br />&nbsp;
<a name="processing"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>How the Plugin Works</h3>
<p>
This section explains the implementation of each plugin feature. It's a bit technical but may help you understand why a feature works the way it does.
</p>
<h4>The <code>site_id</code> parameter</h4>
<p>
This parameter can be added to the <code>[mla_gallery]</code> shortcode to compose a gallery that draws items from multiple sites in your network. The parameter implementation uses six of the many "hooks" provided by the <code>[mla_gallery]</code> shortcode. Using those hooks, this plugin first queries each site's Media Library separately, then combines the results from all sites to sort the items apply pagination and compose the gallery display. The logic in each of the six hooks is explained here.
</p>
<p>
In the <code>mla_gallery_attributes</code> filter this plugin looks for the <code>site_id=</code> parameter and, if found, copies it to an internal shared variable and removes it from the shortcode parameters.
</p>
<p>
The <code>mla_gallery_query_arguments</code> filter is called for the original query and for each individual "multi_site_query" query. In the original query the filter:
</p>
<ol class="mla_settings">
<li>saves several of the taxonomy parameters for later use in the "multi_site_query" queries.</li>
<li>parses and validates the content of the <code>site_id=</code> parameter.</li>
<li>saves the pagination parameters for use after the "multi_site_query" queries are completed.</li>
<li>performs a separate "multi_site_query" query for each site named in the parameter, using the <code>switch_to_blog()</code> function.</li>
<li>saves the items returned from each query to an internal shared variable for later use.</li>
<li>replaces the original query with a short, simple query that returns no items since the real results have already been saved.</li>
</ol>
<p>
The <code>mla_gallery_wp_query_object</code> action restores the current site/blog setting after the "multi_site_query" queries are completed.
</p>
<p>
The <code>mla_gallery_the_attachments</code> filter combines all of the items from the "multi_site_query" queries, then sorts and paginates the commbined items to return the items composing the current gallery display. This filter also saves each item's site ID as a field-level substitution parameter value that can be accessed as <code>[+site_id+]</code>. This is the last step in the auery processing; the last two filters are called while compising the gallery display.
</p>
<p>
The <code>mla_gallery_item_initial_values</code> filter ensures that the correct site/blog is current for each item in the gallery display, switching from site to site as needed. This is required because composing the gallery item values relies on several WordPress functions that are sensitive to the current site value.
</p>
<p>
The <code>mla_gallery_item_values</code> filter tests the number of items remaining to be processed and when there are no more items left in the current gallery display, switches back to the original site/blog that was current before the shortcode was processed.
</p>
<h4>Multisite Global Media support</h4>
<p>
The <a href="https://github.com/bueltge/Multisite-Global-Media" target="_blank">Multisite Global Media</a> plugin adds a "Global Media" tab to the Media Manager Modal (popup) window (MMMW)  used, for example, by the Image and Gallery blocks and the Set Featured Image function. When the MLA enhancements are added to the MMMW window MLA must detect the Global Media queries and modify the MLA query correspondingly. This plugin uses two of MLA's many "hooks" to accomplish that.
</p>
<p>
First, in the <code>mla_media_modal_query_filtered_terms</code> action, this plugin detects the <code>global_meda</code> query parameter and switches to the global media site/blog. Once that's done the action sets the second hook to complete the work. After this first step, MLA performs the query using the correct (global media) site's Media Library.
</p>
<p>
Second, after MLA's query is finished, the <code>mla_media_modal_query_items</code> action is executed. There this plugin modifies the original query, replacing the query parameters with an <code>ids=</code> list of the item IDs returned by MLA's query. Finally, it restores the "current" blog and then passes control to the <code>wp_ajax_query-attachments</code> action to complete the query and return the results to the MMMW window.
</p>
<h4>The Copy Settings tool</h4>
<p>
The Copy Settings tool uses two MLA functions to copy non-default MLA option settings from a source site to one or more destination sites. The process has two steps: 1) switch to the source site/blog and build an array of all non-default settings for that site, and 2) switch to each destination site in turn and apply the settings array to that site.
</p>
<p>
The first step calls the <code>MLASettings::mla_get_export_settings()</code> function to build the array. Three WordPress "Attachment Display Settings" options ('image_default_align', 'image_default_link_type', 'image_default_size') are included because they can be set on the Settings/Media Library Assistant General tab.
</p>
<p>
For each option defined in the <code>MLACoreOptions::$mla_option_definitions</code> array the get export settings function calls the <code>MLACore::mla_get_option()</code> function to return any non-default setting for that option. If a non-default setting is found the option and its setting are added to the array returned by the function.
</p>
<p>
The second step calls the <code>MLASettings::mla_put_export_settings()</code> function for each site checked in the Setting Destinations list, passing the settings array from the first step as a parameter. That function calls the <code>MLACore::mla_update_option()</code> for each option in the array and tabulates how many options were changed and now many were unchanged.
</p>
<h4>The Copy Terms tool</h4>
<p>
Thanks to Bill Kneller (@Rhapsody348) for supplying the code that inspired this approach to copying term definitions.
</p>
<p>
The Copy Terms tool displays a list of all source site taxonomies that are supported by MLA (see Taxonomy Support in the Settings/Media Library Assistant General tab). One or more of these taxonomies can be selected to copy term definitions from the source site to one or more destination sites. There are some limitations of the tool to keep in mind:
</p>
<ol class="mla_settings">
<li>Each taxonomy must also be supported by MLA in the destination site. The tool will not create new taxonomies or add MLA support for taxonomies in the destination site.</li>
<li>The tool will never delete term definitions from a destination site. Deleting a term in the source site will not have any effect that term definition in any destination site.</li>
<li>The tool only copies term definitions. It does not process any term <strong>assignments</strong> to items in the source site or destination sites.</li>
<li>The tool uses the term slug to match terms from the source site to a destination site. The tool will fail in the unlikely event that two sites separately define a term with the same slug.</li>
</ol>
<p>
The Copy Terms tool uses a two-step process to copy term definitions from a source site to one or more destination sites: 1) switch to the source site/blog and build an array of all term definitions for the selected taxonomies in that site, and 2) switch to each destination site in turn and insert any missing terms in that site.
</p>
<p>
The first step calls the WordPress <code>get_terms()</code> function to build an array of term definitions for each selected taxonomy. For each term, an array element is added, indexed by taxonomy slug and term id. The element is an array containing the term name, slug, description and parent. The array is stored in a class property to be shared with the second step. Each taxonomy's array is sorted by term id so, in most cases, destination terms are created in the same order as source terms were.
</p>
<p>
The second step iterates through each taxonomy. First, it calls <code>MLACore::mla_taxonomy_support()</code> to verify that the taxonomy exists and is supported by MLA. Taxonomies that do not exist are simply ignored. For each supported taxonomy: 1) <code>get_terms()</code> is called to get a count of terms before processing, 2) for each term, this plugin's <code>_maybe_insert_destination_site_term()</code> (explained below) is called, and 3) <code>get_terms()</code> is called again to get an updated count of terms. The difference between the before and after counts is the number of terms insertred by the tool.
</p>
<p>
The <code>_maybe_insert_destination_site_term()</code> function has three steps. First, the function calls the WordPress <code>get_term_by()</code> function using the term slug to see if the term already exists. If it does, the function moves on to the next term. If the term does not exist, the second step ensures that the parent term(s) for a child term exist or are inserted before the child term is inserted. This is done by calling the same function recursively until a root-level term is reached. The third step inserts the term itself, specifying the term slug to preserve consistency between the source and destination taxonomies.
</p>
<p>
The key to this approach is the use of term slug to uniquely and correctly identify each term in both the source and destination taxonomies. The slug is used because the term id values will, in general, be different in the source and the destination taxonomies. There is a (very) small chance that this approach will fail if terms are manually created in a destination taxonomy. The tool works best when one site is used to define terms and the other sites only accept term definitions from that source site.
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
<br />&nbsp;
<a name="general-tools"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Plugin Tools</h3>
<p>
Each plugin tool operates by taking information from one Source Site, e.g., option settings or term definitions, and copying it to one or more Destination Sites. To use a tool: 1) select a source site in the dropdown control on the left, 2) check the box for one or more (or ALL) destination sites, 3) configure the tool-specific options and 4) click the tool's "Copy..." button to perform the operation. When the page refreshes a status message at the top of the page will summarize the results of the operation. The source and destination choices are retained in the dropdown and checklist controls. You can reset them by clicking on the "General" tab header.
<p>
You can check the "<strong>* - ALL Destination Sites</strong>" box to automatically copy the settings to all of the sites in your network. You can leave the other boxes unchecked; the ALL Destination Sites box will take precedence.
</p>
<h4>The Copy Settings tool</h4>
<p>
The "Copy Settings" tool lets you copy all the non-default option values from a source MLA site to one or more destination sites. The tool uses the MLA core code that implements the "Export ALL Settings"/"Import ALL Settings" features of the <a href="[+settingsURL+]?page=mla-settings-menu-general&amp;mla_tab=general#gotobottom" target="_blank">Settings/Media Library Assistant General tab</a>. By default, this code copies only non-default settings values so keep the following points in mind:
</p>
<ul class="mla_settings">
<li>Default values for each setting are compiled into the MLA code; they are not stored in the database. Any source site setting that has the default value will not be "exported" to the destination sites.</li>
<li>When the source site setting has the default value, any non-default settings in destination sites are preserved; they are not set back to the default value.</li>
</ul>
<p>
You can check the "Copy Defaults" box if you want to process ALL of the plugin settings, not just those with non-default values. This can be handy if you have copied a non-default setting in the past, then changed the setting back to its default value and want to copy that to other sites.
</p>
<h4>The Copy Terms tool</h4>
<p>
The Copy Terms tool copies term definitions from the source site to one or more destination sites. The tool displays a list of all source site taxonomies that are supported by MLA (see Taxonomy Support in the Settings/Media Library Assistant General tab). One or more of these taxonomies can be selected for the copy operation.
</p>
<p>
For each of the taxonomies you select, the tool compiles all the source site term definitions. It then goes to each destination site and ensures that all of the source terms exist there. The tool inserts any source term that does not already exist. The tool won't delete any existing destination term that isn't in the source terms, and the tool does not make or affect any term assignments to Media Library items. Be sure you read the details in the "How the Plugin Works" section so you understand the operation and limitations of this tool.
</p>
<p>
&nbsp;
<a name="shortcodes"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Plugin Shortcodes</h3>
<p>
The plugin supports five shortcodes to make its features available for use on posts and pages. Three shortcodes generate lists of sites and taxonomies to be added to HTML forms for a user interface to configure the tools. Two shortcodes provide access to the tools provided by the plugin. You can find a complete examlpe using all five shortcodes in the <a href="#examples"><strong>Examples</strong></a> section.
</p>
<h4>The <code>[mme_source_site_options]</code> shortcode</h4>
<p>
This shortcode returns HTML markup with <code>&lt;option></code> tags for each site in the network. When surrounded by a <code>&lt;select></code> tag it provides a dropdown control for selecting a single source site. For example:
</p>
<p>
<pre><code>&lt;select name="copy_options_source" id="mme_copy_options_source">
[mme_source_site_options]
&lt;/select></code></pre>
The shortcode parameters are:
</p>
<ul class="mla_settings">
<li><strong>current_site</strong> - Default '0'. ID value of the currently selected site.</li>
<li><strong>select_name</strong> - Default 'copy_options_source'. Name attribute of the surrounding select control.</li>
</ul>
<h4>The <code>[mme_destination_sites_items]</code> shortcode</h4>
<p>
This shortcode returns HTML markup with <code>&lt;li></code> tags for each site in the network. When surrounded by a <code>&lt;ul></code> tag it provides a list of checkbox items for selecting one or more destination sites. For example:
</p>
<p>
<pre><code>&lt;input type="hidden" name="copy_options_destinations[]" value="0">
&lt;ul class="cat-checklist form-no-clear" id="mme_copy_options_destinations_ul">
[mme_destination_sites_items]
&lt;/ul></code></pre>
The shortcode parameters are:
</p>
<ul class="mla_settings">
<li><strong>current_sites</strong> - Default ''. Comma-separated list of ID value(s) of the currently selected site(s). If it is empty, the <code>$_REQUEST</code> array will be checked for an element with the checklist_name to make the list "sticky".</li>
<li><strong>checklist_name</strong> - Default 'copy_options_destinations'. Name attribute of the li tags.</li>
</ul>
<h4>The <code>[mme_terms_source_taxonomies]</code> shortcode</h4>
<p>
This shortcode returns HTML markup with <code>&lt;li></code> tags for each taxonomy supported by MLA. When surrounded by a <code>&lt;ul></code> tag it provides a list of checkbox items for selecting one or more taxonomies from which to copy term definitions. For example:
</p>
<p>
<pre><code>&lt;input type="hidden" name="copy_terms_taxonomies[]" value="0">
&lt;ul class="cat-checklist attachment_categorychecklist form-no-clear" id="mme_copy_terms_taxonomies_ul">
[mme_terms_source_taxonomies]
&lt;/ul></code></pre>
The shortcode parameters are:
</p>
<ul class="mla_settings">
<li><strong>current_taxonomies</strong> - Default ''. Comma-separated list of ID value(s) of the currently selected site(s). If it is empty, the <code>$_REQUEST</code> array will be checked for an element with the checklist_name to make the list "sticky".</li>
<li><strong>checklist_name</strong> - Default 'copy_terms_taxonomies'. Name attribute of the li tags.</li>
</ul>
<h4>The <code>[mme_copy_settings]</code> shortcode</h4>
<p>
</p>
<p>
<pre><code>[mme_copy_settings]
source_site="{+template:({+request:copy_options_source+})+}"
destination_sites="{+template:({+request:copy_options_destinations+})+}"
copy_defaults="{+template:({+request:copy_defaults+}|false)+}"
[/mme_copy_settings]</code></pre>
The shortcode parameters are:
</p>
<ul class="mla_settings">
<li><strong>enforce_capability</strong> - Default 'true'. This shortcode will perform a WordPress "current_user_can" test before starting its work. This means the shortcode will only work for a logged-in user with the appropriate caopability. To bypass this security check, add the <code>enforce_capability=false</code> parameter.</li>
<li><strong>action</strong> - Default 'copy_settings'. This shortcode is often with an HTML form that populates its parameters and a "submit" button that runs the shortcode after parameters are selected (see, for example, the Examples section). If this parameter is not empty the shortcode will only run if the <code>$_REQUEST</code> array has an element with a matching key. To disable this check, add the <code>action=''</code> parameter.</li>
<li><strong>no_action_text</strong> - Default ''. If not empty, the shortcode will return this string when the <code>$_REQUEST</code> array does not have an element with the action value as the key.</li>
<li><strong>source_site</strong> - Default '0'. When not empty and not '0', this is the ID of the site from which to retrieve settings for the copy operation.</li>
<li><strong>no_source_text</strong> - Default ''. If not empty, the shortcode will return this string when the <code>source_site</code> is empty or '0'.</li>
<li><strong>destination_sites</strong> - Default ''. Comma-separated list of ID value(s) of the sites to which the settings are to be copied.</li>
<li><strong>copy_defaults</strong> - Default 'false'. The shortcode does not copy any settings set to their default value. To unconditionally copy all settings, including default values, add the <code>copy_defaults=true</code> parameter.</li>
</ul>
<h4>The <code>[mme_copy_terms]</code> shortcode</h4>
<p>
</p>
<p>
<pre><code>[mme_copy_terms]
source_site="{+template:({+request:copy_options_source+})+}"
destination_sites="{+template:({+request:copy_options_destinations+})+}"
taxonomy="{+template:({+request:copy_terms_taxonomies+})+}"
[/mme_copy_terms]</code></pre>
The shortcode parameters are:
</p>
<ul class="mla_settings">
<li><strong>enforce_capability</strong> - Default 'true'. This shortcode will perform a WordPress "current_user_can" test before starting its work. This means the shortcode will only work for a logged-in user with the appropriate caopability. To bypass this security check, add the <code>enforce_capability=false</code> parameter.</li>
<li><strong>action</strong> - Default 'copy_terms'. This shortcode is often with an HTML form that populates its parameters and a "submit" button that runs the shortcode after parameters are selected (see, for example, the Examples section). If this parameter is not empty the shortcode will only run if the <code>$_REQUEST</code> array has an element with a matching key. To disable this check, add the <code>action=''</code> parameter.</li>
<li><strong>no_action_text</strong> - Default ''. If not empty, the shortcode will return this string when the <code>$_REQUEST</code> array does not have an element with the action value as the key.</li>
<li><strong>source_site</strong> - Default '0'. When not empty and not '0', this is the ID of the site from which to retrieve term definitions for the copy operation.</li>
<li><strong>no_source_text</strong> - Default ''. If not empty, the shortcode will return this string when the <code>source_site</code> is empty or '0'.</li>
<li><strong>destination_sites</strong> - Default ''. Comma-separated list of ID value(s) of the sites to which the term definitions are to be copied.</li>
<li><strong>taxonomy</strong> - Default ''. Comma-separated list of slug value(s) (e.g., attachment_category, attachment_tag) of the taxonomies for which the term definitions are to be copied. Only taxonomies with the "Support" box checked in the MLA General tab "Taxonomy Support" section (of the source site) will be acted on.</li>
<li><strong>no_taxonomy_text</strong> - Default ''. If not empty, the shortcode will return this string when the <code>taxonomy</code> parameter is empty.</li>
</ul>
<p>
&nbsp;
<a name="api-functions"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Plugin API Functions</h3>
<p>
The plugin supports five API functions to make its features available for use in application code. Three functions generate lists of sites and taxonomies to be added to HTML forms for sonfiguring the tools. Two functions provide access to the tools provided by the plugin. The calling program is responsible for ensuring that all parameters contain valid values. You can inspect the corresponding shortcode functions to see that validations they perform before calling these functions.
</p>
<h4>The <code>mme_source_site_options()</code> function</h4>
<p>
<pre><code>/**
 * Compose HTML markup for Source Site select field options
 *
 * @since 1.13
 *
 * @param	string	$current_value Optional current selected value, default ''
 *
 * @return	string	HTML markup for the select field options
 */</code></pre>
MLAMultisiteExtensions::mme_source_site_options( $current_value = '' )
</p>
<h4>The <code>mme_destination_sites_items()</code> function</h4>
<p>
<pre><code>/**
 * Compose HTML markup for Destination Sites checklist items
 *
 * @since 1.13
 *
 * @param	array	$current_value Optional current selected values, default array()
 * @param	string	$checklist_name Optional HTML name attribute element, default 'copy_options_destinations'
 *
 * @return	string	HTML markup for the select field options
 */</code></pre>
MLAMultisiteExtensions::mme_destination_sites_items( $current_value = array(), $checklist_name = 'copy_options_destinations' )
</p>
<h4>The <code>mme_terms_source_taxonomies()</code> function</h4>
<p>
<pre><code>/**
 * Compose HTML markup for copy terms taxonomies items
	 *
 * @since 1.13
 *
 * @param	string	Optional current selected values, default array()
 * @param	string	Optional HTML name attribute element, default 'copy_terms_taxonomies'
 *
 * @return	string	HTML markup for the select field options
 */</code></pre>
MLAMultisiteExtensions::mme_terms_source_taxonomies( $current_value = array(), $checklist_name = 'copy_terms_taxonomies' )
</p>
<h4>The <code>mme_copy_settings_action()</code> function</h4>
<p>
<pre><code>/**
 * Copy non-default (or ALL) MLA option settings from a source site to one or more destination sites.
 *
 * @since 1.10
 *
 * @param	string	$options_source Source site
 * @param	array	$options_destinations Destination site(s)
 * @param	boolean	$copy_defaults Optional, default false. True to copy ALL settings, not just non-defaults.
 *
 * @return	string	action-specific message(s), e.g., summary of results
 */</code></pre>
MLAMultisiteExtensions::mme_copy_settings_action( $options_source, $options_destinations, $copy_defaults = false )
</p>
<h4>The <code>mme_copy_terms()</code> function</h4>
<p>
<pre><code>/**
 * Copy non-default MLA options from a source site to one or more destination sites.
 *
 * @since 1.12
 *
 * @param	string	$terms_source Source site
 * @param	array	$terms_destinations Destination site(s)
 * @param	array	$terms_taxonomies Source Site taxonomies
 *
 * @return	string	action-specific message(s), e.g., summary of results
 */</code></pre>
MLAMultisiteExtensions::mme_copy_terms_action( $terms_source, $terms_destinations, $terms_taxonomies )
</p>
<p>
&nbsp;
<a name="site-id-parameter"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>The "site_id=id[,id...]|all" parameter</h3>
<p>
This plugin adds a <code>site_id=id[,id...]|all</code> parameter to the <code>[mla_gallery]</code> shortcode so you can compose a gallery that includes items from any combination of the sites in your network. WordPress assigns the value one (1) to the initial site and numbers the child sites from two (2) upwards. You can find the id values (preceding the site names) in the controls of the General tab's Copy Settings tool. You can combine this parameter with any of the other shortcode parameters, e.g., taxonomy or keyword parameters, to filter the gallery results.
</p>
<p>
In a multisite environment the <code>attachment_ID</code> values are only unique within a given site, not globally unique across all sites. This plugin saves each item's site ID as a field-level substitution parameter value that can be accessed as <code>[+site_id+]</code>. During the gallery formatting process MLA makes calls to several WordPress functions to get elements like the thumbnail image. All of these calls assume that the item ID can be used to look up information in the current site’s database tables. MLA uses the <code>switch_to_blog( site_id )</code> function to ensure that the right site is current as each item is formatted for gallery display.
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
Here are some examples of the <code>site_id=</code> shortcode parameter.
</p>
<ol class="mla_settings">
<li><strong>[mla_gallery parent=all site_id=1]</strong> - selects items from one site in the network.</li>
<li><strong>[mla_gallery parent=all site_id=1,2,3]</strong> - merges items from 3 sites in the network.</li>
<li><strong>[mla_gallery parent=all site_id=all]</strong> - merges items from all of the sites in the network.</li>
<li><strong>[mla_gallery parent=all site_id=0]</strong> - selects items from the "current" site.</li>
</ol>
<h4>Plugin shortcodes example</h4>
<p>
Here is an example that uses all five shortcodes made available by the plugin. It contains a simplified version of all of the controls in the Plugin Tool(s) section on the General tab. Below the CSS styles and HTML markup are some notes highlighting the important parts of the example.
</p>
<p>
<pre><code>&lt;style type="text/css">
ul.cat-checklist {
list-style: none;
max-width: fit-content;
height: 10em;
border: 1px solid #ddd;
margin: 0 0 5px;
padding: 0.2em 5px;
overflow-y: scroll;
}
&lt;/style>
&lt;form action="." method="post" class="mla-display-settings-page" id="mme-tools-form">
&lt;h4>Source Site&lt;/h4>
&lt;p>
&lt;select name="copy_options_source" id="mme_copy_options_source">
[mme_source_site_options]
&lt;/select>
&lt;/p>
&lt;h4>Destination Site(s)&lt;/h4>
&lt;input type="hidden" name="copy_options_destinations[]" value="0">
&lt;ul class="cat-checklist" id="mme_copy_options_destinations_ul">
[mme_destination_sites_items]
&lt;/ul>
&lt;h4>Copy Settings tool&lt;/h4>
&lt;p>
&lt;input name="copy_defaults" id="mme_copy_defaults" type="checkbox" value="true">
&nbsp;Copy Defaults
&lt;/p>
&lt;p>
&lt;span class="submit mla-settings-submit">
&lt;input name="copy_settings" class="button-primary" id="mme_copy_settings" type="submit" value="Copy Settings">
&lt;/span>
&lt;/p>
&lt;h4>Copy Terms tool&lt;/h4>
&lt;input type="hidden" name="copy_terms_taxonomies[]" value="0">
&lt;ul class="cat-checklist" id="mme_copy_terms_taxonomies_ul">
[mme_terms_source_taxonomies]
&lt;/ul>
&lt;p>
&lt;span class="submit mla-settings-submit">
&lt;input name="copy_terms" class="button-primary" id="mme_copy_terms" type="submit" value="&nbsp;&nbsp;Copy Terms&nbsp;&nbsp;">
&lt;/span>
&lt;/p>
&lt;/form>
&lt;h3>Tool Results&lt;/h3>
[mme_copy_settings]
source_site="{+template:({+request:copy_options_source+})+}"
destination_sites="{+template:({+request:copy_options_destinations+})+}"
copy_defaults="{+template:({+request:copy_defaults+}|false)+}"
[/mme_copy_settings]
[mme_copy_terms]
source_site="{+template:({+request:copy_options_source+})+}"
destination_sites="{+template:({+request:copy_options_destinations+})+}"
taxonomy="{+template:({+request:copy_terms_taxonomies+})+}"
[/mme_copy_terms]</code></pre>
</p>
<p>
The example begins with a CSS style definition that gives the destination site and taxonomy checklists a familiar appearance, with a limited height and scroll bar. Next is an HTML form that contains controls for setting all of the values to be used by the tools. Most of the controls are generated by MME shortcodes, but there are a few conventional HTML elements as well. The <code>name=</code> attributes of the HTML tags must match the name parameters of the shortcodes, e.g., <code>&lt;select name="copy_options_source"</code> matches the (default) value of the <code>[mme_source_site_options select_name=...]</code> parameter. Note the <code>&lt;input type="hidden" ...</code> tags in the checklists. These ensure that the checklist array will be added to the <code>$_REQUEST</code> array even if none of the checklist boxes are checked.
</p>
<p>
The form includes two submit buttons, one for each of the tools. The <code>name=</code> attribute of each button matches the (default) <code>action=</code> value for the corresponding tool shortcode. This ensures that only one of the tool shortcodes will run, and only when the button is clicked.
</p>
<p>
The example concludes with the two tool shortcodes, using the "enclosing shortcode" syntax to spread the parameters over several lines for readability. The important part of the example here is how the results of the form become the parameter values for the tool shortcodes. For example, the source site is passed from the <code>&lt;select name="copy_options_source" ...</code> element as <code>source_site="{+template:({+request:copy_options_source+})+}"</code>. When the post/page is first displayed the <code>$_REQUEST</code> array is empty; the parentheses in the template handle this case, leaving the parameter value empty. In a similar way, the <code>copy_defaults="{+template:({+request:copy_defaults+}|false)+}"</code> template substitutes "false" for a missing value.
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
If you are not getting the results you expect from the plugin carefully inspecting the results of tool or shortcode processing can be a valuable exercise. You can activate some debug loging, run a test and inspect the log file for more information about what&rsquo;s going on.
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
[11-Apr-2023 18:14:24 UTC] 695 MLACore::mla_plugins_loaded_action() MLA 3.07 (20230403) mla_debug_level 0x8001<br />
[11-Apr-2023 18:14:24 UTC] 348 MLAMultisiteExtensions::_copy_options_action() MLAMultisiteExtensions_tools = array (
  'copy_options_source' => '1',<br />
  'copy_options_destinations' => <br />
  array (<br />
    0 => '0',<br />
    1 => '3',<br />
    2 => '4',<br />
  ),<br />
)<br />
[11-Apr-2023 18:14:24 UTC] 359 MLAMultisiteExtensions::_copy_options_action() source_settings = array (<br />
  'settings' => <br />
  array (<br />
    'image_default_link_type' => 'none',<br />
    'current_version' => '3.07',<br />
    ...
</blockquote>
<p>
Of course, the details will be different. If you discover a defect in the plugin (or in MLA) you can <a href="http://wordpress.org/support/plugin/media-library-assistant" target="_blank">open a support topic</a> or <a href="http://davidlingren.com/#two" target="_blank">contact me at my web site</a> so it can be investigated further. I may ask for a copy of the log file from your tests.
</p>
</div>