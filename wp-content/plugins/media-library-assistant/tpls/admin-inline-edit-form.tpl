<!-- template="category_fieldset" -->
          <fieldset class="inline-edit-col-center inline-edit-categories">
            <div class="inline-edit-col">
[+category_blocks+]
            </div>
          </fieldset>

<!-- template="category_block" -->
              <div id="taxonomy-[+tax_attr+]" class="categorydiv">
                <span class="title inline-edit-categories-label">[+tax_html+]</span>
                <input type="hidden" name="tax_input[[+tax_attr+]][]" value="0" />
                <ul class="cat-checklist [+tax_attr+]checklist form-no-clear" id="[+tax_attr+]checklist" data-wp-lists="list:[+tax_attr+]">
[+tax_checklist+]
                </ul>
[+category_add_link+]
				<span><a class="hide-if-no-js" id="[+tax_attr+]-search-toggle" href="#[+tax_attr+]-search">[+Search+]</a></span>
[+category_adder+]
                <div id="[+tax_attr+]-searcher" class="wp-hidden-children">
                  <p id="[+tax_attr+]-search" class="category-add wp-hidden-child">
                    <label class="screen-reader-text" for="search-category">[+Search Reader+]</label>
                    <input type="text" name="search-[+tax_attr+]" id="search-[+tax_attr+]" class="form-required form-input-tip" value="[+Search Reader+]" aria-required="true">
                  </p>
                </div>
              </div>

<!-- template="category_add_link" -->
		<span><a class="hide-if-no-js" id="[+tax_attr+]-add-toggle" href="#[+tax_attr+]-add">[+Add New Term+]</a></span>
		&nbsp; &nbsp;

<!-- template="category_adder" -->
		<div id="[+tax_attr+]-adder" class="wp-hidden-children">
		  <p id="[+tax_attr+]-add" class="category-add wp-hidden-child">
			<label class="screen-reader-text" for="new[+tax_attr+]">[+Add Reader+]</label>
			<input name="new[+tax_attr+]" class="form-required form-input-tip" id="new[+tax_attr+]" aria-required="true" type="text" value="[+Add Reader+]">
[+tax_parents+]
			<input class="button category-add-submit mla-taxonomy-add-submit" id="[+tax_attr+]-add-submit" type="button"  data-wp-lists="add:[+tax_attr+]checklist:[+tax_attr+]-add"value="[+Add Button+]">
			[+ajax_nonce_field+]
			<span id="[+tax_attr+]-ajax-response"></span>
		  </p>
		</div>

<!-- template="tag_fieldset" -->
          <fieldset class="inline-edit-col-right inline-edit-tags">
            <div class="inline-edit-col">
[+tag_blocks+]
            </div>
          </fieldset>

<!-- template="tag_block" -->
            <label class="inline-edit-tags">
              <span class="title">[+tax_html+]</span>
              <textarea cols="22" rows="1" name="tax_input[[+tax_attr+]]" class="tax_input_[+tax_attr+] mla_tags"></textarea>
            </label>

<!-- template="taxonomy_options" -->
			<div class="mla_bulk_taxonomy_options">
            <input type="radio" name="tax_action[[+tax_attr+]]" id="tax_add_[+tax_attr+]" checked="checked" value="add" /> [+Add+]&nbsp;
            <input type="radio" name="tax_action[[+tax_attr+]]" id="tax_remove_[+tax_attr+]" value="remove" /> [+Remove+]&nbsp;
            <input type="radio" name="tax_action[[+tax_attr+]]" id="tax_reset_[+tax_attr+]" value="replace" /> [+Replace+]&nbsp;
            </div>
<!-- template="custom_field" -->
              <label class="inline-edit-[+slug+]" style="clear:both"> <span class="title">[+label+]</span> <span class="input-text-wrap">
                <input type="text" name="[+slug+]" value="" />
                </span> </label>
<!-- template="page" -->
<form>
  <table width="99%" style="display: none">
    <tbody id="inlineedit">
      <tr id="inline-edit" class="inline-edit-row inline-edit-row-attachment inline-edit-attachment quick-edit-row quick-edit-row-attachment quick-edit-attachment" style="display: none">
        <td colspan="[+colspan+]" class="colspanchange">
		<div class="edit-fields-div" id="inline-edit-fields-div">
          <fieldset class="inline-edit-col-left">
            <div class="inline-edit-col">
              <h4>[+Quick Edit+]</h4>
			  <div id="item_thumbnail"></div>
              <label> <span class="title">[+Title+]</span> <span class="input-text-wrap">
                <input type="text" name="post_title" class="ptitle" value="" />
                </span> </label>
              <label> <span class="title">[+Name/Slug+]</span> <span class="input-text-wrap">
                <input type="text" name="post_name" value="" />
                </span> </label>
              <label> <span class="title">[+Caption+]</span> <span class="input-text-wrap">
                <input type="text" name="post_excerpt" value="" />
                </span> </label>
              <label> <span class="title">[+Description+]</span> <span class="input-text-wrap">
                <textarea class="widefat" name="post_content"></textarea>
                </span> </label>
              <label class="inline-edit-image-alt"> <span class="title">[+ALT Text+]</span> <span class="input-text-wrap">
                <input type="text" name="image_alt" value="" />
                </span> </label>
              <div class="inline-edit-group">
[+Uploaded on+]
              </div>
              <div class="inline-edit-group">
                <label class="inline-edit-post-parent alignleft"> <span class="title">[+Parent ID+]</span> <span class="input-text-wrap">
                  <input type="text" name="post_parent" value="" />
                  </span> </label>
                <label class="inline-edit-post-parent-title"> <span class="">
                  <input type="text" readonly="readonly" disabled="disabled" name="post_parent_title" value="" />
                  </span> </label>
                  <input id="inline-edit-post-set-parent" title="[+Select+]" class="button-primary parent" type="button" name="post_parent_set" value="[+Select+]" />
              </div>
              <div class="inline-edit-group">
                <label class="inline-edit-menu-order alignleft"> <span class="title">[+Menu Order+]</span> <span class="input-text-wrap">
                  <input type="text" name="menu_order" value="" />
                  </span> </label>
[+authors+]
              </div>
            </div>
          </fieldset>
[+quick_middle_column+]
[+quick_right_column+]
          <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
[+custom_fields+]
            </div>
          </fieldset>
		  </div> <!-- inline-edit-fields-div -->
          <p class="submit inline-edit-save">
		  	<a accesskey="c" href="#inline-edit" title="[+Cancel+]" class="button-secondary cancel alignleft">[+Cancel+]</a>
		  	<a accesskey="s" href="#inline-edit" title="[+Update+]" class="button-primary save alignright">[+Update+]</a>
			<span class="spinner"></span>
            <input type="hidden" name="page" value="mla-menu" />
            <input type="hidden" name="screen" value="media_page_mla-menu" />
			<br class="clear" />
            <span class="error" style="display:none"></span>
          </p>
        </td>
      </tr>
      <tr id="blank-bulk-edit" class="inline-edit-row inline-edit-row-attachment inline-edit-attachment bulk-edit-row bulk-edit-row-attachment bulk-edit-attachment" style="display: none">
        <td colspan="[+colspan+]" class="colspanchange">
		<div class="edit-fields-div" id="blank-bulk-edit-fields-div">
[+bulk_middle_column+]
[+bulk_right_column+]
          <fieldset class="inline-edit-col-right inline-edit-fields">
            <div class="inline-edit-col">
              <label> <span class="title">[+Title+]</span> <span class="input-text-wrap">
                <input type="text" name="post_title" class="ptitle" value="" />
                </span> </label>
              <label> <span class="title">[+Caption+]</span> <span class="input-text-wrap">
                <input type="text" name="post_excerpt" value="" />
                </span> </label>
              <label class="inline-edit-post-content"> <span class="title">[+Description+]</span> <span class="input-text-wrap">
                [+description_field+]
                </span> </label>
              <label class="inline-edit-image-alt"> <span class="title">[+ALT Text+]</span> <span class="input-text-wrap">
                <input type="text" name="image_alt" value="" />
                </span> </label>
              <label class="inline-edit-post-date"><span class="title">[+Bulk Uploaded on+]</span><span class="input-text-wrap">
                <input type="text" name="post_date" value="" />
                </span></label>
              <div class="inline-edit-group">
                <label class="inline-edit-post-parent alignleft"> <span class="title">[+Parent ID+]</span> <span class="input-text-wrap">
                  <input type="text" name="post_parent" value="" />
                  </span> </label>
                  <input id="bulk-edit-set-parent" title="[+Select+]" class="button-primary parent" type="button" name="post_parent_set" value="[+Select+]" />
[+bulk_authors+]
              </div>
              <div class="inline-edit-group">
							<label class="inline-edit-comments alignleft"> <span class="title">[+Comments+]</span> <span class="input-text-wrap">
								<select name="comment_status">
									<option selected="selected" value="-1">&mdash; [+No Change+] &mdash;</option>
									<option value="open">[+Allow+]</option>
									<option value="closed">[+Do not allow+]</option>
								</select>
								</span> </label>
							<label class="inline-edit-pings alignright"> <span class="title">[+Pings+]</span> <span class="input-text-wrap">
								<select name="ping_status">
									<option selected="selected" value="-1">&mdash; [+No Change+] &mdash;</option>
									<option value="open">[+Allow+]</option>
									<option value="closed">[+Do not allow+]</option>
								</select>
								</span> </label>
              </div>
[+bulk_custom_fields+]
            </div>
          </fieldset>
		</div> <!-- blank-bulk-edit-fields-div -->
        </td>
      </tr>
      <tr id="bulk-edit" class="inline-edit-row inline-edit-row-attachment inline-edit-attachment bulk-edit-row bulk-edit-row-attachment bulk-edit-attachment" style="display: none">
        <td colspan="[+colspan+]" class="colspanchange">
		<div class="edit-fields-div" id="bulk-edit-fields-div">
          <fieldset class="inline-edit-col-left">
            <div class="inline-edit-col">
              <h4>[+Bulk Edit+]</h4>
              <div id="bulk-title-div">
                <div id="bulk-titles"></div>
              </div>
		  	<a accesskey="c" href="#inline-edit" title="[+Cancel+]" class="button-secondary cancel alignleft">[+Cancel+]</a>
		  	<a accesskey="r" href="#inline-edit" title="[+Reset+]" class="button-secondary reset alignleft">[+Reset+]</a>
            </div>
          </fieldset>
[+bulk_middle_column+]
[+bulk_right_column+]
          <fieldset class="inline-edit-col-right inline-edit-fields">
            <div class="inline-edit-col">
              <label> <span class="title">[+Title+]</span> <span class="input-text-wrap">
                <input type="text" name="post_title" class="ptitle" value="" />
                </span> </label>
              <label> <span class="title">[+Caption+]</span> <span class="input-text-wrap">
                <input type="text" name="post_excerpt" value="" />
                </span> </label>
              <label class="inline-edit-post-content"> <span class="title">[+Description+]</span> <span class="input-text-wrap">
                [+description_field+]
                </span> </label>
              <label class="inline-edit-image-alt"> <span class="title">[+ALT Text+]</span> <span class="input-text-wrap">
                <input type="text" name="image_alt" value="" />
                </span> </label>
              <label class="inline-edit-post-date"><span class="title">[+Bulk Uploaded on+]</span><span class="input-text-wrap">
                <input type="text" name="post_date" value="" />
                </span></label>
              <div class="inline-edit-group">
                <label class="inline-edit-post-parent alignleft"> <span class="title">[+Parent ID+]</span> <span class="input-text-wrap">
                  <input type="text" name="post_parent" value="" />
                  </span> </label>
                  <input id="bulk-edit-set-parent" title="[+Select+]" class="button-primary parent" type="button" name="post_parent_set" value="[+Select+]" />
[+bulk_authors+]
              </div>
              <div class="inline-edit-group">
							<label class="inline-edit-comments alignleft"> <span class="title">[+Comments+]</span> <span class="input-text-wrap">
								<select name="comment_status">
									<option selected="selected" value="-1">&mdash; [+No Change+] &mdash;</option>
									<option value="open">[+Allow+]</option>
									<option value="closed">[+Do not allow+]</option>
								</select>
								</span> </label>
							<label class="inline-edit-pings alignright"> <span class="title">[+Pings+]</span> <span class="input-text-wrap">
								<select name="ping_status">
									<option selected="selected" value="-1">&mdash; [+No Change+] &mdash;</option>
									<option value="open">[+Allow+]</option>
									<option value="closed">[+Do not allow+]</option>
								</select>
								</span> </label>
              </div>
[+bulk_custom_fields+]
            </div>
          </fieldset>
		</div> <!-- bulk-edit-fields-div -->
          <p class="submit inline-edit-save">
		  	<a accesskey="c" href="#inline-edit" title="[+Cancel+]" class="button-secondary cancel alignleft">[+Cancel+]</a>
            <input accesskey="s" type="submit" name="bulk_edit" id="bulk_edit" class="button-primary alignright" value="[+Update+]"  />
            <input style="[+bulk_map_style+]" accesskey="i" type="submit" name="bulk_map" id="bulk_map" class="button-secondary alignright" value="[+Map IPTC/EXIF metadata+]" />
            <input style="[+bulk_custom_field_map_style+]" accesskey="m" type="submit" name="bulk_custom_field_map" id="bulk_custom_field_map" class="button-secondary alignright" value="[+Map Custom Field metadata+]" />
            <input type="hidden" name="page" value="mla-menu" />
            <input type="hidden" name="screen" value="media_page_mla-menu" />
            <span class="error" style="display:none"></span> <br class="clear" />
          </p>
        </td>
      </tr>
      <tr id="bulk-progress" class="inline-edit-row inline-edit-row-attachment inline-edit-attachment bulk-edit-row bulk-edit-row-attachment bulk-edit-attachment" style="display: none">
        <td colspan="[+colspan+]" class="colspanchange">
          <fieldset class="inline-edit-col-left">
            <div class="inline-edit-col">
              <h4>[+Bulk Edit+] [+Bulk Waiting+]</h4>
              <div id="bulk-progress-waiting-div">
                <div class="bulk-progress-titles" id="bulk-progress-waiting"></div>
              </div>
            </div>
          </fieldset>
          <fieldset class="inline-edit-col-center">
		    <div class="inline-edit-col">
              <h4>[+Bulk Running+]</h4>
              <div id="bulk-progress-running-div">
                <div class="bulk-progress-titles" id="bulk-progress-running"></div>
              </div>
		    </div>
		  </fieldset>
          <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
		    <div class="inline-edit-col">
              <h4>[+Bulk Complete+]</h4>
              <div id="bulk-progress-complete-div">
                <div class="bulk-progress-titles" id="bulk-progress-complete"></div>
              </div>
		    </div>
            </div>
          </fieldset>
          <p class="submit inline-edit-save">
		  	<a accesskey="c" href="#inline-edit" title="[+Cancel+]" class="button-secondary cancel alignleft">[+Cancel+]</a>
            <input accesskey="r" type="submit" name="bulk_refresh" id="bulk_refresh" class="button-primary alignright" value="[+Refresh+]"  />
			<span class="spinner"></span>
            <input type="hidden" name="page" value="mla-menu" />
            <input type="hidden" name="screen" value="media_page_mla-menu" />
            <span class="error" style="display:none"></span> <br class="clear" />
          </p>
        </td>
      </tr>
	  <tr id="add-term-ajax" style="display: none">
        <td colspan="[+colspan+]" class="colspanchange">
          <span id="add-term-ajax-response"></span>
		</td>
	  </tr>
    </tbody>
  </table>
</form>
[+set_parent_form+]
