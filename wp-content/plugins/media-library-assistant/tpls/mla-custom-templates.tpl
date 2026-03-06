<!-- template="default-style" -->
<!-- mla_shortcode_slug="gallery" -->
<!-- mla_description="CSS Styles for the gallery shortcode" -->
<style type='text/css'>
	#[+selector+] {
		margin: auto;
		width: 100%;
	}
	#[+selector+] .gallery-item {
		float: [+float+];
		margin: [+margin+];
		display: inline-block;
		text-align: center;
		width: [+itemwidth+];
	}
	#[+selector+] .gallery-item .gallery-icon img {
		border: 2px solid #cfcfcf;
	}
	#[+selector+] .gallery-caption {
		margin-left: 0;
		vertical-align: top;
	}
</style>

<!-- template="default-description-markup" -->
This template uses the itemtag, icontag and captiontag values to compose a list-based gallery display.
<!-- template="default-arguments-markup" -->
mla_shortcode_slug="gallery"
<!-- template="default-open-markup" -->
<div id='[+selector+]' class='gallery galleryid-[+id+] gallery-columns-[+columns+] gallery-size-[+size_class+]'>

<!-- template="default-row-open-markup" -->
<!-- row-open -->

<!-- template="default-item-markup" -->
<[+itemtag+] class='gallery-item [+last_in_row+]'>
	<[+icontag+] class='gallery-icon [+orientation+]'>
		[+link+]
	</[+icontag+]>
	[+captiontag_content+]</[+itemtag+]>
<!-- template="default-row-close-markup" -->
<br style="clear: both" />

<!-- template="default-close-markup" -->
</div>

<!-- template="tag-cloud-style" -->
<!-- mla_shortcode_slug="tag-cloud" -->
<!-- mla_description="CSS Styles for the tag cloud shortcode" -->
<style type='text/css'>
	#[+selector+] {
		margin: auto;
		width: 100%;
	}
	#[+selector+] .tag-cloud-item {
		float: [+float+];
		margin: [+margin+];
		text-align: center;
		width: [+itemwidth+];
	}
	#[+selector+] .tag-cloud-caption {
		margin-left: 0;
		vertical-align: top;
	}
</style>

<!-- template="tag-cloud-description-markup" -->
For the "grid" output format, this template wraps each item in a "dl,dd,dt" list, divides them in rows and encloses them in a 'div'element.
<!-- template="tag-cloud-arguments-markup" -->
mla_shortcode_slug="tag-cloud"
<!-- template="tag-cloud-open-markup" -->
<div id='[+selector+]' class='tag-cloud tag-cloud-taxonomy-[+taxonomy+] tag-cloud-columns-[+columns+]'>

<!-- template="tag-cloud-row-open-markup" -->
<!-- row-open -->

<!-- template="tag-cloud-item-markup" -->
<[+itemtag+] class='tag-cloud-item [+last_in_row+]'>
	<[+termtag+] class='tag-cloud-term'>
		[+thelink+]
	</[+termtag+]>
	<[+captiontag+] class='wp-caption-text tag-cloud-caption'>
		[+caption+]
	</[+captiontag+]>
</[+itemtag+]>

<!-- template="tag-cloud-row-close-markup" -->
<br style="clear: both" />

<!-- template="tag-cloud-close-markup" -->
</div>

<!-- template="tag-cloud-ul-description-markup" -->
For the "ulist" and "olist" output formats, this template generates the "ul/ol" and "li" elements.
<!-- template="tag-cloud-ul-arguments-markup" -->
mla_shortcode_slug="tag-cloud"
<!-- template="tag-cloud-ul-open-markup" -->
<[+itemtag+] id='[+selector+]' class='tag-cloud tag-cloud-taxonomy-[+taxonomy+]'>

<!-- template="tag-cloud-ul-item-markup" -->
	<[+termtag+] class='tag-cloud-term'>[+thelink+]</[+termtag+]>

<!-- template="tag-cloud-ul-close-markup" -->
</[+itemtag+]>

<!-- template="tag-cloud-dl-description-markup" -->
For the "dlist" output format, this template wraps "dt" and "dd" elements in a "dl" list.
<!-- template="tag-cloud-dl-arguments-markup" -->
mla_shortcode_slug="tag-cloud"
<!-- template="tag-cloud-dl-open-markup" -->
<[+itemtag+] id='[+selector+]' class='tag-cloud tag-cloud-taxonomy-[+taxonomy+]'>

<!-- template="tag-cloud-dl-item-markup" -->
	<[+termtag+] class='tag-cloud-term'>[+thelink+]</[+termtag+]>
	<[+captiontag+] class='wp-caption-text tag-cloud-caption'>[+caption+]</[+captiontag+]>

<!-- template="tag-cloud-dl-close-markup" -->
</[+itemtag+]>

<!-- template="term-list-style" -->
<!-- mla_shortcode_slug="term-list" -->
<!-- mla_description="CSS Styles for the term list shortcode" -->
<style type='text/css'>
	#[+selector+] {
		margin: auto;
		width: 100%;
	}
	#[+selector+] .term-list-item {
		text-align: left;
	}
	#[+selector+] .term-list-caption {
		margin-left: 0;
		vertical-align: top;
	}

	#[+selector+].term-list-checklist {
		list-style: none;
	}
</style>

<!-- template="term-list-checklist-div-style" -->
<!-- mla_description="CSS Styles for the 'checklist,div' output format" -->
<!-- mla_shortcode_slug="term-list" -->
<style type='text/css'>
	#[+selector+] {
		height: 14em;
		border: 1px solid #ddd;
		overflow-y: scroll;
		margin: auto;
		width: 100%;
	}

	#[+selector+] .term-list-item {
		text-align: left;
	}
	#[+selector+] .term-list-caption {
		margin-left: 0;
		vertical-align: top;
	}

	#[+selector+]-div .term-list-checklist {
		list-style: none;
	}
</style>

<!-- template="term-list-ul-description-markup" -->
For the "ulist" and "olist" output formats, this template generates the "ul/ol" and "li" elements.
<!-- template="term-list-ul-arguments-markup" -->
mla_shortcode_slug="term-list"
<!-- template="term-list-ul-open-markup" -->
<[+itemtag+] [+itemtag_attributes+] class="[+itemtag_class+]" id="[+itemtag_id+]">

<!-- template="term-list-ul-item-markup" -->
	<[+termtag+] [+termtag_attributes+] class="[+termtag_class+]" id="[+termtag_id+]">[+thelink+]
	[+children+]</[+termtag+]>

<!-- template="term-list-ul-close-markup" -->
</[+itemtag+]>

<!-- template="term-list-dl-description-markup" -->
For the "dlist" output format, this template wraps "dt" and "dd" elements in a "dl" list.
<!-- template="term-list-dl-arguments-markup" -->
mla_shortcode_slug="term-list"
<!-- template="term-list-dl-open-markup" -->
<[+itemtag+] id='[+selector+]' class='term-list term-list-taxonomy-[+taxonomy+]'>

<!-- template="term-list-dl-item-markup" -->
	<[+termtag+] class='term-list-term'>[+thelink+]</[+termtag+]>
	<[+captiontag+] class='wp-caption-text term-list-caption'>[+caption+]</[+captiontag+]>

<!-- template="term-list-dl-close-markup" -->
</[+itemtag+]>

<!-- template="term-list-dropdown-description-markup" -->
For the "dropdown" output format, this template generates the "select" and "option" elements.
<!-- template="term-list-dropdown-arguments-markup" -->
mla_shortcode_slug="term-list"
<!-- template="term-list-dropdown-open-markup" -->
<[+itemtag+] [+multiple+] name='[+thename+]' class='term-list term-list-dropdown term-list-taxonomy-[+taxonomy+]' id='[+selector+]'>

<!-- template="term-list-dropdown-item-markup" -->
	<[+termtag+] class='term-list-term term-list-dropdown-term level-[+current_level+]' value='[+thevalue+]' [+selected+]>[+thelabel+]</[+termtag+]>
	[+children+]

<!-- template="term-list-dropdown-close-markup" -->
</[+itemtag+]>

<!-- template="term-list-checklist-description-markup" -->
For the "checklist" output format, this template wraps checkbox elements in a "ul" list.
<!-- template="term-list-checklist-arguments-markup" -->
mla_shortcode_slug="term-list"
<!-- template="term-list-checklist-open-markup" -->
<[+itemtag+] id='[+selector+]' class='term-list term-list-checklist term-list-taxonomy-[+taxonomy+]'>

<!-- template="term-list-checklist-item-markup" -->
	<[+termtag+] class='term-list-term term-list-checklist-term level-[+current_level+] [+popular+]' id='[+termtag_id+]'><label class='selectit'><input name='[+thename+]' id='in-[+termtag_id+]' type='checkbox' value='[+thevalue+]' [+selected+]>[+thelabel+]</label>[+children+]</[+termtag+]>

<!-- template="term-list-checklist-close-markup" -->
</[+itemtag+]>

<!-- template="term-list-checklist-div-description-markup" -->
For the "checklist,div" output format, this template wraps the list in a DIV tag to enable CSS styling.
<!-- template="term-list-checklist-div-arguments-markup" -->
mla_shortcode_slug="term-list"
<!-- template="term-list-checklist-div-open-markup" -->
<div id='[+selector+]-div' class='term-list term-list-taxonomy-[+taxonomy+]'>
<[+itemtag+] id='[+selector+]' class='term-list term-list-checklist term-list-taxonomy-[+taxonomy+]'>

<!-- template="term-list-checklist-div-item-markup" -->
	<[+termtag+] class='term-list-term term-list-checklist-term level-[+current_level+] [+popular+]' id='[+termtag_id+]'><label class='selectit'><input name='[+thename+]' id='in-[+termtag_id+]' type='checkbox' value='[+thevalue+]' [+selected+]>[+thelabel+]</label>[+children+]</[+termtag+]>

<!-- template="term-list-checklist-div-child-open-markup" -->
<[+itemtag+] id='[+selector+]-[+current_level+]' class='term-list term-list-checklist term-list-taxonomy-[+taxonomy+]'>

<!-- template="term-list-checklist-div-child-close-markup" -->
</[+itemtag+]>

<!-- template="term-list-checklist-div-close-markup" -->
</[+itemtag+]>
</div>

<!-- template="custom-list-style" -->
<!-- mla_shortcode_slug="custom-list" -->
<style type='text/css'>
	#[+selector+] {
		margin: auto;
		width: 100%;
	}
	#[+selector+] .custom-list-item {
		float: [+float+];
		margin: [+margin+];
		text-align: center;
		width: [+itemwidth+];
	}
	#[+selector+] .custom-list-caption {
		margin-left: 0;
		vertical-align: top;
	}
</style>

<!-- template="custom-list-flat-div-style" -->
<!-- mla_shortcode_slug="custom-list" -->
<!-- mla_description="CSS Styles for the 'flat,div' output format" -->
<style type='text/css'>
	#[+selector+] a.mla_current_item,
	#[+selector+] a.mla_current_item:visited {
		color:#FF0000;
		font-weight:bold
	}
</style>

<!-- template="custom-list-checklist-div-style" -->
<!-- mla_description="CSS Styles for the 'checklist,div' output format" -->
<!-- mla_shortcode_slug="custom-list" -->
<style type='text/css'>
	#[+selector+] {
		height: 14em;
		border: 1px solid #ddd;
		overflow-y: scroll;
		list-style: none;
		margin: auto;
		width: 100%;
	}
	#[+selector+] .custom-list-item {
		float: [+float+];
		margin: [+margin+];
		text-align: center;
		width: [+itemwidth+];
	}
	#[+selector+] .custom-list-caption {
		margin-left: 0;
		vertical-align: top;
	}
</style>

<!-- template="custom-list-flat-div-description-markup" -->
For the "flat,div" output format, this template wraps the list/cloud in a DIV tag to enable CSS styling.<!-- template="custom-list-flat-div-arguments-markup" -->
mla_shortcode_slug="custom-list"
<!-- template="custom-list-flat-div-open-markup" -->
<div id='[+selector+]' class='custom-list custom-list-key-[+meta_key+]'>

<!-- template="custom-list-flat-div-close-markup" -->
</div>

<!-- template="custom-list-grid-description-markup" -->
For the "grid" output format, this template wraps each item in a "dl,dd,dt" list, divides them in rows and encloses them in a 'div'element.
<!-- template="custom-list-grid-arguments-markup" -->
mla_shortcode_slug="custom-list"
<!-- template="custom-list-grid-open-markup" -->
<div id='[+selector+]' class='custom-list custom-list-key-[+meta_key+] custom-list-columns-[+columns+]'>

<!-- template="custom-list-grid-row-open-markup" -->
<!-- row-open -->

<!-- template="custom-list-grid-item-markup" -->
<[+itemtag+] class='custom-list-item [+last_in_row+]'>
	<[+valuetag+] class='custom-list-value'>
		[+thelink+]
	</[+valuetag+]>
	<[+captiontag+] class='wp-caption-text custom-list-caption'>
		[+caption+]
	</[+captiontag+]>
</[+itemtag+]>

<!-- template="custom-list-grid-row-close-markup" -->
<br style="clear: both" />

<!-- template="custom-list-grid-close-markup" -->
</div>

<!-- template="custom-list-ul-description-markup" -->
For the "ulist" and "olist" output formats, this template generates the "ul/ol" and "li" elements.
<!-- template="custom-list-ul-arguments-markup" -->
mla_shortcode_slug="custom-list"
<!-- template="custom-list-ul-open-markup" -->
<[+itemtag+] [+itemtag_attributes+] class="[+itemtag_class+]" id="[+itemtag_id+]">

<!-- template="custom-list-ul-item-markup" -->
	<[+valuetag+] class='custom-list-value'>[+thelink+]</[+valuetag+]>

<!-- template="custom-list-ul-close-markup" -->
</[+itemtag+]>

<!-- template="custom-list-dl-description-markup" -->
For the "dlist" output format, this template wraps "dt" and "dd" elements in a "dl" list.
<!-- template="custom-list-dl-arguments-markup" -->
mla_shortcode_slug="custom-list"
<!-- template="custom-list-dl-open-markup" -->
<[+itemtag+] [+itemtag_attributes+] class="[+itemtag_class+]" id="[+itemtag_id+]">

<!-- template="custom-list-dl-item-markup" -->
	<[+valuetag+] class='custom-list-value'>[+thelink+]</[+valuetag+]>
	<[+captiontag+] class='wp-caption-text custom-list-caption'>[+caption+]</[+captiontag+]>

<!-- template="custom-list-dl-close-markup" -->
</[+itemtag+]>

<!-- template="custom-list-dropdown-description-markup" -->
For the "dropdown" output format, this template generates the "select" and "option" elements.
<!-- template="custom-list-dropdown-arguments-markup" -->
mla_shortcode_slug="custom-list"
<!-- template="custom-list-dropdown-open-markup" -->
<[+itemtag+] [+multiple+] name='[+thename+]' class='custom-list custom-list-dropdown custom-list-key-[+meta_key+]' id='[+selector+]'>

<!-- template="custom-list-dropdown-item-markup" -->
	<[+valuetag+] class='custom-list-value custom-list-dropdown-value' value='[+thevalue+]' [+selected+]>[+thelabel+]</[+valuetag+]>

<!-- template="custom-list-dropdown-close-markup" -->
</[+itemtag+]>

<!-- template="custom-list-checklist-description-markup" -->
For the "checklist" output format, this template wraps checkbox elements in a "ul" list.
<!-- template="custom-list-checklist-arguments-markup" -->
mla_shortcode_slug="custom-list"
<!-- template="custom-list-checklist-open-markup" -->
<[+itemtag+] id='[+selector+]' class='custom-list custom-list-checklist custom-list-key-[+meta_key+]'>

<!-- template="custom-list-checklist-item-markup" -->
	<[+valuetag+] class='custom-list-value custom-list-checklist-value [+popular+]' id='[+valuetag_id+]'><label class='selectit'><input name='[+thename+]' id='in-[+valuetag_id+]' type='checkbox' value='[+thevalue+]' [+selected+]>[+thelabel+]</label></[+valuetag+]>

<!-- template="custom-list-checklist-close-markup" -->
</[+itemtag+]>

<!-- template="custom-list-checklist-div-description-markup" -->
For the "checklist,div" output format, this template wraps the list in a DIV tag to enable CSS styling.
<!-- template="custom-list-checklist-div-arguments-markup" -->
mla_shortcode_slug="custom-list"
<!-- template="custom-list-checklist-div-open-markup" -->
<div id='[+selector+]-div' class='custom-list custom-list-key-[+meta_key+]'>
<[+itemtag+] id='[+selector+]' class='custom-list custom-list-checklist custom-list-key-[+meta_key+]'>

<!-- template="custom-list-checklist-div-item-markup" -->
	<[+valuetag+] class='custom-list-value custom-list-checklist-value [+popular+]' id='[+valuetag_id+]'><label class='selectit'><input name='[+thename+]' id='in-[+valuetag_id+]' type='checkbox' value='[+thevalue+]' [+selected+]>[+thelabel+]</label></[+valuetag+]>

<!-- template="custom-list-checklist-div-close-markup" -->
</[+itemtag+]>
</div>

<!-- template="archive-list-style" -->
<!-- mla_description="CSS Styles for the archive list shortcode" -->
<!-- mla_shortcode_slug="archive-list" -->
<style type='text/css'>
	#[+selector+] {
		margin: auto;
		width: 100%;
	}
	#[+selector+] .archive-list-item {
		text-align: left;
	}
	#[+selector+] li.[+current_archive_class+] a {
		font-weight: bold;
		font-size: larger;
	}
</style>

<!-- template="archive-list-dropdown-description-markup" -->
For the "dropdown" output format, this template generates the "select" and "option" elements.
<!-- template="archive-list-dropdown-arguments-markup" -->
mla_shortcode_slug="archive-list"
<!-- template="archive-list-dropdown-open-markup" -->
<[+listtag+] name="[+listtag_name+]" id="[+listtag_id+]" class="[+listtag_class+]" [+listtag_attributes+]>

<!-- template="archive-list-dropdown-item-markup" -->
	<[+itemtag+] id="[+item_id+]" class="[+item_class+]" [+item_attributes+] [+item_selected+] value="[+current_value+]">[+item_label+]</[+itemtag+]>

<!-- template="archive-list-dropdown-close-markup" -->
</[+listtag+]>

<!-- template="archive-list-flat-div-style" -->
<!-- mla_shortcode_slug="archive-list" -->
<!-- mla_description="CSS Styles for the 'flat,div' output format" -->
<style type='text/css'>
	#[+selector+] a.mla_archive_current,
	#[+selector+] a.mla_archive_current:visited {
		color:#FF0000;
		font-weight:bold
	}
</style>

<!-- template="archive-list-flat-div-description-markup" -->
For the "flat,div" output format, this template wraps the list/cloud in a DIV tag to enable CSS styling.<!-- template="archive-list-flat-div-arguments-markup" -->
mla_shortcode_slug="archive-list"
<!-- template="archive-list-flat-div-open-markup" -->
<div id="[+listtag_id+]" class="[+listtag_class+]" [+listtag_attributes+]>

<!-- template="archive-list-flat-div-close-markup" -->
</div>

<!-- template="archive-list-ul-description-markup" -->
For the "ulist" and "olist" output formats, this template generates the "ul/ol" and "li" elements.
<!-- template="archive-list-ul-arguments-markup" -->
mla_shortcode_slug="archive-list"
<!-- template="archive-list-ul-open-markup" -->
<[+listtag+] id="[+listtag_id+]" class="[+listtag_class+]" [+listtag_attributes+]>

<!-- template="archive-list-ul-item-markup" -->
	<[+itemtag+] id="[+item_id+]" class="[+item_class+]" [+item_attributes+]>[+thelink+]</[+itemtag+]>

<!-- template="archive-list-ul-close-markup" -->
</[+listtag+]>

<!-- template="archive-list-ul-div-style" -->
<!-- mla_shortcode_slug="archive-list" -->
<!-- mla_description="CSS Styles for the 'ul,div' and 'ol,div' output formats" -->
<style type='text/css'>
	#[+selector+] {
		height: 14em;
		border: 1px solid #ddd;
		overflow-y: scroll;
		list-style: none;
		margin: auto;
		width: 100%;
	}
	#[+selector+] li.mla_archive_current a,
	#[+selector+] li.mla_archive_current:visited a {
		color:#FF0000;
		font-weight:bold
	}
</style>

<!-- template="archive-list-ul-div-description-markup" -->
For the ulist and olist ",div" output format, this template wraps the list in a DIV tag to enable CSS styling.<!-- <!-- template="archive-list-ul-div-arguments-markup" -->
mla_shortcode_slug="archive-list"
<!-- template="archive-list-ul-div-open-markup" -->
<div id="[+listtag_id+]" class="[+listtag_class+]" [+listtag_attributes+]>
<[+listtag+] id="[+listtag_id+]-[+listtag+]" class="[+listtag_class+]" [+listtag_attributes+]>

<!-- template="archive-list-ul-div-item-markup" -->
	<[+itemtag+] id="[+item_id+]" class="[+item_class+]" [+item_attributes+]>[+thelink+]</[+itemtag+]>

<!-- template="archive-list-ul-div-close-markup" -->
</[+listtag+]>
</div>
