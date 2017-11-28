<?php

$cminds_plugin_config = array(
	'plugin-is-pro'				 => TRUE,
	'plugin-has-addons'			 => TRUE,
	'plugin-addons'				 => array(
		array( 'title' => 'Tooltip Glossary Search Widget', 'description' => 'Make your glossary more accessible by adding a search widget on the bottom of your website.', 'link' => 'https://www.cminds.com/store/tooltip-glossary-search-console-widget-add-on-for-wordpress-by-creativeminds/#', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=105680&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Custom Taxonomies', 'description' => 'Add support for multiple taxonomies and filtering for the Glossary terms.', 'link' => 'https://www.cminds.com/store/tooltip-glossary-custom-taxonomies-add-on-for-wordpress-by-creativeminds/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=113609&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Skins', 'description' => 'Lets you change the tooltip shape, color, opacity and much more. It offers various improved shapes and themes for the tooltip and improves the overall user experience. It is mobile responsive.', 'link' => 'https://www.cminds.com/store/cm-tooltip-glossary-skins-cm-plugins-store/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=9644&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Log & Statistics', 'description' => 'Tracks and reports tooltip usage statistics such as number of tooltip hovers, term link clicks, inside tooltip clicks, term overall impressions, and server loads. Apply this data to your site to improve your glossary performance.', 'link' => 'https://www.cminds.com/store/cm-tooltip-glossary-log-cm-plugins-store/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=10130&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Community Terms', 'description' => 'Let users suggest new terms for your Glossary. Works for both anonymous and registered users and allows you to control which users can add new terms directly and which needs moderation', 'link' => 'https://www.cminds.com/store/cm-tooltip-glossary-community-terms-cm-plugins-store/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=11837&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Remote Import', 'description' => 'Provides an easy way to import, replicate and create an up-to-date copy of your CM Glossary across several WordPress sites or domains.', 'link' => 'https://www.cminds.com/store/cm-tooltip-glossary-remote-import-cm-plugins-store/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=12111&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'Tooltip Glossary Widgets', 'description' => 'Lets you add six new widgets to your glossary, which enhance the user experience and glossary engagement by exposing its content to users and visitors. Create visually appealing widgets to improve glossary content and user interaction.', 'link' => 'https://www.cminds.com/store/purchase-cm-tooltip-glossary-widgets-add-wordpress/', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=30457&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
		array( 'title' => 'All Glossary AddOns Bundle', 'description' => 'Includes All CM Tooltip Glossary 5 AddOns.', 'link' => '', 'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=107574&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1' ),
	),
	'plugin-show-shortcodes'	 => TRUE,
	'plugin-shortcodes'			 => '<article class="cm-shortcode-desc">
    <header>
        <h4>[glossary]</h4>
        <span>Show Glossary Index</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Parameters:</h5>
        <ul>
            <li><strong>cat</strong> - The comma separated list of categories.</li>
            <li><strong>gtags</strong> - The comma separated list of tags</li>
            <li><strong>search_term</strong> - The preselected search term.</li>
            <li><strong>itemspage</strong> - The number of items on the page. This attribute is for Server-side pagination only.</li>
            <li><strong>letter</strong> - The preselected letter on the alphabetical index.</li>
            <li><strong>related</strong> - Whether the related terms should be displayed.</li>
            <li><strong>no_desc</strong> - Whether the descriptions of terms should be hidden.</li>
            <li><strong>hide_terms</strong> - Allows to remove the regular terms from the Glossary Index.</li>
            <li><strong>hide_abbrevs</strong> - Allows to remove the abbreviations from the Glossary Index.</li>
            <li><strong>hide_synonyms</strong> - Allows to remove the synonyms from the Glossary Index.</li>
            <li><strong>glossary_index_style</strong> - The style of the Glossary Index.
                Possible values are (use the value in quotes): Classic "classic", Classic + definition "classic-definition", Classic + excerpt "classic-excerpt", Small Tiles "small-tiles", Big Tiles "big-tiles", Classic table "classic-table", Modern table "modern-table", Sidebar + term page "sidebar-termpage", Expand style "expand-style", Grid "grid", Cube "cube"</li>
            <li><strong>disable_listnav</strong> - Allows to disable the alphabetical navigation bar.</li>
        </ul>
        <h5>Example</h5>
        <p><kbd>[glossary cat="cat1,cat2" gtags="tag1,tag2" search_term="term" itemspage="1" letter="all" related="0" no_desc="0" hide_terms="0" hide_abbrevs="0" hide_synonyms="0" glossary_index_style="tiles" disable_listnav="1" ]</kbd></p>
        <p>The shows a custom glossary tooltip.</p>
    </div>
</article>

<article class="cm-shortcode-desc">
    <header>
        <h4>[glossary_tooltip]</h4>
        <span>Custom glossary tooltip</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Parameters:</h5>
        <ul>
            <li><strong>content</strong> - The content of the tooltip.</li>
            <li><strong>dashicon</strong> - Dashicon to show the tooltip instead of the term/phrase.<a href="https://developer.wordpress.org/resource/dashicons/#chart-bar">List of dashicons</a></li>
            <li><strong>color</strong> - The color of the dashicon.</li>
            <li><strong>size</strong> - The size of the dashicon.</li>
        </ul>
        <h5>Shortcode content:</h5>
        <p>The term/phrase which should display the custom tooltip.</p>
        <h5>Example</h5>
        <p><kbd>[glossary_tooltip content="text" dashicon="dashicon="dashicons-editor-help" color="#c0c0c0" size="16px"] term [/glossary_tooltip]</kbd></p>
        <p>The shows a custom glossary tooltip.</p>
    </div>
</article>

<article class="cm-shortcode-desc">
    <header>
        <h4>[glossary_exclude]</h4>
        <span>Exclude from parsing</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Example</h5>
        <p><kbd>[glossary_exclude] text [/glossary_exclude]</kbd></p>
        <p>The content inside the shortcode will be excluded from the parsing.</p>
    </div>
</article>

<article class="cm-shortcode-desc">
    <header>
        <h4>[cm_tooltip_parse]</h4>
        <span>Apply tooltip</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Example</h5>
        <p><kbd>[cm_tooltip_parse] text [/cm_tooltip_parse]</kbd></p>
        <p>
            Apply the tooltip to the text inside. Useful to force parsing in places where "the_content" filter is not being used.
        </p>
    </div>
</article>

<article class="cm-shortcode-desc">
    <header>
        <h4>[cm_tooltip_link_to_term]</h4>
        <span>Link word/phrase to the term</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Parameters:</h5>
        <ul>
            <li><strong>term</strong> - The title of the term which should be linked.</li>
        </ul>
        <h5>Example</h5>
        <p><kbd>[cm_tooltip_link_to_term term="Term Title"]Linked Word[/cm_tooltip_link_to_term]</kbd></p>
        <p>
            Display the tooltip for a word/phrase as if it was a terms own synonym/variation.
        </p>
    </div>
</article>

<article class="cm-shortcode-desc">
    <header>
        <h4>[glossary_search]</h4>
        <span>Show Glossary Search Form</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Example</h5>
        <p><kbd>[glossary_search]</kbd></p>
        <p>
            Display the form which allows to search for the glossary terms.
        </p>
    </div>
</article>

<article class="cm-shortcode-desc">
    <header>
        <h4>[glossary_dictionary]</h4>
        <span>Show Merriam-Webster Dictionary</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Parameters:</h5>
        <ul>
            <li><strong>term</strong> - The term for which the Dictionary definition should be displayed.</li>
        </ul>
        <h5>Example</h5>
        <p><kbd>[glossary_dictionary term="term name"]</kbd></p>
        <p>
            Display the Show Merriam-Webster Dictionary definition. [Ecommerce only]
        </p>
    </div>
</article>

<article class="cm-shortcode-desc">
    <header>
        <h4>[glossary_thesaurus]</h4>
        <span>Show Merriam-Webster Thesaurus</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Parameters:</h5>
        <ul>
            <li><strong>term</strong> - The term for which the Dictionary definition should be displayed.</li>
        </ul>
        <h5>Example</h5>
        <p><kbd>[glossary_thesaurus term="term name"]</kbd></p>
        <p>
            Display the Show Merriam-Webster Thesaurus definition. [Ecommerce only]
        </p>
    </div>
</article>

<article class="cm-shortcode-desc">
    <header>
        <h4>[glossary_translate]</h4>
        <span>Translate</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Parameters:</h5>
        <ul>
            <li><strong>term</strong> - The term for which the Dictionary definition should be displayed.</li>
            <li><strong>source</strong> - The term for which the Dictionary definition should be displayed.</li>
            <li><strong>target</strong> - The term for which the Dictionary definition should be displayed.</li>
        </ul>
        <h5>Example</h5>
        <p><kbd>[glossary_translate term="text-to-translate" source="english" target="spanish"]</kbd></p>
        <p>
            Display the Google Translated definition of the term. [Ecommerce only]
        </p>
    </div>
</article>

<article class="cm-shortcode-desc">
    <header>
        <h4>[glossary-toogle-tooltips]</h4>
        <span>Toggle Tooltips</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Parameters:</h5>
        <ul>
            <li><strong>session</strong> - Whether the result of the shortcode should be persisted in the session. Defaults to 0 (not persisted).</li>
        </ul>
        <h5>Example</h5>
        <p><kbd>[glossary-toogle-tooltips session="0"]</kbd></p>
        <p>
            Display the button allowing to temporarily disable the tooltips on given page.
        </p>
    </div>
</article>

<article class="cm-shortcode-desc">
    <header>
        <h4>[glossary-toggle-theme]</h4>
        <span>Toggle Theme</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Parameters:</h5>
        <ul>
            <li><strong>label</strong> - Choose the label for the theme.</li>
            <li><strong>class</strong> - Choose the class for the theme.</li>
        </ul>
        <h5>Example</h5>
        <p><kbd>[glossary-toggle-theme label="Test theme" class="test"]</kbd></p>
        <p>
            Displays a selection allowing to change the class of the tooltips on given page.
        </p>
    </div>
</article>

<article class="cm-shortcode-desc">
    <header>
        <h4>[glossary_wikipedia]</h4>
        <span>Wikipedia</span>
    </header>
    <div class="cm-shortcode-desc-inner">
        <h5>Parameters:</h5>
        <ul>
            <li><strong>term</strong> - The term for which the Wikipedia term should be displayed.</li>
        </ul>
        <h5>Example</h5>
        <p><kbd>[glossary_wikipedia term="term name"]</kbd></p>
        <p>
            Displays the Wikipedia definition of the term. [Ecommerce only]
        </p>
    </div>
</article>',
	'plugin-shortcodes-action'	 => 'cmtt_glossary_supported_shortcodes',
	'plugin-version'			 => '3.6.1',
	'plugin-abbrev'				 => 'cmtt',
	'plugin-short-slug'			 => 'cmtooltip',
	'plugin-parent-short-slug'	 => '',
	'plugin-settings-url'		 => admin_url( 'admin.php?page=cmtt_settings' ),
	'plugin-show-guide'			 => FALSE,
	'plugin-guide-text'			 => '<p>
										The description of the plugin goes here
									</p>',
	'plugin-guide-video-height'	 => 180,
	'plugin-guide-videos'		 => array(
		array( 'title' => 'Free Version Installation Tutorial', 'video_id' => '157868636' ),
	),
	'plugin-file'				 => CMTT_PLUGIN_FILE,
	'plugin-dir-path'			 => plugin_dir_path( CMTT_PLUGIN_FILE ),
	'plugin-dir-url'			 => plugin_dir_url( CMTT_PLUGIN_FILE ),
	'plugin-basename'			 => plugin_basename( CMTT_PLUGIN_FILE ),
	'plugin-icon'				 => '',
	'plugin-name'				 => CMTT_NAME,
	'plugin-license-name'		 => CMTT_CANONICAL_NAME,
	'plugin-slug'				 => '',
	'plugin-menu-item'			 => CMTT_MENU_OPTION,
	'plugin-textdomain'			 => CMTT_SLUG_NAME,
	'plugin-userguide-key'		 => '6-cm-tooltip',
	'plugin-store-url'			 => 'https://www.cminds.com/store/tooltipglossary/',
	'plugin-review-url'			 => 'https://wordpress.org/support/view/plugin-reviews/enhanced-tooltipglossary',
	'plugin-changelog-url'		 => CMTT_RELEASE_NOTES,
	'plugin-licensing-aliases'	 => array( CMTT_LICENSE_NAME ),
);
