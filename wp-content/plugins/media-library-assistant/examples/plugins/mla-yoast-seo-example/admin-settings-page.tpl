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

<!-- template="page-level-options" -->
<tr valign="top">
  <td colspan=2>
<span style="font-weight:bold; font-size:16px;>">XML Sitemaps</span>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[enable_xml_sitemap]" id="[+slug_prefix+]_options_enable_xml_sitemap" type="checkbox" [+enable_xml_sitemap_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Enable XML Sitemaps</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to add image tags to the XML sitemap.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[always_use_full_size]" id="[+slug_prefix+]_options_always_use_full_size" type="checkbox" [+always_use_full_size_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Always Use Full Size URL</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to use the full size URL regardless of the size in the content.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Maximum Sitemap<br />Image Limit</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[maximum_sitemap_image_limit]" id="[+slug_prefix+]_options_maximum_sitemap_image_limit" type="text" size="3" maxlength="5" value="[+maximum_sitemap_image_limit+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the maximum number of Image entries per post/page in the sitemap.</div>
  </td>
</tr>
<tr valign="top">
  <td colspan=2>
<span style="font-weight:bold; font-size:16px;>">Schema Pieces</span>
  </td>
</tr>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[enable_development_mode]" id="[+slug_prefix+]_options_enable_development_mode" type="checkbox" [+enable_development_mode_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Enable Schema Development Mode</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to pretty print the Schema script.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[enable_article_piece]" id="[+slug_prefix+]_options_enable_article_piece" type="checkbox" [+enable_article_piece_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Enable Schema Article Piece</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to add Image pieces to the Article piece.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[enable_webpage_piece]" id="[+slug_prefix+]_options_enable_webpage_piece" type="checkbox" [+enable_webpage_piece_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Enable Schema WebPage Piece</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to add Image pieces to the WebPage piece.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Minimum Image Width</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[minimum_image_width]" id="[+slug_prefix+]_options_minimum_image_width" type="text" size="3" maxlength="5" value="[+minimum_image_width+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the minimum image width required for adding an Image piece.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Maximum Image Limit</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[maximum_image_limit]" id="[+slug_prefix+]_options_maximum_image_limit" type="text" size="3" maxlength="5" value="[+maximum_image_limit+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the maximum number of ImageInfo pieces per post/page.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Image Piece Type</strong>
  </td>
  <td>
    <select name="[+slug_prefix+]_options[image_piece_type]" id="[+slug_prefix+]_options_image_piece_type">
		<option [+minimal_selected+] value="minimal">Minimal</option>
		<option [+simple_selected+] value="simple">Simple</option>
		<option [+complete_selected+] value="complete">Complete</option>
    </select>
    <div class="mla-settings-help">&nbsp;&nbsp;Select the amount of information in each piece.</div>
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
<p>This plugin uses filters provided by the Yoast SEO plugin to add information about images displayed by the <code>[mla_gallery]</code> shortcode to post/page SEO metadata.</p>
<p>You can find more information about using the features of this plugin in the Documentation tab on this screen.</p>
<div class="mla-page-level-options-form">
	<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-path-mapping-tab">
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
The <a href="https://wordpress.org/plugins/wordpress-seo/" target="_blank">Yoast SEO plugin</a> is designed to help visitors and search engines get the most out of a website by adding information to describe site content and structure, including:</p>
<ul class="mla_settings">
<li>Advanced <strong>XML sitemaps</strong>; making it easy for Google to understand your site structure.</li>
<li>An in-depth <strong>Schema.org integration</strong> that will increase your chance of getting rich results, by helping search engines to understand your content.</li>
</ul>
<p>
This MLA Example Plugin adds entries to the sitemaps and Schema that describe the images displayed by the <code>[mla_gallery]</code> shortcodes in your posts and pages. You can use options on the General tab to enable/disable entry generation and filter the size and number of the entries.
<a name="processing"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>How the Plugin Works</h3>
<p>
Depending on which options you enable, this plugin installs filters provided by the Yoast SEO plugin to add information to the sitemaps and schema. You can find more information about their features and filters here:</p>
<ul class="mla_settings">
<li><a href="https://developer.yoast.com/features/xml-sitemaps/" target="_blank">XML sitemaps</a></li>
<li><a href="https://developer.yoast.com/features/schema/" target="_blank">Schema.org integration</a></li>
</ul>
<p>
Both of those Yoast SEO features operate on each post/page separately, applying the filters to a single post/page. When the filter is applied, this plugin finds all of the <code>[mla_gallery]</code> shortcodes in the post/page content and executes them, using the <a href="[+settingsURL+]?page=mla-settings-menu-documentation&mla_tab=documentation#mla_gallery_hooks" target="_blank">MLA Gallery Filters and Actions (Hooks)</a> to capture the needed information for each item in the gallery. The information is formatted and passed back from this plugin's filter to Yoast SEO for inclusion in the sitemap or schema piece.
</p>
<p>
Of course, this plugin can only get results from an <code>[mla_gallery]</code> shortcode that does not depend on user-supplied input. Shortcodes that rely on taxonomy term selections or keyword search results cannot know which terms or keywords might be input. These shortcodes will return whatever items they display when the post/page is first loaded, before ay user-supplied input is available.
<a name="general-options"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>General Plugin Options</h3>
<p>
Here is more information regarding the option settings on the General tab:</p>
<ul class="mla_settings">
<li><strong>Enable XML Sitemaps</strong> - Check this box to allow this plugin to add image entries to the XMP sitemap.</li>
<li><strong>Always Use Full Size URL</strong> - Check this box to use the URL of the full size image in the sitemap, regardless of the URL that actually appears in the gallery display, e.g., the "thumbnail" size image file.</li>
<li><strong>Maximum Sitemap Image Limit</strong> - If your post/page contains a large number of images you can limit the number of entries added to the sitemap. Enter the maximum number of entries you think makes sense, or enter zero ('0') to bypass the maximum limit check. This value includes entries added by the Yoast SEO plugin as well as those added by this plugin.</li>
<li><strong>Enable Schema Development Mode</strong> - The <code>application/ld+json</code> script added to the header section of a post/page is usually compressed by removing whitespace and linebreaks. Check this box to "pretty print" the script, which makes it easier to read but much larger; useful for debugging but not for production sites.</li>
<li><strong>Enable Schema Article Piece</strong> - Check this box to allow this plugin to add image entries to the Article piece of the schema. The Article piece is present for posts but not for pages.</li>
<li><strong>Enable Schema WebPage Piece</strong> - Check this box to allow this plugin to add image entries to the WebPage piece of the schema. The WebPage piece is present for posts and pages. If the Article piece is present and contains image entries, the WebPage piece will contain references to the entries in the Article piece to avoid duplication and bloat.</li>
<li><strong>Minimum Image Width</strong> - The Yoast documentation for the Article piece states that included images must be at least 696 pixels wide, the default value for this option. Elsewhere the documentation simply says to use "common sense". Enter the minimum size you think makes sense, or enter zero ('0') to bypass the minimum width test.</li>
<li><strong>Maximum Image Limit</strong> - If your post/page contains a large number of images the schema information will also grow to a large size. Enter the maximum number of entries you think makes sense, or enter zero ('0') to bypass the maximum limit check.</li>
<li><strong>Image Piece Type</strong> - The schema specification allows for three levels of image details. The "Minimal" setting simply adds the image file URL to the schema. The "Simple" setting adds an ImageObject with the Required properties. The "Complete" setting adds an ImageObject with optional properties such as caption, height and width.</li>
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
If you are not getting the results you expect from the plugin carefully inspecting the XML sitemaps and schema script(s) can be a valuable exercise. This section gives more information about how to access them.
</p>
<p>
The sitemaps are generated by requesting the XML file that contains them. Yoast produces an XML sitemap index that contains an entry for each indivicdual sitemap. You can access the index by adding <code>/sitemap_index.xml</code> to your site URL, e.g., <code>http://mysite.com/sitemap_index.xml</code>. The index file will contain URLs for each individual sitemap. You can click on the <code>/post-sitemap.xml</code> or <code>/page-sitemap.xml</code> URL to get the individual sitemap containing the image entries added by this plugin. Once you have an individual sitemap you must use your browser's "View page source" feature to see the actual XML content including the image entries.</p>
<p>
The Schema scripts are added to the HTML headers for each post and page. You can use your browser's "Developer Tools" to view the HTML source code for the post/page. Look for and expand the <code>&lt;head></code> tag at the top of the page, then scroll down and find the <code>&lt;!-- / Yoast SEO plugin. --></code> comment line. Just above that you should see the <code>&ltscript type="application/ld+json" class="yoast-schema-graph"></code> tag; expand that to see the schema script.</p>
<p>
The example plugin does not record any debug information in the Error Log, but if you are having trouble you may find warning or error messages in the Log that can give you clues.  To activate MLA&rsquo;s debug logging:
</p>
<ol>
<li>Navigate to the <a href="[+settingsURL+]?page=mla-settings-menu-documentation&mla_tab=debug" target="_blank">Settings/Media Library Assistant Debug tab</a>.</li>
<li>Scroll down to the &ldquo;MLA Reporting&rdquo; text box and enter &ldquo;0x1&rdquo;. This will turn on MLA debug logging.</li>
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
[28-Dec-2020 05:38:55 UTC] 610 MLACore::mla_plugins_loaded_action() MLA 2.96 mla_debug_level 0x1<br />
[28-Dec-2020 05:38:55 UTC] 623 <strong>mla_debug REQUEST</strong> = array (
  'tax_input' => 
  array (
    'attachment_category' => 
    array (
      0 => 'def',
    ),
  ),
)
[28-Dec-2020 05:38:55 UTC] 635 <strong>mla_debug attributes</strong> = array (
  'post_mime_type' => 'image/j*',
  'numberposts' => '3',
  'columns' => '2',
  'size' => 'large',
  'mla_debug' => 'log',
  'mla_page_parameter' => 'mla_paginate_current',
)

</blockquote>
<p>
Of course, the details will be different. If you discover a defect in the plugin (or in MLA) you can <a href="http://wordpress.org/support/plugin/media-library-assistant" target="_blank">open a support topic</a> or <a href="http://davidlingren.com/#two" target="_blank">contact me at my web site</a> so it can be investigated further. I may ask for a copy of the log file from your tests.
</p>
</div>