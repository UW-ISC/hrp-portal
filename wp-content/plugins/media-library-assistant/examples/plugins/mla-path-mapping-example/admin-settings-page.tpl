<!-- template="page" -->
<a name="backtotop"></a>
&nbsp;
<div class="wrap">
<h1 class="wp-heading-inline">MLA Path Mapping Example [+version+] Settings</h1>
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
  <td class="textright">
    <input name="[+slug_prefix+]_options[assign_parents]" id="[+slug_prefix+]_options_assign_parents" type="checkbox" [+assign_parents_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Assign parent terms</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to assign all terms in path, not just the last (leaf) term.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[assign_rule_parent]" id="[+slug_prefix+]_options_assign_rule_parent" type="checkbox" [+assign_rule_parent_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Assign Rule Parent term</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to assign the Rule Parent (if any) in addition to terms in path.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Path Delimiter</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[path_delimiter]" id="[+slug_prefix+]_options_path_delimiter" type="text" size="1" maxlength="1" value="[+path_delimiter+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the delimiter that separates path components.</div>
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
</style>
<div class="mla-display-settings-page" id="mla-display-settings-general-tab" style="width:600px">
  <h2>Plugin Options</h2>
  <p>In this tab you can define the path delimiter and parent-term handling options for mapping terms in hierarchical taxonomies.</p>
  <p>You can find more information about using the features of this plugin in the Documentation tab on this screen.</p>
  <div class="mla-page-level-options-form" style="display:inline">
    <form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-path-mapping-tab">
      <table class="optiontable">
        <tbody>
        
        [+options_list+]
        </tbody>
        
      </table>
      <span class="submit mla-settings-submit">
      <input name="[+slug_prefix+]_options_save" class="button-primary" id="[+slug_prefix+]_options_save" type="submit" value="Save Changes" />
      <input name="[+slug_prefix+]_options_reset" class="button-primary alignright" id="[+slug_prefix+]_options_reset" type="submit" value="Delete Settings, Restore Defaults" />
      </span> [+_wpnonce+]
    </form>
  </div>
  <div class="mla-page-level-tools-form" style="display:inline">
    <h3>Plugin Tool(s)</h3>
    <p>The plugin tools let you copy term definitions or term assignments from a source taxonomy to one destination taxonomy. Pick your source and destination sites, then run one of the tools below.</p>
    <form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-general-tools-form">
      <input name="[+slug_prefix+]_tools[path_delimiter]" id="[+slug_prefix+]_tools_path_delimiter" type="hidden" value="[+path_delimiter+]" />
      <table style="border: 1px solid; width: 100%">
        <tbody>
          <tr>
            <td valign="top" width="300px"><strong>Source Taxonomy</strong></td>
            <td valign="top">&nbsp;&nbsp;&nbsp;&nbsp; </td>
            <td valign="top"><strong>Destination Taxonomy</strong></td>
          </tr>
          <tr>
            <td valign="top" width="300px">
			  <select name="[+slug_prefix+]_tools[source_taxonomy]" id="[+slug_prefix+]_tools_source_taxonomy">
                
[+source_taxonomy+]
            
              </select></td>
            <td valign="top">&nbsp;&nbsp;&nbsp;&nbsp; </td>
            <td valign="top">
			  <select name="[+slug_prefix+]_tools[destination_taxonomy]" id="[+slug_prefix+]_tools_destination_taxonomy">
                
[+destination_taxonomy+]
            
              </select></td>
          </tr>
        </tbody>
      </table>
      <p>The "Copy Term Definitions" tool lets you copy all the term definitions from a source taxonomy to one destination taxonomy.</p>
      <table style="border: 1px solid; width: 100%">
        <tbody>
          <tr> 
            <!--          <td valign="top" width="300px">
            <table>
              <tbody>
                <tr>
                  <td valign="top" class="textright"><input name="[+slug_prefix+]_tools[copy_defaults]" id="[+slug_prefix+]_tools_copy_defaults" type="checkbox" [+copy_defaults_checked+] value="1" /></td>
                  <td valign="top">&nbsp;<strong>Copy Defaults</strong></td>
                </tr>
              </tbody>
            </table>
          </td> -->
            <td valign="top">&nbsp;&nbsp;&nbsp;&nbsp; </td>
            <td valign="top"><span class="submit mla-settings-submit">
              <input name="[+slug_prefix+]_tools_copy_definitions" class="button-primary" id="[+slug_prefix+]_tools_definitions" type="submit" value="Copy Term Definitions" />
              </span>
              <div class="mla-settings-help">&nbsp;<br />
                Term <strong>names</strong>, not slugs or IDs, are used for this operation.<br />
                &nbsp;&nbsp;Be sure the read the Documentation before you proceed!. </div>
            </td>
          </tr>
        </tbody>
      </table>
      <p>The "Copy Term Assignments" tool lets you copy term definitions from a source MLA site to one or more destination sites.</p>
      <table style="border: 1px solid; width: 100%">
      <tbody>
        <tr>
          <!-- <td valign="top" width="300px"><table>
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
            </table>
		</td> -->
                  <td valign="top">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                  <td valign="top"><span class="submit mla-settings-submit">
                    <input name="[+slug_prefix+]_tools_copy_assignments" class="button-primary" id="[+slug_prefix+]_tools_copy_assignments" type="submit" value="Copy Term Assignments" />
                    </span>
              <div class="mla-settings-help">&nbsp;<br />
                Term <strong>names</strong>, not slugs or IDs, are used for this operation.<br />
                &nbsp;&nbsp;Be sure the read the Documentation before you proceed!. </div>
                  </td>
                </tr>
              </tbody>
            </table>
            [+_wpnonce+]
    </form>
  </div>
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
</style>
<h2>Plugin Documentation. In this tab, jump to:</h2>
<div class="mla-display-settings-page" id="mla-display-settings-documentation-tab" style="width:700px">
<ul class="mla-doc-toc-list">
<li><a href="#introduction"><strong>Introduction</strong></a></li>
<li><a href="#processing"><strong>How the Plugin Works</strong></a></li>
<li><a href="#general-options"><strong>General Plugin Options</strong></a></li>
<li><a href="#general-tools"><strong>Plugin Tools</strong></a></li>
<li><a href="#debugging"><strong>Debugging and Troubleshooting</strong></a></li>
</ul>
<p>&nbsp;</p>
<ul class="mla-doc-toc-list">
<li><a href="#mapping-rule-process"><strong>Appendix: The Mapping Rule Process</strong></a></li>
<li><a href="#assigning-ids"><strong>Assigning Term ID Values</strong></a></li>
<li><a href="#names-vs-slugs"><strong>Term Names Vs Slugs</strong></a></li>
<li><a href="#flat-taxonomies"><strong>Path Specifications for Flat Taxonomies</strong></a></li>
<li><a href="#matching"><strong>Matching on Term Name</strong></a></li>
<li><a href="#creating"><strong>Creating New Terms</strong></a></li>
<li><a href="#multi-level"><strong>Assigning Multi-level Terms</strong></a></li>
<li><a href="#mapping-multiple"><strong>Mapping Multiple Items</strong></a></li>
</ul>
<p>
<a name="introduction"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Introduction</h3>
<p>
The current MLA version provides mapping rules to assign taxonomy terms to Media Library items from IPTC, EXIF and XMP metadata values embedded in the image files. For hierarchical taxonomies, the current version allows new terms to be added under a specified &ldquo;Parent&rdquo; term, but does not support more general mapping of terms within multiple levels.
</p>
<p>
The current version allows the specification of &ldquo;Delimiters&rdquo; (including a space character) to separate multiple terms within a metadata value. This example plugin adds a &ldquo;Path Delimiter&rdquo; that allows term parent and higher-level ancestor values to be specified, more precisely placing a term in the hierarchy. For example, a value such as &ldquo;/grand parent/parent/child&rdquo; denotes a specific term within a three-level hierarchy. In this example the delimiter at the start of the value means that &ldquo;grand parent&rdquo; must be a root term, i.e., it appears at the highest level and has no ancestors. This is an absolute path specification. A path such as &ldquo;parent/child&rdquo; is a relative path specification, starting wherever &ldquo;parent&rdquo; appears in the hierarchy.
</p>
<p>
<a name="processing"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>How the Plugin Works</h3>
<p>
This section explains the implementation of each plugin feature. It's a bit technical but may help you understand why a feature works the way it does.
</p>
<h4>The path mapping filters</h4>
<p>
The plugin lets you specify relative and absolute path values for terms assigned to Media Library items. It uses three of the filters MLA provides, called during the evaluation of an IPTC/EXIF/WP mapping rule for a hierarchical taxonomy. Other mapping rules and flat taxonomy mapping rules are ignored. The three filters are called for each Media Library item for which mapping is being processed.
</p>
<p>
The <code>mla_mapping_rule</code> filter is called once for each mapping rule, before the rule is evaluated. In this filter the plugin simply saves a copy of the rule in a private class variable so it can be used in the other two filters.
</p>
<p>
The <code>mla_mapping_new_text</code> filter is called once for each IPTC/EXIF mapping rule, after the selection between the IPTC and EXIF values has been made. This filter does most of the work, ensuring that every component of the path has a term definition in the tqxonomy. It looks for existing terms, and inserts missing terms as necessary. First, two validation checks are performed:
</p>
<ol>
<li>Verify that this is a hierarchical taxonomy mapping rule, exiting if not.</li>
<li>If the rule specifies a Parent under which terms are to be located, verify that the Parent exists, ignor it if not.</li>
</ol>
<p>
MLA uses a term cache defined as a public property (<code>MLAOptions::$mla_term_cache</code>). This filter populates that term cache with elements for every path component that requires a term assignment. This includes the lowest-level term and parent terms depending on the plugin option settings. A unique "term name" is the array key and the correct term id is the array value. The unique "term name" for any parent term assignments is added to the array of term assignments returned from the filter. During this process each path component is checked to see if it matches an existing term using the WordPress <code>term_exists()</code> function. Any path compoonent that does not already exist is added using the WordPress <code>wp_insert_term()</code> function. This ensures that all of the terms exist in their proper hierarchy position when MLA performs the actual term assignments.
</p>
<p>
The <code>mla_mapping_updates</code> filter is called AFTER all mapping rules are applied. The plugin does not have any active logic in this filter, but you can use it to inspect the final set of updates MLA will make to the item based on the mapping rules.
</p>
<h4>The copy definitions tool</h4>
<p>
As the name implies, the Copy Term Definitions tool copies term definitions from a source taxonomy to a destination taxonomy. Existing destination definitions are preserved and their locations in the term hierarchy are not changed. Source term definitions that do not exist in the destination are inserted, including any parent term(s) needed to preserve the location in the hierarchy.
</p>
<p>
As described in the <a href="#names-vs-slugs">Term Names Vs Slugs</a> section below, term slug values are guaranteed to be unique within a given taxonomy, and there is no problem having the same slug value in two or more taxonomies. This makes them the best choice for matching source definitions to existing destination definitions. The source slug will also be used to insert new destination terms so source parent/child relationships can be replicated in the destination taxonomy.
</p>
<p>
The tool begins by compiling an array of a source terms; the array key is the term ID and the elements are an array with the term name, slug, description and parent.The WordPress <code>get_terms()</code> function provides the source terms for this step.
</p>
<p>
The tool completes the operation by iterating through the array of source terms, checking to see if they exist in the destination taxonomy and inserting them if they do not. If the destination taxonomy is hierarchical the logic will check for and insert parent terms as necessary. If the destination taxonomy is flat, all terms are inserted at the root level and source taxonomy parent relationships are ignored. Of course, if the source taxonomy is flat there are no parent relationships to consider and all terms are inserted at the root level of the destination taxonomy.
</p>
<p>
For each source term, the tool calls the WordPress <code>get_term_by()</code> function using the term slug to detect a match. If a match is found the operation is complete and the tools moves on to the next source term. If there's no match, the tool inserts the source term as a new destination term. If the source term has a parent, the tool will see if the parent exists and if so, insert the new term under the existing parent (ignoring the original source parent). If the parent does not exist the tool will insert it and then insert the source term under the new destination parent. The parent match/insert process can extend up multiple levels to the root level.
</p>
<h4>The copy assignments tool</h4>
<p>
The Copy Term Assignments tool copies term assignments for all Media Library items from a source taxonomy to a destination taxonomy. Existing destination assignments are preserved and their locations in the term hierarchy are not changed. Source term definitions that do not exist in the destination are inserted, including any parent term(s) needed to preserve the location in the hierarchy. Because the purpose of this tool is to copy existing term assignments, the Assign parent terms and Assign Rule Parent term plugin options have no effect on the operation.
</p>
<p>
The tool begins by compiling an array of Media Library item ID values, using the WordPress <code>$wpdb->get_col()</code> function. The function selects the ID from the posts database table where <code>post_type = 'attachment' AND post_status = 'inherit'</code>.
</p>
<p>
The tool continues the operation by iterating through the array of ID values, building an array of destination term assignments on an item-by-item basis. For each Media Library item, the current source taxonomy assignments are gathered using the WordPress <code>wp_get_object_terms()</code> function. For each source assignment, the destination taxonomy is queried using the slug to see if a corresponding term exists. If it does, the destination term's <code>term_taxonomy_id</code> is saved in the destination assignments array. If there's no match, the tool inserts the source term as a new destination term, adding parent terms as necessary.
</p>
<p>
Once all of the item's source assignments have been evaluated, the tool completes the operation by calling the WordPress <code>wp_set_post_terms()</code> function using the array of destination term_taxonomy_id values. Then, the tool moves on to the next item and repeats the process.
</p>
<p>
<a name="general-options"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>General Plugin Options</h3>
<p>
There are three General option settings in this version of the plugin. These settings apply to the plugin's path mapping filters; they have no effect on the plugin tools for copying definitions and assignments.
</p>
<h4>Assign parent terms</h4>
<p>
When a term has one or more parent level terms above it, this option lets you chose betweening assigning just the lowest-level (leaf) term, or also assigning the parent term(s) to tht item. For example, a value such as &ldquo;/grand parent/parent/child&rdquo; denotes a specific term within a three-level hierarchy. If you check the Assign parent terms box, all three terms will be assigned to the item. If unchecked, only the &ldquo;child&rdquo; term will be assigned.
</p>
<h4>Assign Rule Parent term</h4>
<p>
If your IPTC/EXIF/WP mapping rule specifies a Parent term under which mapped terms will go, this option lets you assign the rule's Parent term in addition to the term(s) the rule assigns to the item. In this case, the "Assign parent terms" option will only apply to multiple levels under the Parent term. If the Parent term has a parent of its own, that term will <strong>never</strong> be assigned by the mapping rule.
</p>
<h4>Path Delimiter</h4>
<p>
This option lets you specify the single character that separates the parts of a multi-level path or starts an absolute path. The current plugin version does not provide a way to escape delimiters that appear within a term name, so be sure to pick a character that does not occur in any term name.
</p>
<p>
<a name="general-tools"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Plugin Tools</h3>
<p>
Each plugin tool operates by taking information from one Source Taxonomy, e.g., term definitions or term assignments, and copying it to one  Destination Taxonomy. To use a tool:
</p>
<ol>
<li>Select a source taxonomy (copy from) in the dropdown control on the left</li>
<li>Select a destination taxonomy (copy to) in the dropdown control on the right</li>
<li>Click the tool's "Copy..." button to perform the operation.</li>
</ol>
<p>
When the page refreshes a status message at the top of the page will summarize the results of the operation. The source and destination choices are retained in the dropdown controls. You can reset them by clicking on the "General" tab header.
</p>
<h4>The Copy Term Definitions tool</h4>
<p>
The Copy Term Definitions tool copies term definitions from the source taxonomy to one destination taxonomy. The source and destination dropdown controls include all taxonomies that are supported by MLA (see Taxonomy Support in the Settings/Media Library Assistant General tab).
</p>
<p>
For the source taxonomy you select, the tool compiles all the term definitions. It then goes to the destination taxonomy and ensures that all of the source terms exist there. The tool inserts any source term that does not already exist. The tool won't delete any existing destination term that isn't in the source terms, and the tool does not make or affect any term assignments to Media Library items. Be sure you read the details in the "How the Plugin Works" section so you understand the operation and limitations of this tool.
</p>
<h4>The Copy Term Assignments tool</h4>
<p>
The Copy Term Assignments tool copies term assignments from the source taxonomy to one destination taxonomy. The source and destination dropdown controls include all taxonomies that are supported by MLA (see Taxonomy Support in the Settings/Media Library Assistant General tab).
</p>
<p>
The tool begins by assembling a list of all Media Library items. For each item, the tool compiles all the term assignments in the source taxonomy. It then goes to the destination taxonomy and ensures that all of the assigned terms exist there. The tool inserts any term that does not already exist, including any parent terms to ensure that the term hierarchy is replicated. The tool won't delete any existing destination term assignments. Be sure you read the details in the "How the Plugin Works" section so you understand the operation and limitations of this tool.
</p>
<p>
<a name="debugging"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Debugging and Troubleshooting</h3>
<p>
If you are not getting the results you expect from the plugin carefully inspecting the intermediate results of the tools and mapping filters can be a valuable exercise. To activate MLA&rsquo;s debug logging:
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
[30-Nov-2023 01:35:08 UTC] 709 MLACore::mla_plugins_loaded_action() MLA 3.13 (20231118) mla_debug_level 0x8001<br />
[30-Nov-2023 01:35:08 UTC] 178 MLAPathMappingExample::initialize() request = array (<br />
  'page' => 'mlapathmap-settings-general',<br />
  'mla_tab' => 'general',<br />
  'mlapathmap_tools' => <br />
  array (<br />
    'path_delimiter' => '/',<br />
    'source_taxonomy' => 'attachment_category',<br />
    'destination_taxonomy' => 'category',<br />
  ),<br />
  'mlapathmap_tools_copy_definitions' => 'Copy Term Definitions',<br />
  'mla_admin_nonce' => 'f354d71f1c',<br />
  '_wp_http_referer' => '/wp-admin/options-general.php?page=mlapathmap-settings-general&mla_tab=general',<br />
)<br />
[30-Nov-2023 01:35:08 UTC] 442 MLAPathMappingExample::mpm_copy_definitions_action( attachment_category, category} )<br />
</blockquote>
<p>
Of course, the details will be different. If you discover a defect in the plugin (or in MLA) you can <a href="http://wordpress.org/support/plugin/media-library-assistant" target="_blank">open a support topic</a> or <a href="http://davidlingren.com/#two" target="_blank">contact me at my web site</a> so it can be investigated further. I may ask for a copy of the log file from your tests.
</p>
<p>
<a name="mapping-rule-process"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h2>Appendix: The Mapping Rule Process</h2>
<p>
The sections below contain a more detailed description of how WordPress implements taxonomies and the implications for the process of mapping rules that use path specifications containing term <strong>names</strong>. The plugin tools for copying term definitions and assignments use a somewhat different approach based on term <strong>slugs</strong>. Slugs are more reliable but are generated and managed internally; they are not available to mapping rules that use external values such as IPTC/EXIF/XMP metadata.
<p>
<a name="assigning-ids"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Assigning Term ID Values</h3>
<p>
Because a given Term Name can exist in multiple taxonomies and in more than one place in a given hierarchical taxonomy, Term ID is an important part of organizing and finding terms within a taxonomy. Early versions of WordPress supported &ldquo;shared terms&rdquo;, which required a separate Term-taxonomy ID to uniquely distinguish among multiple occurrences of the same Term Name; see the Appendix for more information. Shared terms no longer occur in more recent WordPress versions (4.3+) so Term ID is now unique.
</p>
<p>
Term ID values start at 1 (assigned to the WordPress &ldquo;Uncategorized&rdquo; category) and are incremented each time a new term is created. They are never reused, so if terms are deleted there will be gaps in the remaining ID values.
</p>
<p>
If the same Term Name is used more than once, how do you decide which term is specified by an unqualified Term Name? WordPress matches an unqualified Term Name to the term with the lowest Term ID, i.e., the &ldquo;oldest&rdquo; term defined with that name. See the &ldquo;Matching on Term Name&rdquo; section for more information. 
</p>
<p>
<a name="names-vs-slugs"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Term Names Vs Slugs</h3>
<p>
When terms are added to a taxonomy WordPress accepts a term Name, an optional Slug, an optional Parent and an optional Description. WordPress will generate a Slug from the Name and will always add whatever is needed to make the slug unique within the taxonomy. For example, consider these terms:
<table cellpadding="5">
<tr>
<td><strong>Name</strong></td>
<td><strong>Slug</strong></td>
<td><strong>Name</strong></td>
<td><strong>Slug</strong></td>
<tr>
<td>Grand Parent One (08)</td>
<td>grand-parent-one</td>
<td>Grand Parent Two (02)</td>
<td>grand-parent-two</td>
</tr>
<tr>
<td>&mdash; Parent Two (10)</td>
<td>parent-two-grand-parent-one</td>
<td>&mdash; Parent Three (05)</td>
<td>parent-three</td>
</tr>
<tr>
<td>&mdash; &mdash; Child Three (13)</td>
<td>child-three-parent-two-grand-parent-one</td>
<td>&mdash; &mdash; Child Five (07)</td>
<td></td>
</tr>
<tr>
<td>&mdash; &mdash; Child Two (12)</td>
<td>child-two</td>
<td>&mdash; &mdash; Child Three (06)</td>
<td>child-three</td>
</tr>
<tr>
<td>&mdash; Parent One (09)</td>
<td>parent-one</td>
<td>&mdash; Parent Two (03)</td>
<td>parent-two</td>
</tr>
<tr>
<td>&mdash; &mdash; Child One (11)</td>
<td>child-one</td>
<td>&mdash; &mdash; Child Four (04)</td>
<td>child-four</td>
</tr>
</table>
In this example, the &ldquo;Grand Parent Two&rdquo; hierarchy was added first and the slugs in those terms are obvious. The &ldquo;Parent Two&rdquo; and &ldquo;Child Three&rdquo; terms under &ldquo;Grand Parent One&rdquo; have modified slugs to avoid duplication with the terms added earlier.
There is no prohibition against the same slug appearing in two different taxonomies.
</p>
<p>
<a name="flat-taxonomies"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Path Specifications for Flat Taxonomies</h3>
<p>
The plugin ignores mapping rules for non-hierarchical, or &ldquo;flat&rdquo;, taxonomies such as Att. Tags. Any path specifications found will be processed as a simple term name, e.g., &ldquo;/parent/child&rdquo; will become a simple root-level term, not separate &ldquo;parent&rdquo; and &ldquo;child&rdquo; terms.
</p>
<p>
<a name="matching"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Matching on Term Name</h3>
<p>
Because the term Slug cannot be known in advance, MLA mapping rules match term values in item metadata against the term Name. When duplicate term Names exist in a taxonomy MLA will match the term defined earliest, i.e., with the lowest term_id value. In the above example &ldquo;Child Three&rdquo; will match term_id &ldquo;6&rdquo;, under &ldquo;Grand Parent Two/Parent Three&rdquo;. Several examples of WordPress &ldquo;search by term Name&rdquo; functions and their results are in the Appendix.
</p>
<p>
When duplicate term Names exist in a taxonomy they can always be disambiguated by specifying the complete, absolute path to the desired term. Starting a relative path with a unique ancestor name will also work.</p>
<p>
Finally, the &ldquo;Parent&rdquo; value <strong>in the mapping rule</strong> (a rule Parent) can restrict the search to a subset of the term tree and change the handling of relative paths. For example:
</p>
<ul class="mla_settings">
<li>if the <strong>rule</strong> Parent is &ldquo;Grand Parent One&rdquo;, then a <strong>relative path</strong> such as &ldquo;Child Three&rdquo; will be matched as &ldquo;/Grand Parent One/Child Three&rdquo;. It will not match any other &ldquo;Child One&rdquo; values lower down in the subtree.</li>
<li>if the <strong>rule</strong> Parent is &ldquo;Grand Parent One/Parent Two&rdquo;, then a <strong>relative path</strong> such as &ldquo;Child Three&rdquo; will be matched as &ldquo;/Grand Parent One/Parent Two/Child Three&rdquo;.</li>
</ul>
<p>
An absolute path will ignore the rule Parent and start from the root level of the term hierarchy.
For more examples, here is a hierarchical taxonomy sorted by the order in which the terms were created (Term ID order):
</p>
<code>
Grand Parent Two (02)<br />
Grand Parent Two/Parent Two (03)<br />
Grand Parent Two/Parent Two/Child Four (04)<br />
Grand Parent Two/Parent Three (05)<br />
Grand Parent Two/Parent Three/Child Three (06)<br />
Grand Parent Two/Parent Three/Child Five (07)<br />
Grand Parent One (08)<br />
Grand Parent One/Parent One (09)<br />
Grand Parent One/Parent Two (10)<br />
Grand Parent One/Parent One/Child One (11)<br />
Grand Parent One/Parent Two/Child Two (12)<br />
Grand Parent One/Parent Two/Child Three (13)
</code>
<p>
Here&rsquo;s the way the taxonomy appears in the Edit Taxonomy admin submenu:
</p>
<code>
Grand Parent One (08)<br />
— Parent One (09)<br />
— — Child One (11)<br />
— Parent Two (10)<br />
— — Child Three (13)<br />
— — Child Two (12)<br />
Grand Parent Two (02)<br />
— Parent Three (05)<br />
— — Child Five (07)<br />
— — Child Three (06)<br />
— Parent Two (03)<br />
— — Child Four (04)
</code>
<p>
Here are the results of matching ambiguous unqualified names to taxonomy terms (assuming no rule Parent):
</p>
<ul>
<li>&ldquo;<strong>Child Three</strong>&rdquo; => Grand Parent Two/Parent Three/<strong>Child Three</strong> (06)</li>
<li>&ldquo;<strong>Parent Two</strong>&rdquo; => Grand Parent Two/<strong>Parent Two</strong> (03)</li>
</ul>
<p>
You can&rsquo;t read down the displayed list and easily predict how the match will come out!
</p>
<p>
Here are the unexpected results of matching relative paths with ambiguous initial names (assuming no rule Parent):
</p>
<ul>
<li>&ldquo;<strong>Parent Two/Child Four</strong>&rdquo; => Grand Parent Two/<strong>Parent Two/Child Four</strong> (04)</li>
<li>&ldquo;<strong>Parent Two/Child Three</strong>&rdquo; => <strong>NO MATCH</strong> - creates Grand Parent Two/<strong>Parent Two/Child Three</strong> (14)
</li>
</ul>
<p>
In the second case, to match the existing &ldquo;Parent Two/Child Three&rdquo; the ambiguous &ldquo;Parent Two&rdquo; must be made unique by adding &ldquo;Grand Parent One&rdquo;. To avoid this issue always start a relative path with a unique name.
</p>
<p>
<a name="creating"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3><strong>Creating New Terms</strong></h3>
<p>
When a term embedded in the metadata in an item&rsquo;s file is not found in the taxonomy it must be added to the taxonomy&rsquo;s terms in the proper location.
</p>
<p>
Single-level terms are straightforward. If a rule Parent is specified they will be added just under the Parent. If the rule Parent is not specified they will be added a new root-level terms.
</p>
<p>
Multi-level terms are more challenging, since any of the path elements might be new to the taxonomy. Consider these additions to the above example taxonomy:
</p>
<ol>
<li>&ldquo;<strong>Parent Three/Child Six</strong>&rdquo; – a new &ldquo;Child Six&rdquo; term will be added just under the existing &ldquo;Parent Three&rdquo;.</li>
<li>&ldquo;<strong>Parent Four/Child One</strong>&rdquo; – both of the path elements are new to the taxonomy. &ldquo;Parent Four&rdquo; will be added as a new root-level term (no rule Parent) or just under the rule Parent. Then, &ldquo;Child One&rdquo; will be added just under the new &ldquo;Parent Four&rdquo;.</li>
<li>&ldquo;<strong>Grand Parent Two/Parent Four/Child One</strong>&rdquo; – &ldquo;Parent Four&rdquo; will be added as a new term just under the existing &ldquo;Grand Parent Two&rdquo;. Then, &ldquo;Child One&rdquo; will be added just under the new &ldquo;Parent Four&rdquo;.</li>
<li>&ldquo;<strong>/Parent Four/Child Six</strong>&rdquo; – &ldquo;Parent Four&rdquo; will be added as a new root-level term. Then, a new &ldquo;Child Six&rdquo; term will be added just under the new &ldquo;/Parent Four&rdquo;.</li>
</ol>
<p>
In general, multi-level terms are processed one level at a time from left to right. Relative paths take the rule Parent into account, absolute paths always start form the root level.
</p>
<p>
<a name="multi-level"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3><strong>Assigning Multi-level Terms</strong></h3>
<p>
WordPress allows you to assign terms at any level in any combination. In other words, given the absolute path &ldquo;/Grand Parent One/Parent Two/Child Three&rdquo; you can assign one, two or all three of the path elements to the item. If you choose to assign only lowest-level &ldquo;Child Three&rdquo; you can still associate the item with &ldquo;Grand Parent One&rdquo; by including &ldquo;children&rdquo; in a term query.
</p>
<p>
The two common choices seem to be &ldquo;assign all levels&rdquo; or &ldquo;assign lowest level&rdquo;. Adding syntax to assign or skip individual path elements seems overly complicated. For this reason, a simple Assign rule parents checkbox option will select one of the two common choices.
</p>
<p>
When a Rule Parent is specified, a second checkbox option will select whether the Rule Parent term is also assigned to the item. The Rule Parent term is only assigned when one or more other terms are assigned as well, so items with no explicit term assignments do not get assigned to the Rule Parent.
</p>
<p>
Even if a Rule Parent is specified, absolute paths (i.e., beginning with the path delimiter) will be assigned at the root level, not under the rule Parent. Only relative paths will be assigned under the rule parent.
</p>
<p>
<a name="mapping-multiple"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Mapping Multiple Items</h3>
<p>
All of MLA&rsquo;s mapping operations are performed on each item in isolation. If you are mapping two or more items in an upload, a batch edit or a &ldquo;map all items&rdquo; operation be aware that the order in which the items are processed can affect the placement of new terms within the hierarchy. For example, consider these two items and their terms:
</p>
<ol>
<li>Item A, terms &ldquo;/Grand Parent One/Parent Two/Child Three&rdquo;</li>
<li>Item B, terms &ldquo;Parent Two/Child Three&rdquo;</li>
</ol>
<p>
If these items are processed in order there will be a single &ldquo;Parent Two&rdquo; term under &ldquo;Grand Parent One&rdquo;. If, however, Item B is processed before Item A there will be a root-level &ldquo;Parent Two&rdquo; term from Item B and a separate &ldquo;/Grand Parent One/Parent Two&rdquo; term from Item A. The best defense is unique names for every term!
</p>
</div>