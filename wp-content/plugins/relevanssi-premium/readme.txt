=== Relevanssi Premium - A Better Search ===
Contributors: msaari
Donate link: https://www.relevanssi.com/
Tags: search, relevance, better search
Requires at least: 4.9
Requires PHP: 7.0
Tested up to: 5.8.2
Stable tag: 2.16.5

Relevanssi Premium replaces the default search with a partial-match search that sorts results by relevance. It also indexes comments and shortcode content.

== Description ==

Relevanssi replaces the standard WordPress search with a better search engine, with lots of features and configurable options. You'll get better results, better presentation of results - your users will thank you.

= Key features =
* Search results sorted in the order of relevance, not by date.
* Fuzzy matching: match partial words, if complete words don't match.
* Find documents matching either just one search term (OR query) or require all words to appear (AND query).
* Search for phrases with quotes, for example "search phrase".
* Create custom excerpts that show where the hit was made, with the search terms highlighted.
* Highlight search terms in the documents when user clicks through search results.
* Search comments, tags, categories and custom fields.

= Advanced features =
* Adjust the weighting for titles, tags and comments.
* Log queries, show most popular queries and recent queries with no hits.
* Restrict searches to categories and tags using a hidden variable or plugin settings.
* Index custom post types and custom taxonomies.
* Index the contents of shortcodes.
* Google-style "Did you mean?" suggestions based on successful user searches.
* Automatic support for [WPML multi-language plugin](http://wpml.org/).
* Automatic support for [s2member membership plugin](http://www.s2member.com/).
* Advanced filtering to help hacking the search results the way you want.
* Search result throttling to improve performance on large databases.
* Disable indexing of post content and post titles with a simple filter hook.
* Multisite support.

= Premium features (only in Relevanssi Premium) =
* PDF content indexing.
* Search result throttling to improve performance on large databases.
* Improved spelling correction in "Did you mean?" suggestions.
* Searching over multiple subsites in one multisite installation.
* Indexing and searching user profiles.
* Weights for post types, including custom post types.
* Limit searches with custom fields.
* Index internal links for the target document (sort of what Google does).
* Search using multiple taxonomies at the same time.

Relevanssi is available in two versions, regular and Premium. Regular Relevanssi is and will remain free to download and use. Relevanssi Premium comes with a cost, but will get all the new features. Standard Relevanssi will be updated to fix bugs, but new features will mostly appear in Premium. Also, support for standard Relevanssi depends very much on my mood and available time. Premium pricing includes support.

= Relevanssi in Facebook =
You can find [Relevanssi in Facebook](https://www.facebook.com/relevanssi). Become a fan to follow the development of the plugin, I'll post updates on bugs, new features and new versions to the Facebook page.

= Other search plugins =
Relevanssi owes a lot to [wpSearch](https://wordpress.org/extend/plugins/wpsearch/) by Kenny Katzgrau. Relevanssi was built to replace wpSearch, when it started to fail.

Search Unleashed is a popular search plugin, but it hasn't been updated since 2010. Relevanssi is in active development and does what Search Unleashed does.



== Installation ==

1. Extract all files from the ZIP file, and then upload the plugin's folder to /wp-content/plugins/.
1. If your blog is in English, skip to the next step. If your blog is in other language, rename the file *stopwords* in the plugin directory as something else or remove it. If there is *stopwords.yourlanguage*, rename it to *stopwords*.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to the plugin settings and build the index following the instructions there.

To update your installation, simply overwrite the old files with the new, activate the new version and if the new version has changes in the indexing, rebuild the index.

= Note on updates =
If it seems the plugin doesn't work after an update, the first thing to try is deactivating and reactivating the plugin. If there are changes in the database structure, those changes do not happen without a deactivation, for some reason.

= Changes to templates =
None necessary! Relevanssi uses the standard search form and doesn't usually need any changes in the search results template.

If the search does not bring any results, your theme probably has a query_posts() call in the search results template. That throws Relevanssi off. For more information, see [The most important Relevanssi debugging trick](http://www.relevanssi.com/knowledge-base/query_posts/).

= How to index =
Check the options to make sure they're to your liking, then click "Save indexing options and build the index". If everything's fine, you'll see the Relevanssi options screen again with a message "Indexing successful!"

If something fails, usually the result is a blank screen. The most common problem is a timeout: server ran out of time while indexing. The solution to that is simple: just return to Relevanssi screen (do not just try to reload the blank page) and click "Continue indexing". Indexing will continue. Most databases will get indexed in just few clicks of "Continue indexing". You can follow the process in the "State of the Index": if the amount of documents is growing, the indexing is moving along.

If the indexing gets stuck, something's wrong. I've had trouble with some plugins, for example Flowplayer video player stopped indexing. I had to disable the plugin, index and then activate the plugin again. Try disabling plugins, especially those that use shortcodes, to see if that helps. Relevanssi shows the highest post ID in the index - start troubleshooting from the post or page with the next highest ID. Server error logs may be useful, too.

= Using custom search results =
If you want to use the custom search results, make sure your search results template uses `the_excerpt()` to display the entries, because the plugin creates the custom snippet by replacing the post excerpt.

If you're using a plugin that affects excerpts (like Advanced Excerpt), you may run into some problems. For those cases, I've included the function `relevanssi_the_excerpt()`, which you can use instead of `the_excerpt()`. It prints out the excerpt, but doesn't apply `wp_trim_excerpt()` filters (it does apply `the_content()`, `the_excerpt()`, and `get_the_excerpt()` filters).

To avoid trouble, use the function like this:

`<?php if (function_exists('relevanssi_the_excerpt')) { relevanssi_the_excerpt(); }; ?>`

See Frequently Asked Questions for more instructions on what you can do with Relevanssi.

= The advanced hacker option =
If you're doing something unusual with your search and Relevanssi doesn't work, try using `relevanssi_do_query()`. See [Knowledge Base](http://www.relevanssi.com/knowledge-base/relevanssi_do_query/).

= Uninstalling =
To uninstall the plugin remove the plugin using the normal WordPress plugin management tools (from the Plugins page, first Deactivate, then Delete). If you remove the plugin files manually, the database tables and options will remain.

= Combining with other plugins =
Relevanssi doesn't work with plugins that rely on standard WP search. Those plugins want to access the MySQL queries, for example. That won't do with Relevanssi. [Search Light](http://wordpress.org/extend/plugins/search-light/), for example, won't work with Relevanssi.

Some plugins cause problems when indexing documents. These are generally plugins that use shortcodes to do something somewhat complicated. One such plugin is [MapPress Easy Google Maps](http://wordpress.org/extend/plugins/mappress-google-maps-for-wordpress/). When indexing, you'll get a white screen. To fix the problem, disable either the offending plugin or shortcode expansion in Relevanssi while indexing. After indexing, you can activate the plugin again.

== Frequently Asked Questions ==

= Where is the Relevanssi search box widget? =
There is no Relevanssi search box widget.

Just use the standard search box.

= Where are the user search logs? =
See the top of the admin menu. There's 'User searches'. There. If the logs are empty, please note showing the results needs at least MySQL 5.

= Displaying the number of search results found =

The typical solution to showing the number of search results found does not work with Relevanssi. However, there's a solution that's much easier: the number of search results is stored in a variable within $wp_query. Just add the following code to your search results template:

`<?php echo 'Relevanssi found ' . $wp_query->found_posts . ' hits'; ?>`

= Advanced search result filtering =

If you want to add extra filters to the search results, you can add them using a hook. Relevanssi searches for results in the _relevanssi table, where terms and post_ids are listed. The various filtering methods work by listing either allowed or forbidden post ids in the query WHERE clause. Using the `relevanssi_where` hook you can add your own restrictions to the WHERE clause.

These restrictions must be in the general format of ` AND doc IN (' . {a list of post ids, which could be a subquery} . ')`

For more details, see where the filter is applied in the `relevanssi_search()` function. This is stricly an advanced hacker option for those people who're used to using filters and MySQL WHERE clauses and it is possible to break the search results completely by doing something wrong here.

There's another filter hook, `relevanssi_hits_filter`, which lets you modify the hits directly. The filter passes an array, where index 0 gives the list of hits in the form of an array of post objects and index 1 has the search query as a string. The filter expects you to return an array containing the array of post objects in index 0 (`return array($your_processed_hit_array)`).

= Direct access to query engine =
Relevanssi can't be used in any situation, because it checks the presence of search with the `is_search()` function. This causes some unfortunate limitations and reduces the general usability of the plugin.

You can now access the query engine directly. There's a new function `relevanssi_do_query()`, which can be used to do search queries just about anywhere. The function takes a WP_Query object as a parameter, so you need to store all the search parameters in the object (for example, put the search terms in `$your_query_object->query_vars['s']`). Then just pass the WP_Query object to Relevanssi with `relevanssi_do_query($your_wp_query_object);`.

Relevanssi will process the query and insert the found posts as `$your_query_object->posts`. The query object is passed as reference and modified directly, so there's no return value. The posts array will contain all results that are found.

= Sorting search results =
If you want something else than relevancy ranking, you can use orderby and order parameters. Orderby accepts $post variable attributes and order can be "asc" or "desc". The most relevant attributes here are most likely "post_date" and "comment_count".

If you want to give your users the ability to sort search results by date, you can just add a link to http://www.yourblogdomain.com/?s=search-term&orderby=post_date&order=desc to your search result page.

Order by relevance is either orderby=relevance or no orderby parameter at all.

= Filtering results by date =
You can specify date limits on searches with `by_date` search parameter. You can use it your search result page like this: http://www.yourblogdomain.com/?s=search-term&by_date=1d to offer your visitor the ability to restrict their search to certain time limit (see [RAPLIQ](http://www.rapliq.org/) for a working example).

The date range is always back from the current date and time. Possible units are hour (h), day (d), week (w), month (m) and year (y). So, to see only posts from past week, you could use by_date=7d or by_date=1w.

Using wrong letters for units or impossible date ranges will lead to either defaulting to date or no results at all, depending on case.

Thanks to Charles St-Pierre for the idea.

= Displaying the relevance score =
Relevanssi stores the relevance score it uses to sort results in the $post variable. Just add something like

`echo $post->relevance_score`

to your search results template inside a PHP code block to display the relevance score.

= Did you mean? suggestions =
To use Google-style "did you mean?" suggestions, first enable search query logging. The suggestions are based on logged queries, so without good base of logged queries, the suggestions will be odd and not very useful.

To use the suggestions, add the following line to your search result template, preferably before the have_posts() check:

`<?php if (function_exists('relevanssi_didyoumean')) { relevanssi_didyoumean(get_search_query(), "<p>Did you mean: ", "?</p>", 5); }?>`

The first parameter passes the search term, the second is the text before the result, the third is the text after the result and the number is the amount of search results necessary to not show suggestions. With the default value of 5, suggestions are not shown if the search returns more than 5 hits.

= Search shortcode =
Relevanssi also adds a shortcode to help making links to search results. That way users can easily find more information about a given subject from your blog. The syntax is simple:

`[search]John Doe[/search]`

This will make the text John Doe a link to search results for John Doe. In case you want to link to some other search term than the anchor text (necessary in languages like Finnish), you can use:

`[search term="John Doe"]Mr. John Doe[/search]`

Now the search will be for John Doe, but the anchor says Mr. John Doe.

One more parameter: setting `[search phrase="on"]` will wrap the search term in quotation marks, making it a phrase. This can be useful in some cases.

= Restricting searches to categories and tags =
Relevanssi supports the hidden input field `cat` to restrict searches to certain categories (or tags, since those are pretty much the same). Just add a hidden input field named `cat` in your search form and list the desired category or tag IDs in the `value` field - positive numbers include those categories and tags, negative numbers exclude them.

This input field can only take one category or tag id (a restriction caused by WordPress, not Relevanssi). If you need more, use `cats` and use a comma-separated list of category IDs.

You can also set the restriction from general plugin settings (and then override it in individual search forms with the special field). This works with custom taxonomies as well, just replace `cat` with the name of your taxonomy.

If you want to restrict the search to categories using a dropdown box on the search form, use a code like this:

`<form method="get" action="<?php bloginfo('url'); ?>">
	<div><label class="screen-reader-text" for="s">Search</label>
	<input type="text" value="<?php the_search_query(); ?>" name="s" id="s" />
<?php
	wp_dropdown_categories(array('show_option_all' => 'All categories'));
?>
	<input type="submit" id="searchsubmit" value="Search" />
	</div>
</form>`

This produces a search form with a dropdown box for categories. Do note that this code won't work when placed in a Text widget: either place it directly in the template or use a PHP widget plugin to get a widget that can execute PHP code.

= Restricting searches with taxonomies =

You can use taxonomies to restrict search results to posts and pages tagged with a certain taxonomy term. If you have a custom taxonomy of "People" and want to search entries tagged "John" in this taxonomy, just use `?s=keyword&people=John` in the URL. You should be able to use an input field in the search form to do this, as well - just name the input field with the name of the taxonomy you want to use.

It's also possible to do a dropdown for custom taxonomies, using the same function. Just adjust the arguments like this:

`wp_dropdown_categories(array('show_option_all' => 'All people', 'name' => 'people', 'taxonomy' => 'people'));`

This would do a dropdown box for the "People" taxonomy. The 'name' must be the keyword used in the URL, while 'taxonomy' has the name of the taxonomy.

= Automatic indexing =
Relevanssi indexes changes in documents as soon as they happen. However, changes in shortcoded content won't be registered automatically. If you use lots of shortcodes and dynamic content, you may want to add extra indexing. Here's how to do it:

`if (!wp_next_scheduled('relevanssi_build_index')) {
	wp_schedule_event( time(), 'daily', 'relevanssi_build_index' );
}`

Add the code above in your theme functions.php file so it gets executed. This will cause WordPress to build the index once a day. This is an untested and unsupported feature that may cause trouble and corrupt index if your database is large, so use at your own risk. This was presented at [forum](http://wordpress.org/support/topic/plugin-relevanssi-a-better-search-relevanssi-chron-indexing?replies=2).

= Highlighting terms =
Relevanssi search term highlighting can be used outside search results. You can access the search term highlighting function directly. This can be used for example to highlight search terms in structured search result data that comes from custom fields and isn't normally highlighted by Relevanssi.

Just pass the content you want highlighted through `relevanssi_highlight_terms()` function. The content to highlight is the first parameter, the search query the second. The content with highlights is then returned by the function. Use it like this:

`if (function_exists('relevanssi_highlight_terms')) {
    echo relevanssi_highlight_terms($content, get_search_query());
}
else { echo $content; }`

= Multisite searching =
To search multiple blogs in the same WordPress network, use the `searchblogs` argument. You can add a hidden input field, for example. List the desired blog ids as the value. For example, searchblogs=1,2,3 would search blogs 1, 2, and 3.

The features are very limited in the multiblog search, none of the advanced filtering works, and there'll probably be fairly serious performance issues if searching common words from multiple blogs.

= What is tf * idf weighing? =

It's the basic weighing scheme used in information retrieval. Tf stands for *term frequency* while idf is *inverted document frequency*. Term frequency is simply the number of times the term appears in a document, while document frequency is the number of documents in the database where the term appears.

Thus, the weight of the word for a document increases the more often it appears in the document and the less often it appears in other documents.

= What are stop words? =

Each document database is full of useless words. All the little words that appear in just about every document are completely useless for information retrieval purposes. Basically, their inverted document frequency is really low, so they never have much power in matching. Also, removing those words helps to make the index smaller and searching faster.

== Known issues and To-do's ==
* Known issue: In general, multiple Loops on the search page may cause surprising results. Please make sure the actual search results are the first loop.
* Known issue: Relevanssi doesn't necessarily play nice with plugins that modify the excerpt. If you're having problems, try using relevanssi_the_excerpt() instead of the_excerpt().
* Known issue: When a tag is removed, Relevanssi index isn't updated until the post is indexed again.

== Thanks ==
* Cristian Damm for tag indexing, comment indexing, post/page exclusion and general helpfulness.
* Marcus Dalgren for UTF-8 fixing.
* Warren Tape.
* Mohib Ebrahim for relentless bug hunting.
* John Blackbourn for amazing internal link feature and other fixes.
* John Calahan for extensive 2.0 beta testing.

== Changelog ==
= 2.16.5 =
* Security fix: Extra hardening for AJAX requests. Some AJAX actions in Relevanssi could leak information to site subscribers who knew what to look for.

= 2.16.4 =
* Security fix: Any registered user could empty the Relevanssi index by triggering the index truncate AJAX action. That is no longer possible.
* New feature: The [searchform] shortcode has a new parameter, 'checklist', which you can use to create taxonomy checklists.
* New feature: New filter hook `relevanssi_post_type_archive_ok` allows controlling whether individual post type archives are indexed or not.
* New feature: You can now set the API key with the constant RELEVANSSI_API_KEY. If the constant is set, the API key settings disappear on the Relevanssi settings page.
* Changed behaviour: The `relevanssi_related_output_objects` filter hook has been removed. It was unnecessary: it simply isn't useful to filter a function return value, because you can modify it without a filter hook.
* Changed behaviour: The `relevanssi_search_form` filter hook has an additional parameter which has the shortcode attributes.
* Changed behaviour: The `relevanssi_search_again` parameter array has more parameters the filter can modify.
* Changed behaviour: The `relevanssi_show_matches` filter hook gets the post object as the second parameter.
* Changed behaviour: The `relevanssi_term_add_data` filter hook now runs also when individual terms are modified. Previously it only run when all terms were indexed.
* Translations: Relevanssi Premium is now professionally translated to German and Spanish.

= 2.16.3 =
* Security fix: User searches page had a XSS vulnerability.
* Changed behaviour: Click tracking is no longer added to links if the user is on the logging block list or a bot.
* Minor fix: `relevanssi_orderby` did not always accept an array-format orderby parameter.
* Minor fix: Removes a highlighting problem stemming from uppercase search terms.
* Minor fix: When image attachment indexing was disabled, saving image attachments would still index the images. Image attachment blocking is now a `relevanssi_indexing_restriction` filter function, which means it's always active.
* Minor fix: Enabling click tracking doesn't break anchor links anymore.
* Minor fix: Relevanssi removes highlights better from inside multiline HTML tags.

= 2.16.2 =
* Minor fix: Remove unnecessary database calls from admin pages.
* Minor fix: Improved Oxygen compatibility.

= 2.16.1 =
* Fixes an error on the post query insights screen.

= 2.16.0 =
* New feature: Click tracking lets you track the way the users click different posts from the search results pages. Enable the click tracking from the Logging settings to see it in effect.
* New feature: Proximity sorting lets you sort posts by geographical distance. See the knowledge base for details on how this works.
* New feature: New filter hook `relevanssi_render_blocks` controls whether Relevanssi renders blocks in a post or not. If you are having problems updating long posts with lots of blocks, having this filter hook return `false` for the post in question will likely help, as rendering the blocks in a long post can take huge amounts of memory.
* New feature: The [searchform] shortcode has a new parameter, 'post_type_boxes', which creates a checkbox for each post type you list in the value. For example [searchform post_type_boxes='*post,page'] would create a search with a checkbox for 'post' and 'page' post types, with 'post' pre-checked.
* New feature: You can now have multiple dropdowns in one [searchform] shortcode. Anything that begins with 'dropdown' is considered a dropdown parameter, so you can do [searchform dropdown_1='category' dropdown_2='post_tag'] for example.
* New feature: New filter hook `relevanssi_search_params` lets you filter search parameters after they've been collected from the WP_Query.
* New feature: New filter hook `relevanssi_excerpt_post` lets you make Relevanssi skip creating excerpts for specific posts.
* Changed behaviour: Redirect queries now support regular expressions.
* Changed behaviour: Filter hooks `relevanssi_1day`, `relevanssi_7days` and `relevanssi_30days` are removed, as the user searches page is now different. The default value for `relevanssi_user_searches_limit` is now 100 instead of 20.
* Minor fix: Stopwords weren't included in the exported options; they are now.
* Minor fix: Relevanssi won't let you adjust synonyms and stopwords anymore if you use Polylang and are in 'Show all languages' mode.
* Minor fix: New parameter for `relevanssi_tokenize()` introduces the context (indexing or search query). The `relevanssi_extract_phrases()` is only used on search queries.
* Minor fix: When you deactivate the Related posts feature from the settings, the Related post caches for posts are flushed.
* Minor fix: In some languages, iOS uses „“ for quotes. Relevanssi now understands those for the phrase operator.
* Minor fix: You can now choose 'user' post type in the admin search post type dropdown.
* Minor fix: The debugging page now lets you debug user profiles and taxonomy terms.
* Minor fix: Highlighting is improved by a more precise HTML entity filter, thanks to Jacob Bearce.
* Minor fix: Fixes problems with the WP-Members compatibility.
* Minor fix: The `relevanssi_premium_tokenizer` filter hook now gets the context (indexing or searching) as a parameter.
* Minor fix: Stops Relevanssi from blocking the admin search for WooCommerce coupons and other WooCommerce custom post types.
* Minor fix: Searching for a stemmed word with the AND search didn't find correct results.

= 2.15.3.1 =
* Minor fix: The Bricks compatibility was broken, this version fixes it.

= 2.15.3 =
* New feature: You can add a post type dropdown to [searchform] forms with `dropdown='post_type'`.
* New feature: New filter hook `relevanssi_sku_boost` controls the WooCommerce `_sku` field weight boost.
* New feature: New filter hook `relevanssi_related_posts_cache_id` lets you change the Related posts transient cache ID so allow multiple cached related posts per post.
* New feature: New filter hook `relevanssi_post_to_excerpt` lets you filter the post object before an excerpt is created from it.
* New feature: New filter hook `relevanssi_custom_fields_before_repeaters` filters the list of custom fields before repeater fields are processed, so you can add fields from code using the field_%_subfield notation.
* New feature: Relevanssi is now compatible with the Bricks page builder theme (requires Bricks 1.3.2).
* Changed behaviour: The spam block now returns a 410 Gone status code for blocked pages.
* Changed behaviour: The minimum capability for seeing the Gutenberg sidebar or the Relevanssi meta box is changed from 'manage_options' to 'edit_others_posts' in order to allow editors see the sidebar and the meta box. If you prefer the original way, use the `relevanssi_sidebar_capability` filter hook to adjust.
* Minor fix: Relevanssi removes HTML comments better from the post content.
* Minor fix: Sometimes the Did you mean would return really weird long suggestions from the search logs. That won't happen anymore.
* Minor fix: Oxygen compatibility has been improved. Rich text fields and updating posts when they are saved in Oxygen now work better, and revisions are no longer indexed.
* Minor fix: Improves tax_query handling in fringe cases with multiple AND clauses joined together with OR.
* Minor fix: It's now possible to override global multisite search settings from the `searchblogs` query variable.
* Minor fix: Searching without a search term works much better now, you get more posts in the results (default value is up to 500).

= 2.15.2 =
* New feature: Adds support for Avada Live Search.
* New feature: Adds support for Fibo Search.
* New feature: Spam blocking can be used to block bots from accessing your search results pages.
* Changed behaviour: The filter hook `relevanssi_indexing_terms` that appeared in post type archive indexing is renamed to `relevanssi_indexing_tokens` what it should've been to begin with.
* Minor fix: Elementor library searches are not broken anymore when Relevanssi is enabled in admin.
* Minor fix: Relevanssi now understands array-style post_type[] parameters.
* Minor fix: The MySQL column detail information has been missing from the index.
* Minor fix: Spam blocking now works with pretty search page URLs and not just with ?s= URLs. You can use the new filter hook `relevanssi_search_url_prefix` to adjust the prefix in case it's not `/search/`. Spam blocking is also extended to page views with spam content in the `highlight` parameter.
* Minor fix: Relevanssi now automatically considers the Turkish 'ı' the same as 'i'.

= 2.15.1 =
* New feature: New action hooks `relevanssi_disable_stemmer` and `relevanssi_enable_stemmer`. Relevanssi-compatible stemmers should implement these action hooks: the first should disable the stemmer and the second should enable it.
* New feature: Adds compatibility for WP-Members plugin, preventing blocked posts from showing up in the search results.
* New feature: New function `relevanssi_get_attachment_suffix()` can be used to return the attachment file suffix based on a post object or a post ID.
* Major fix: Fixes the broken Relevanssi controls on block editor post edit pages.
* Minor fix: Improved the Missing terms feature when used with stemming. This fix requires updating Snowball Stemmer to version 1.3.
* Minor fix: Improves the Oxygen compatibility. Now also the [oxygen] shortcode tags are removed.

= 2.15.0 =
* New feature: Relevanssi can now add Google-style missing term lists to the search results. You can either use the `%missing%` tag in the search results breakdown settings, or you can create your own code: the missing terms are also in `$post->missing_terms`. Relevanssi Premium will also add "Must have" links when there's just one missing term.
* New feature: New filter hook `relevanssi_missing_terms_tag` controls which tag is used to wrap the missing terms.
* New feature: New filter hook `relevanssi_missing_terms_template` can be used to filter the template used to display the missing terms.
* New feature: New filter hook `relevanssi_missing_terms_must_have` filters the 'Must have' part of the missing terms element.
* New feature: New filter hook `relevanssi_phrase` filters each phrase before it's used in the MySQL query.
* New feature: New filter hook `relevanssi_multi_results`, which is the same as `relevanssi_results`, but is applied to multisite results, so instead of a post ID, it has 'blog ID|post ID' in the keys and as usual the post weight in the value.
* New feature: New filter hook `relevanssi_site_results`, which is the same as `relevanssi_results`, but only applied in single site results in multisite searching (ie. the filter is applied once for each subsite included in the results).
* New feature: New filter hook `relevanssi_post_author` lets you filter the post author display_name before it is indexed.
* New feature: New function `relevanssi_get_post_meta_for_all_posts()` can be used to fetch particular meta field for a number of posts with just one query.
* New feature: Relevanssi now has a keyword-based spam blocking feature to stop spam searches as soon as possible.
* New feature: The `fields` parameter can be set to `id=>type`, which returns post ID and the post type (post, user, taxonomy term), providing support for non-post results. This only works when using `relevanssi_do_query()` to run the query.
* Changed behaviour: `relevanssi_strip_tags()` used to add spaces between HTML tags before stripping them. It no longer does that, but instead adds a space after specific list of tags (p, br, h1-h6, div, blockquote, hr, li, img) to avoid words being stuck to each other in excerpts.
* Changed behaviour: Relevanssi now indexes the contents of Oxygen Builder PHP & HTML code blocks.
* Changed behaviour: Relevanssi now handles synonyms inside phrases differently. If the new filter hook `relevanssi_phrase_synonyms` returns `true` (default value), synonyms create a new phrase (with synonym 'dog=hound', phrase `"dog biscuits"` becomes `"dog biscuits" "hound biscuits"`). If the value is `false`, synonyms inside phrases are ignored.
* Changed behaviour: Multisite searching code has been refactored, and at the same time new features have been added. It is now possible to use date parameters and synonyms in multisite searching.
* Minor fix: Attachments that cause the reading server run out of memory are now labeled with the "File size too large error".
* Minor fix: Multisite searches were not logged. Now they are.
* Minor fix: Warnings when creating excerpts with search terms that contain a slash were removed.
* Minor fix: Better Ninja Tables compatibility to avoid problems with lightbox images.
* Minor fix: Trying to open the Relevanssi sidebar in Gutenberg when a post type did not support custom fields caused a crash. Now the sidebar is simply disabled if the post type does not support custom fields.
* Minor fix: Relevanssi did not work well in the Media Library grid view. Relevanssi is now blocked there. If you need Relevanssi in Media Library searches, use the list view.
* Minor fix: Relevanssi excerpt creation didn't work correctly when numerical search terms were used.

= 2.14.5 =
* New feature: New WP CLI command `wp relevanssi remove_attachment_errors` clears out all attachment reading errors.
* Changed behaviour: `relevanssi_excerpt_custom_field_content` now gets the post ID and list of custom field names as a parameter.
* Changed behaviour: Attachments tab will now prevent reading the attachments if the options have been changed and aren't saved.
* Changed behaviour: Instead of setting the attachment reading server to 'us', Relevanssi install process will now guess whether 'eu' would be a better option based on the site locale.
* Minor fix: Avoids admin ajax request flooding when removing lots of posts at once.
* Minor fix: Adds trailing slash to the blog URL in Did you mean links.
* Minor fix: When the contents for an attached attachment are read, Relevanssi will now automatically index the parent post if the setting is enabled.

= 2.14.4 =
* New feature: New action hooks `relevanssi_pre_the_content` and `relevanssi_post_the_content` fire before and after Relevanssi applies `the_content` filter to the post excerpts. Some Relevanssi default behaviour has been moved to these hooks so it can be modified.
* Changed behaviour: The `relevanssi_do_not_index` gets the post object as a third parameter.
* Minor fix: Remove errors from `relevanssi_strip_all_tags()` getting a `null` parameter.
* Minor fix: Updating posts still used `relevanssi_update_doc_count()`, which can sometimes be really slow.
* Minor fix: Corrected misleading instructions about indexing AND synonyms.

= 2.14.3 =
* Major fix: Post type weights did not work; improving the caching had broken them.
* Minor fix: 'Read all unread attachments' did not include 'key is not valid' errors. Now it rereads those attachments.
* Minor fix: Stops indexing error messages in WPML.
* Minor fix: Synonyms are now used for highlighting titles in AND searches if 'Index synonyms for AND searches' is enabled.
* Minor fix: Relevanssi works better with soft hyphens now, removing them in indexing and excerpt-building.

= 2.14.2 =
* Major fix: Stops more problems with ACF custom field indexing.
* Major fix: Fixes a bug in search result caching that caused Relevanssi to make lots of unnecessary database queries.
* Minor fix: Pinning didn't work correctly when the post content indexing was disabled. Now the pinned words are included in the post title, if the post content is not available.

= 2.14.1 =
* Major fix: Stops TypeError crashes from null custom field indexing.

= 2.14.0 =
* New feature: New filter hook `relevanssi_excerpt_gap` lets you adjust the first line of excerpt optimization.
* New feature: Phrase matching now works also for taxonomy terms and user profiles.
* New feature: New filter hook `relevanssi_phrase_queries` can be used to add phrase matching queries to support more content types.
* New feature: New WP CLI command `wp relevanssi refresh` reindexes all posts (and only posts) without truncating the index first. This is very useful for regular reindexing of production sites, as the search won't stop working during the reindexing.
* New feature: New function `relevanssi_update_words_option()` can be used to update the `relevanssi_words` option directly, in case the AJAX update action fails for some reason.
* New feature: You can now reset the `relevanssi_words` cache option from the Relevanssi debugging settings tab.
* Changed behaviour: The `relevanssi_tag_before_tokenize` filter hook parameters were changed in order to be actually useful and to match what the filter hook is supposed to do.
* Changed behaviour: Relevanssi now automatically optimizes excerpt creation in long posts. You can still use `relevanssi_optimize_excerpts` for further optimization, but it's probably not necessary.
* Changed behaviour: The `relevanssi_admin_search_element` filter hook now gets the post object as the second parameter, rendering the filter hook more useful.
* Minor fix: WPML couldn't digest post type archives in the search results. Relevanssi now handles that and also takes errors from WPML more gracefully.
* Minor fix: Taxonomy terms in WPML were not indexed correctly. Instead of the post language, the current language was used, so if your admin dashboard is in English, German posts would get English translations of the terms, not German. This is now fixed.
* Minor fix: Excerpt creation is now faster when multiple excerpts are not used.
* Minor fix: The SEO plugin noindex setting did not actually work. That has been fixed now.
* Minor fix: Multisite searching didn't work correctly in HyperDB environments.
* Minor fix: Improved fringe cases in nested taxonomy queries.
* Minor fix: Indexing would remove content where less than or greater than symbols were interpreted as HTML tags.
* Minor fix: In some cases Relevanssi wouldn't highlight the last word of the title. This is more reliable now.
* Minor fix: Relevanssi will now add the `highlight` parameter only to search results, and not to other links on the search results page.
* Minor fix: Disables stemming for words that are inside phrases to make post part targeted searches more precise.

= 2.13.1 =
* Major fix: User and taxonomy term search did not work correctly, thanks to a complicated mix of small issues that didn't show up in the automated testing. The problem was caused be `relevanssi_premium_get_post()` returning WP_Post objects for these non-posts, so the function is now returning stdClass objects again.
* Major fix: The type hinting introduced for some functions turned out to be too strict, causing fatal errors. The type hinting has been relaxed (using nullable types would help, but that's a PHP 7.4 feature, and we don't want that).

= 2.13.0 =
* New feature: New filter hook `relevanssi_rendered_block` filters Gutenberg block content after the block has been rendered with `render_block()`.
* New feature: New filter hook `relevanssi_log_query` can be used to filter the search query before it's logged. This can be used to log instead the query that includes synonyms (available as a parameter to the filter hook).
* New feature: New filter hook `relevanssi_add_all_results` can be used to make Relevanssi add a list of all result IDs found to `$query->relevanssi_all_results`. Just make this hook return `true`.
* New feature: New filter hook `relevanssi_acceptable_hooks` can be used to adjust where in WP admin the Relevanssi admin javascripts are enqueued.
* New feature: Support for All-in-One SEO. Posts marked as 'Robots No Index' are not indexed by Relevanssi.
* New feature: New setting in advanced indexing settings to control whether Relevanssi respects the SEO plugin 'noindex' setting or not.
* Changed behaviour: Type hinting has been added to Relevanssi functions, which may cause errors if the filter functions are sloppy with data types.
* Changed behaviour: Relevanssi no longer logs queries with the added synonyms. You can use the `relevanssi_log_query` filter hook to return to the previous behaviour of logging the synonyms too. Thanks to Jan Willem Oostendorp.
* Changed behaviour: `relevanssi_the_title()` now supports the same parameters as `the_title()`, so you can just replace `the_title()` with it and keep everything else the same. The old behaviour is still supported.
* Changed behaviour: When using ACF and custom fields indexing set to 'all', Relevanssi will no longer index the meta fields (where the content begins with `field_`).
* Minor fix: In some cases, having less than or greater than symbols in PDF content would block that PDF content from being indexed.
* Minor fix: PDF content wasn't being indexed in some cases where custom field indexing was otherwise disabled.
* Minor fix: The Oxygen compatibility made it impossible to index other custom fields than the Oxygen `ct_builder_shortcodes`. This has been improved now.
* Minor fix: In Related posts, random posts from the same category could include duplicates of posts in the related posts.
* Minor fix: Old legacy scripts that caused Javascript warnings on admin pages have been removed.
* Minor fix: relevanssi_premium_get_post() always returns WP_Post objects now, never stdClass objects.
* Minor fix: The search results log export did not do anything useful when no data was found. Now the export provides a message "No search keywords logged". Thanks to Jan Willem Oostendorp.

== Upgrade notice ==
= 2.16.5 =
* Security fix, extra security for AJAX actions.

= 2.16.4 =
* Security fix.

= 2.16.3 =
* No click tracking for blocked users and bots, bug fixes.

= 2.16.2 =
* Removes unnecessary database calls on admin pages.

= 2.16.1 =
* Fixes an error on the post query insights screen.

= 2.16.0 =
* Click tracking, proximity sorting, improved user searches page and bug fixes.

= 2.15.3.1 =
* Fixes the Bricks compatibility.

= 2.15.3 =
* Bug fixes, small improvements here and there.

= 2.15.2 =
* Bug fixes, updates to the spam blocking.

= 2.15.1 =
* Fixes broken block editor post controls.

= 2.15.0 =
* New features, big improvements to the multisite searching.

= 2.14.5 =
* Minor bug fixes, stops admin ajax flooding issues.

= 2.14.4 =
* Fixes minor bugs, speeds up post updates a lot in some cases.

= 2.14.3 =
* Fixes post type weights and WPML indexing problems.

= 2.14.2 =
* Stops Relevanssi from crashing when saving posts with ACF fields, major performance boost.

= 2.14.1 =
* Stops Relevanssi from crashing when saving posts.

= 2.14.0 =
* Bug fixes and new features.

= 2.13.1 =
* Fixes broken user and taxonomy term search.

= 2.13.0 =
* Bug fixes and new filter hooks.