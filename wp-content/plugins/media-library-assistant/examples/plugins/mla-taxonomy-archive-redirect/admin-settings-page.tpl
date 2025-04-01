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

<!-- template="taxonomy-specific-row" -->
<tr valign="top">
  <td class="textright">
    <strong>[+taxonomy+]</strong>
  </td>
  <td>
    <span>&nbsp;&nbsp;[+site_url+]</span>
    <input name="[+slug_prefix+]_options[[+taxonomy+]]" id="[+slug_prefix+]_options_[+taxonomy+]" type="text" size="30" maxlength="100" value="[+archive_page+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter (optional) the URL path for the taxonomy-specific destination page. Be sure to start with a slash.</div>
  </td>
</tr>

<!-- template="page-level-options" -->
<tr valign="top">
  <td class="textright">
    <strong>Default Archive Page</strong>
  </td>
  <td>
    <span>&nbsp;&nbsp;[+site_url+]</span>
    <input name="[+slug_prefix+]_options[archive_page]" id="[+slug_prefix+]_options_archive_page" type="text" size="30" maxlength="100" value="[+archive_page+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the URL path for the destination page. Be sure to start with a slash.</div>
  </td>
</tr>
[+taxonomy_rows+]

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
<p>You can use this tab to specify the destination page(s) for the archive redirection.</p>
<p>You can find more information about using all of the features of this plugin in the Documentation tab on this page.</p>
<div class="mla-page-level-options-form" style="display:block">
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
WordPress does not provide native taxonomy support for the Media Library's "attachment" post type. MLA adds taxonomy support and provides the Att. Categories and Att. Tags taxonomies for your convenience. Themes usually provide "taxonomy archive" pages, but these pages do not support taxonomies and terms assigned to Media Library items. They return an empty page with nothing displayed.  This plugin provides an alternative solution; you can define your own "taxonomy archive" page that uses MLA's <code>[mla_gallery]</code> shortcode to display a gallery of items assigned to a taxonomy term. This plugin will detect a URL that requests a taxonomy archive page, parse out the taxonomy and term and redirect the request to your page, supplying the taxonomy and term as query arguments.
</p>
<p>
The plugin operation and plugin options are described in the next sections below. The <a href="#examples"><strong>Examples</strong></a> section shows simple <code>[mla_gallery]</code> shortcodes that complete the solution. Of course, your application can include more elaborate shortcodes and  other elements on the destination page(s).
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
The plugin operation has two parts, implemented by functions that "hook" an action and a filter provided by WordPress. 
</p>
<p>
The first part hooks the "parse_query" action that fires after the main query variabless have been parsed (but before the query is processed). If the query came from an <code>[mla_gallery]</code> shortcode, the plugin stops because it can't be a conventional taxonomy archive query. Otherwise, the function begins by testing the main query variables for an "archive" query. If the test passes, the function parses the path portion of the request URL, looking for a path consisting of a taxonomy and a term. Then, the function analyzes the main query variables, looking for a "taxonomy archive" query that matches the taxonomy and term provided in the URL. If there's a match, one last test verifies that the taxonomy is in MLA's "supported" list. If it is, the function concludes by setting a filter that will fire ater the query has been processed. 
</p>
<p>
The second part hooks the "posts_results" filter that fires after the query has been processed and the number of "posts" it finds is known. If the query finds one or more posts, this plugin stops and allows the theme to handle the results and produce a conventional archive page.
</p>
<p>
If the query did not find any posts, the function analyzes the query variable to find which of the three types of taxonomy query is present and extracts the taxonomy and term values. The function composes a new request URL including the site's URL, the page specified in the plugin options and the two values. The function completes its work by calling the <code>wp_safe_redirect()</code> function to redirect the request to the new destination.
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
The General tab includes a default value and one or more taxonomy-specific values where you specify the destination post/page that implements your taxonomy archive page. If there are no taxonomy-specific values displayed on this tab, there are no MLA-supported taxonomies and the plugin will not produce any results.
</p>
<p>
Destination page values must begin with a slash, i.e., "/". The rest of the value depends on your Permalink structure, which you can view or set in the Settings/Permalinks admin page. An easy way to get the actual value for your site is to create the post/page and then view it in your browser. Look for the value in the URL box at the top of the browser window.
</p>
<p>
 The "Default Archive Page" value is used for all taxonomies that do not have a specific destination page. Each non-empty taxonomy-specific value is used for the corresponding taxonomy. If both the specific value and the default value are empty for a given taxonomy the plugin will not redirect the archive query.
</p>
<p>
The simplest way to this plugin is to enter a "Default Archive Page" value and leave the specific values empty. You can use the "taxonomy" query variable passed by this plugin to adjust your <code>[mla_gallery]</code> shortcodes(). You only need a taxonomy-specific value if you want the page content to be different for that taxonomy.
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
As outlined in the Introduction, this plugin is part of an application that has two components: 1) this example plugin, and 2) a destination page that displays the archive. As outlined above, all you have to do in this example plugin is supply the permalink of the destination page. The destination page content is described below.
</p>
<h4>Taxonomy archive page</h4>
<p>
The taxonomy archive destination page can have any content your application requires, but it will typically include one or more shortcodes, such as <code>[mla_gallery]</code>, that display a gallery of the archive items. In this example we have one shortcode for the gallery and a second shortcode for pagination controls. The first shortcode (for the gallery display) looks like this:
</p>
<pre style="width: 700px; background-color: rgba(0, 0, 0, 0.07); color: rgb(60, 67, 74); font-family: Consolas, Monaco, monospace">[mla_gallery]
tax_query="array(
array(
'taxonomy' => '{+template:({+request:taxonomy+}|attachment_category)+}',
'field' => 'slug',
'terms' => '{+template:({+request:term+}|no-term-selected)+}'
),
)"
posts_per_page=6
orderby="date"
order="DESC"
size=small
link=file
columns=3
mla_nolink_text="There are no items assigned to this term."
[/mla_gallery]
</pre>
<p>
The first shortcode parameter is a taxonomy query that uses the two query arguments passed from the example plugin to the destination page. Both the "taxonomy" and the "term" parameters have a template that uses the query argument, if present, or defaults to a safe value. The "no-term-selected" default generates an empty gallery if the page is accessed from some other part of the application. The "posts_per_page", "orderby" and "order" parameters are additional data selection criteria. The "mla_nolink_text" parameter supplies a text string displayed when the gallery is empty.
</p>
<p>
The second shortcode generates pagination controls for any gallery with a large number of items. If all of the gallery items fit on one page this shortcode will not produce any results. It looks like this:
</p>
<pre style="width: 700px; background-color: rgba(0, 0, 0, 0.07); color: rgb(60, 67, 74); font-family: Consolas, Monaco, monospace">[mla_gallery]
tax_query="array(
array(
'taxonomy' => '{+template:({+request:taxonomy+}|attachment_category)+}',
'field' => 'slug',
'terms' => '{+template:({+request:term+}|no-term-selected)+}'
),
)"
posts_per_page=6
orderby="date"
order="DESC"
mla_output='paginate_links,prev_next'
[/mla_gallery]
</pre>
<p>
The "tax_query", "posts_per_page", "orderby" and "order" parameters are the data selection criteria. <strong>These parameters must be identical to those specified in the first shortcode</strong> so the pagination controls will match the gallery display. The "size", "link" and "columns" parameters are not needed since this shortcode displays pagination controls, not gallery items. The "mla_output" parameter specifies the type on controls that will be generated.
</p>
<h4>Picking a taxonomy and term</h4>
<p>
By this point you might be wondering how the taxonomy archive requests are generated in the first place. Many themes provide some way to accomplish this, but you can also use MLA shortcodes to do the job. One simple solution is the "tag cloud" (which can be used with any taxonomy, not just tags). Here is a shortcode you can add to the archive page or any other post/page to allow the use to select a term:
</p>
<pre style="width: 700px; background-color: rgba(0, 0, 0, 0.07); color: rgb(60, 67, 74); font-family: Consolas, Monaco, monospace">[mla_tag_cloud]
taxonomy=attachment_category
post_mime_type=image
minimum=1
number=20
current_item="{+request:term+}"
current_item_class="mla_current_item"
mla_item_value="{+slug+}"
mla_link_href="{+site_url+}/{+taxonomy+}/{+slug+}/"
mla_link_class="{+current_item_class+}"
[/mla_tag_cloud]
</pre>
<p>
You can find more information about the <code>[mla_tag_cloud]</code> shortcode in the MLA Documentation tab. For the example cloud shortcode above, the parameters are:
</p>
<ul class="mla-doc-toc-list">
<li><strong>taxonomy</strong> - specifies the taxonomy for the cloud</li>
<li><strong>post_mime_type</strong> - restricts the cloud to image items (the default is all MIME types)</li>
<li><strong>minimum</strong> - restricts the cloud to terms with one or more assginments, eliminating "empty" terms</li>
<li><strong>number</strong> - limits the number of terms displayed to the twenty most popular terms (the default is all terms)</li>
<li><strong>current_item</strong> - specifies the term named in the archive URL as the current term</li>
<li><strong>current_item_class</strong> - adds an HTML class attribute to the current term (this is the default, so the parameter is not strictly necessary)</li>
<li><strong>mla_item_value</strong> - specifies that term slugs, not term IDs, are used in this cloud</li>
<li><strong>mla_link_href</strong> - specifies the link behind each term. The value is compatible with this example plugin</li>
<li><strong>mla_link_class</strong> - adds the appropriate class attribute to the link for the currently selected term</li>
</ul>
<p>
Adding an HTML class to the current term is all well and good, but to make the added class change the displayed term requires some custom CSS styles. You can do this in the theme's stylesheet or simply add some inline styles to the post/page content. Here is a simple example that wraps the cloud in a "div" tag and adds inline styles to color the current term red and display it in bold:
</p>
<pre style="width: 700px; background-color: rgba(0, 0, 0, 0.07); color: rgb(60, 67, 74); font-family: Consolas, Monaco, monospace">&lt;style type="text/css">
.the_tag_cloud a.mla_current_item,
.the_tag_cloud .mla_current_item a:visited {
	color:#FF0000;
	font-weight:bold}
&lt;/style>
&lt;div id=mla_cloud class="the_tag_cloud">
[mla_tag_cloud]
taxonomy=attachment_category
post_mime_type=image
minimum=1
number=20
current_item="{+request:term+}"
current_item_class="mla_current_item"
mla_item_value="{+slug+}"
mla_link_href="{+site_url+}/{+taxonomy+}/{+slug+}/"
mla_link_class="{+current_item_class+}"
[/mla_tag_cloud]
&lt;/div>
</pre>
<p>
If you add inline styles in the Gutenberg block editor, be sure to use the "Custom HTML" block.
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
If you are not getting the results you expect from the plugin carefully inspecting the results of detecting the archive and generating the redirection can be a valuable exercise.
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
[30-Mar-2025 22:45:24 UTC] 176 MLATaxonomyArchiveRedirect::initialize( /attachment_category/abc/ )
 $_REQUEST = array (
)
[30-Mar-2025 22:45:24 UTC] 189 MLATaxonomyArchiveRedirect::initialize $supported_taxonomies = array (
  0 => 'category',
  1 => 'post_tag',
  2 => 'attachment_category',
  3 => 'attachment_tag',
  4 => 'path_map_hierarchical',
)
[30-Mar-2025 22:45:24 UTC] 205 MLATaxonomyArchiveRedirect::initialize $current_settings = array (
  'archive_page' => '/archive-example-plugin-test/',
  'category' => '',
  'post_tag' => '',
  'attachment_category' => '',
  'attachment_tag' => '/archive-example-tag-test/',
  'path_map_hierarchical' => '',
)
[30-Mar-2025 22:45:24 UTC] 220 MLATaxonomyArchiveRedirect::initialize current_values = array (
  'category' => '',
  'post_tag' => '',
  'attachment_category' => '',
  'attachment_tag' => '/archive-example-tag-test/',
  'path_map_hierarchical' => '',
)
[30-Mar-2025 22:45:24 UTC] 279 MLATaxonomyArchiveRedirect::mla_parse_query_action request = array (
  'taxonomy' => 'attachment_category',
  'term' => 'abc',
)
[30-Mar-2025 22:45:24 UTC] 301 MLATaxonomyArchiveRedirect::mla_parse_query_action args = array (
  'taxonomy' => 'attachment_category',
  'term' => 'abc',
)
[30-Mar-2025 22:45:24 UTC] 350 MLATaxonomyArchiveRedirect::mla_posts_results_filter args = array (
  'taxonomy' => 'attachment_category',
  'term' => 'abc',
)
</pre>
<p>
Of course, the details will be different. If you discover a defect in the plugin (or in MLA) you can <a href="http://wordpress.org/support/plugin/media-library-assistant" target="_blank">open a support topic</a> or <a href="http://davidlingren.com/#two" target="_blank">contact me at my web site</a> so it can be investigated further. I may ask for a copy of the log file from your tests.
</p>
</div>