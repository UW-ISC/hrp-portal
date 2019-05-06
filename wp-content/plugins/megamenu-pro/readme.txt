= 1.7.1 =

* New: "Sticky on desktop" option added, making it possible to have a sticky menu on mobile only
* New: "Sticky: Expand on Mobile" option added, making it possible to differentiate between desktop and mobile when expanding the sticky menu
* Improvement: Validate logo width and height settings
* Fix: Allow shortcodes in Search toggle block placeholder
* Fix: Account for top and bottom border widths when setting heights for tabbed sub menus
* Fix: Output "Title Attribute" field on logo menu items
* Fix: Hide arrow on accordion menus, when sub menu is open
* Fix: Style overrides: Apply hover styling to 'mega-current-page-ancestor' items
* Fix: Transition sticky menu height when menu becomes unstuck
* Fix: Style overrides: Apply icon hover color when sub menu is open
* Improvement: Update plugin updater class

= 1.7 =

Update Instructions: Update Max Mega Menu (free) to v2.5+. Update Max Mega Menu Pro. Go to Mega Menu > Tools and Clear the CSS Cache.

* New: Make arrow indicators clickable on Accordion menus
* Change: Apply sticky "hide until scroll up" option to mobile menus (only when the mobile menu is collapsed)
* Improvement: Validate custom styling options - make sure text inputs are valid before submitting form
* Fix: "Hide arrow" on tab menu items not working
* Fix: Tab menu heights when using jQuery selector to set sub menu width
* Fix: Ensure menu theme CSS can still compile even when leaving toggle block options empty
* Fix: Elementor header builder fails to load when using WooCommerce replacement type/shortcode
* Fix: Auto expand accordion sub menus when menu item has 'mega-current-menu-parent' class
* Fix: Do not apply 600px default sub menu width on vertical menus on mobile

= 1.6.6 =

* Fix: Mobile menu jumping when sticky and expand background is set to false
* Fix: PHP Notice when roles restriction has been set to "By Role" but no roles have been selected
* Fix: Tab positioning when mega menu sub menu inner width has been set to a jQuery/CSS selector
* Fix: Accessibility: Add aria-label attribute to search replacement & search toggle block
* Fix: Change the default value of the logo toggle block max height to 40ox (instead of 100%)
* Fix: Adjustments to tabbed menu JavaScript
* Improvement: Use set_url_scheme() on logo block URL to work around incorrectly configured databases when site has switched to use HTTPS
* Improvement: Remove reference to Toggle Block IDs in CSS, only use the toggle block class
* Improvement: Update list of Google Fonts
* Improvement: Styling for search replacement in vertical/accordion menus updated
* Change: Stick menu on document.load() rather than document.ready() (to allow dashicons to load before sticking the menu)

= 1.6.5 =

* Fix: Potential residual styling on search toggle block
* Fix: Media File Selector missing from logo/icon toggle block
* Fix: Only apply srcset to logo images if 2x size exists
* Improvement: Sticky menu: Add Support for "Bottom Admin Bar" plugin
* Improvement: Only submit search form when text is entered into the search field (static search)
* Improvement: For clarity, hide "Hide Until Search Up" related sticky options until "Hide Until Search Up" option has been selected

= 1.6.4 =

13 November 2017

* Fix: JavaScript error/incompatiblity with the HTML Editor in the "Replacements" tab. Introduced due to an update in core WordPress 4.9.
* Fix: Style override font color when sub menu is open
* Fix: Add WPML language input to search form
* Fix: Logo max height in mobile menu
* Fix: Stop vertical sticky menus from covering page content and making links/text inaccessible
* Fix: Stop "hide until scroll up" sticky functionality being applied to mobile menu
* Fix: Roles: Remove sub menu arrow indicator when children have been removed from the menu due to role restrictions
* Fix: Replace jQuery 'live' with 'on'
* New: Custom Icon Size for mobile menu setting added
* New: Sticky menu height option in theme editor
* New: Search replacement search icon can now be selected from the menu item icon screen
* New: Enable Grid Layout for tabbed sub menus (requires Max Mega Menu 2.4)
* Improvement: Allow shortcodes in toggle block logo URL
* Improvement: Add 2x retina support for logo images

= 1.6.3 =

04 July 2017

* Fix: Sub menu width override on vertical menus
* Fix: Sticky item option not saving
* Fix: Custom icon hover option not applied when sub menu is opened
* Fix: Mobile sub menu indicator for hidden tabbed sub menus
* Fix: Style overrides - icon hover color when "Highlight active item" is enabled
* Fix: WooCommerce cart count doesn't show tax when "Display Prices During Cart and Checkout" is set to "Including tax"
* Fix: Hide on Mobile & Desktop for Vertical Menus
* Fix: PHP warning on tabbed menus
* Improvement: Add Text Align option to Style Overrides
* Improvement: Add "Open link in new window" options to logo and icon toggle blocks
* New: Add BuddyPress Avatar and Notification Count replacement options
* Update: FontAwesome 4.7

= 1.6.2.1 =

24 January 2017

* Fix: Background color of tabbed menu items in mobile menu

= 1.6.2 =

23 January 2017

* Fix: Clearing menu items in tabbed sub menus
* Fix: Mobile menu background color

= 1.6.1 =

17 January 2017

* Improvement: Add Text Decoration, Text Decoration (Hover) and Font Weight (Hover) options to Style Overrides
* Improvement: Add Hover option for Custom Icons

= 1.6 =

16 January 2017

* New Feature: Tabbed Mega Menus
* New Feature: Add "Hide until scroll up" option for sticky menus
* Fix: Accordion and Vertical menu item height when menu text wraps onto 2 lines
* Change: Center align custom icons when icon position is set to "Top"

= 1.5.4 =

27 December 2016

* Fix: Static search box placeholder text disappears if window.resize() is called
* Improvement: Mobile search toggle block styling
* Improvement: Add vertical offset option to search toggle block

= 1.5.3 =

20 December 2016

Note: If you are using the Tabbed Mega Menu (Beta) Functionality in Pro, please also update Max Mega Menu (free) to v2.3.1+

* New feature: Search replacement and search toggle block: Add option to use WooCommerce search results template
* New feature: Replacements: Add [maxmegamenu_logout_url] shortcode to generate a dynamic logout link
* New feature: Replacements: Add [maxmegamenu_user_info] shortcode to display user information (first name, last name etc)
* New feature: Replacements: Add [maxmegamenu_user_gravatar] shortcode to display a users gravatar
* Improvement: Add 'Hide when sticky' and 'Show when sticky' options per menu item
* Improvement: Add 'megamenu_google_fonts_url' filter

= 1.5.2 =

24 November 2016

* Change: Leave the licence input field active/editable when the licence is activated
* Fix: Add alt text to Logo toggle block
* Fix: Update sticky menu z-index so that it sits below the WordPress admin bar (if logged in)
* Fix: Sticky menu positioning when max-width has been applied to the inner menu
* Fix: Mobile search block placeholder text
* Improvement: Add megamenu_search_replacement_html filter
* Improvement: Add megamenu_search_inputs filter
* Improvement: Add Exo 2 Google Font

= 1.5.1 =

17 August 2016

* Fix: Search box loses focus when mobile menu is stuck
* Fix: Panel Background color not working in Theme Editor

= 1.5 =

15 August 2016

* New Feature: Allow multiple menus to be stuck (move Sticky Menu settings to Appearance > Menus)
* New Feature: 'Expand background' sticky option
* Experimental Feature: Tabbed mega menus
* Fix: Menu Item Divider on Accordion Menus
* Fix: Sub Menu Width style override when the default sub menu width is set using a jQuery selector
* Fix: PHP Warning when no editable roles are present
* Fix: Remove automatic focus from search toggle to stop keyboard appearing and disappearing on mobile
* Improvement: Add 'megamenu_search_var' and 'megamenu_search_action' filters
* Improvement: Menu Item Height option to style overrides
* Improvement: Menu Item Margin Top/Bottom options added to style overrides
* Improvement: Menu Item Padding Top/Bottom options added to style overrides
* Improvement: Add FontAwesome icons as options to icon toggle block
* Improvement: Search Replacement: Add 'Width' option
* Improvement: Search Replacement: Improve icon positioning in both mobile & desktop
* Improvement: Search Replacement: Add 'Vertical Offset' option for fine tuning vertical alignment
* Change: Rename 'data-sticky' attribute to 'data-sticky-enabled' to avoid conflict with Shopify plugin

= 1.4.5 =

01 June 2016

* Change: Load javascript in footer
* Improvement: Add icon color and icon color (hover) options to style overrides
* New feature: Accessibility: Open search box when tabbed to
* Improvement: Menu Logo replacement now respects menu item 'Open link in new tab/window' option

= 1.4.4 =

03 May 2016

* Fix: "select()" function echoing output (breaking mega menu builder on some installations)

= 1.4.3 =

03 May 2016

* New Feature: Accordion Menu sub menu visibility option added (keep sub menus of active parents open)
* Improvement: Add Font Weight option to style overrides
* Improvement: Add Font Text Transform option to style overrides
* Change: Add role='search' to search form (for compatibility with BuddyPress Global Search)
* Fix: Change Toggle Block HTML input to textarea
* Fix: HTML Toggle block HTML escaping
* Fix: Use full size image for Logo toggle block

= 1.4.2 =

24 March 2016

* Change: Update URL changed to https to avoid SSL verification errors on some servers

= 1.4.1 =

22 March 2016

* Fix: Class not exists error when Mega Menu is not installed

= 1.4 =

22 March 2016

* New Feature: "Logo / Image" mobile toggle block
* New Feature: "HTML" mobile toggle block
* New Feature: "Search Box" mobile toggle block
* New Feature: "Icon" mobile toggle block
* Fix: Logo Width/Height

= 1.3.13 =

11 March 2016

* Improvement: Add Sub Menu - Vertical Offset and Sub Menu - Horizontal Offset settings to custom item styling
* Fix: Use max-height: none; for logo menu items
* Fix: Clear search inbox text on page load
* Fix: $custom_icon SCSS variable does not exist
* Improvement: Add alt text to logo items
* Fix: Round up sticky menu inner width
* Change: Allow flyout menu item background color to be changed per menu item
* Fix: Max Mega Menu installation check

= 1.3.12 =

20 January 2016

* Fix: PHP Warnings

= 1.3.11 =

20 January 2016

* Update: FontAwesome from 4.3 to 4.5
* Improvement: Add support for Retina custom icons
* Fix: Check Mega_Menu_Style_Manager class exists before attempting to load

= 1.3.10 =

18 January 2016

* Fix: Use protocol relative URLs for custom icons in CSS output
* Fix: Sticky on mobile setting
* Fix: Logo URL now points to custom menu item URL, instead of being hard coded to homepage
* Fix: Sticky menu when the wrapper has a max-width set on it
* Fix: Keep Accordion open when menu item has 'mega-current-menu-ancestor' class
* Fix: Apply menu item divider setting to vertical menus
* Improvement: Intelligently enqueue Google fonts
* Improvement: Standarise media file selection for logos and custom styling background images
* Improvement: Activate max mega menu when activate link is clicked (instead of taking user to plugins page)
* Improvement: Use h3 instead of h4 for font title

= 1.3.9 =

03 December 2015

* Improvement: Sub Menu - Background Color added to Style Override options
* Fix: Static search box markup
* New feature: Accordion style vertical menus
* Improvement: Add 'Expand to Right' search box option
* Fix: Sub menu panel width override CSS
* Improvement: Add sticky menu offset setting

= 1.3.8 =

05 November 2015

* Fix: Allow Replacements option to save script tags
* Fix: PHP Warning

= 1.3.7 =

29 October 2015

* Fix sticky menu pixel rounding width issue
* Fix licence activation/deactivation redirect
* Fix conflict with MaxButtons

= 1.3.6 =

01 September 2015

* Add sub menu background image options to Custom Styling
* Fix Logo Replacement styling

= 1.3.5 =

14 August 2015

* Add "Sticky on mobile" setting
* Add Border Width and Border Radius style override options
* Fix: Apply menu item spacing styling option to vertical menus

= 1.3.4 =

27 July 2015

* Remove item highlighting from menu logo
* Add replacements for EDD Cart Total / Quantity
* Add replacements for WooCommerce Cart Total / Quantity
* Add default expanded state setting to Search replacement type

= 1.3.3 =

14 July 2015

* Add border color to style overrides
* Update licence verification URL

= 1.3.2 =

22 June 2015

* Fix: Search box mobile styling

= 1.3.1 =

09 June 2015

* Fix: Vertical Menu top level menu items width

= 1.3 =

* New Feature: "Replacements" - Replace a menu item with something else: a logo, a search box, custom HTML or a shortcode
* Fix: Conflicts with TGMPA
* Fix: Custom Icon selection when image size is < 150x150

= 1.2 =

* New Feature: Vertical Menu support

= 1.1.2 (internal) =

* Fix: Licence activation conflict with Formidable Pro
* Update: Tidy up licence activation methods

= 1.1.1 =

* Fix: Update notifications

= 1.1 =

* Fix: Reduce timeout from 15 seconds to 5 seconds when attempting to retrieve details of plugin updates
* New feature: Add icon size, font size, menu item padding (left/right), menu item margin (left/right) to styling tab
* Fix: Rename custom styling SCSS variables to avoid conflicts
* New Feature: "Fade Up" effect

= 1.0.1 =

* Fix custom icon tab styling in FireFox

= 1.0 =

* Initial Release
