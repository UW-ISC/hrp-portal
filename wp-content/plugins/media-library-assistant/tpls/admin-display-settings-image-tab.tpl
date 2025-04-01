<!-- template="crop-type-select-option" -->
                <option [+selected+] value="[+value+]">[+text+]</option>

<!-- template="crop-type-select" -->
            <select name="[+name+]" class="ptitle" id="mla-crop-[+dimension+]">
[+options+]
            </select>

<!-- template="single-item-edit" -->
<div id="ajax-response"></div>
<h2>[+Edit Image Size+]</h2>
<form action="[+form_url+]" method="post" class="validate" id="mla-edit-size">
	<input type="hidden" name="page" value="mla-settings-menu-image" />
	<input type="hidden" name="mla_tab" value="image" />
	<input type="hidden" name="mla_admin_action" value="[+action+]" />
	<input type="hidden" name="mla_image_item[original_slug]" value="[+original_slug+]" />
	<input type="hidden" name="mla_image_item[source]" value="[+source+]" />
	[+_wpnonce+]
	<table class="form-table">
	<tr class="form-field form-required">
	<th scope="row" valign="top"><label for="mla-image-slug">[+Slug+]</label></th>
	<td>
	<input name="mla_image_item[slug]" id="mla-image-slug" type="text" value="[+slug+]" size="40" aria-required="true" />
	<p class="description">[+The slug+]</p>
	</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="mla-image-name">[+Name+]</label></th>
	<td>
						<input name="mla_image_item[name]" id="mla-image-name" type="text" value="[+name+]" size="40" />
						<p class="description">[+The name+]</p>
	</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="mla-image-width">[+Width+]</label></th>
	<td>
						<input name="mla_image_item[width]" id="mla-image-width" type="text" value="[+width+]" size="10" />
						<p class="description">[+The width+]</p>
	</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="mla-image-height">[+Height+]</label></th>
	<td>
						<input name="mla_image_item[height]" id="mla-image-height" type="text" value="[+height+]" size="10" />
						<p class="description">[+The height+]</p>
	</td>
	</tr>
	<tr>
	<th scope="row" valign="top"><label for="mla-image-crop">[+Crop+]</label></th>
	<td>
						<input type="checkbox" name="mla_image_item[crop]" id="mla-image-crop" [+crop+] value="1" />
						<span class="description">&nbsp;[+Check crop+]</span>
	</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="mla-image-horizontal">[+Horizontal+]</label></th>
	<td>
[+horizontal_dropdown+]
	<p class="description">[+The horizontal+]</p>
	</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="mla-image-vertical">[+Vertical+]</label></th>
	<td>
[+vertical_dropdown+]
	<p class="description">[+The vertical+]</p>
	</td>
	</tr>
	<tr>
	<th scope="row" valign="top"><label for="mla-image-disabled">[+Inactive+]</label></th>
	<td>
	<input type="checkbox" name="mla_image_item[disabled]" id="mla-image-disabled" [+disabled+] value="1" />
	<span class="description">&nbsp;[+Check inactive+]</span>
	</td>
	</tr>
	<tr class="form-field">
	<th scope="row" valign="top"><label for="mla-image-description">[+Description+]</label></th>
	<td>
						<textarea name="mla_image_item[description]" id="mla-image-description" rows="5" cols="40">[+description+]</textarea>
						<p class="description">[+The description+]</p>
	</td>
	</tr>
</table>
<p class="submit mla-settings-submit">
<input name="cancel" type="submit" class="button-primary" value="[+Cancel+]" />&nbsp;
<input name="update" type="submit" class="button-primary" value="[+Update+]" />&nbsp;
</p>
</form>

<!-- template="image-disabled" -->
<h2>[+Support is disabled+]</h2>
<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-image-tab">
    <table class="optiontable">
[+options_list+]
	</table>
    <p class="submit mla-settings-submit">
        <input name="mla-image-options-save" type="submit" class="button-primary" value="[+Save Changes+]" />
    </p>
	<input type="hidden" name="page" value="mla-settings-menu-image" />
	<input type="hidden" name="mla_tab" value="image" />
	[+_wpnonce+]
</form>

<!-- template="before-table" -->
<h2>[+Image Sizes Processing+]</h2>
<p>[+In this tab+]</p>
<p>[+You can find+]</p>
<div id="ajax-response"></div>
<form action="[+form_url+]" method="get" id="mla-search-images-form">
	<input type="hidden" name="page" value="mla-settings-menu-image" />
	<input type="hidden" name="mla_tab" value="image" />
	[+view_args+]
	[+_wpnonce+]
	[+results+]
	<p class="search-box" style="margin-top: 1em">
		<label class="screen-reader-text" for="mla-search-images-input">[+Search Sizes+]:</label>
		<input type="search" id="mla-search-images-input" name="s" value="[+s+]" />
		<input type="submit" name="" id="mla-search-images-submit" class="button" value="[+Search Sizes+]" />
	</p>
</form>
<br class="clear" />
<div id="col-container">
	<div id="col-right">
		<div class="col-wrap">
			<form action="[+form_url+]" method="post" id="mla-search-images-filter">
				<input type="hidden" name="page" value="mla-settings-menu-image" />
				<input type="hidden" name="mla_tab" value="image" />
				[+view_args+]
				[+_wpnonce+]

<!-- template="after-table" -->
			</form><!-- /id=mla-search-images-filter --> 
		</div><!-- /col-wrap --> 
	</div><!-- /col-right -->

	<div id="col-left">
		<div class="col-wrap">
		<div class="mla-settings-enable-form">
		<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-image-tab">
			<table class="optiontable">
		[+options_list+]
			</table>
			<span class="submit mla-settings-submit">
				<input name="mla-image-options-save" type="submit" class="button-primary" value="[+Save Changes+]" />
			</span>
		[+_wpnonce+]
		</form>
		</div>
			<div class="form-wrap">
				<h2>[+Add New Size+]</h2>
				<form action="[+form_url+]" method="post" class="validate" id="mla-add-image">
					<input type="hidden" name="page" value="mla-settings-menu-image" />
					<input type="hidden" name="mla_tab" value="image" />
					[+_wpnonce+]
					<div class="form-field form-required">
						<label for="mla-image-slug">[+Slug+]</label>
						<input name="mla_image_item[slug]" id="mla-image-slug" type="text" value="[+slug+]" size="40" />
						<p class="description">[+The slug+]</p>
					</div>
					<div class="form-field">
						<label for="mla-image-name">[+Name+]</label>
						<input name="mla_image_item[name]" id="mla-image-name" type="text" value="[+name+]" size="40" />
						<p class="description">[+The name+]</p>
					</div>
					<div class="form-field">
						<label for="mla-image-width">[+Width+]</label>
						<input name="mla_image_item[width]" id="mla-image-width" type="text" value="[+width+]" size="10" />
						<p class="description">[+The width+]</p>
					</div>
					<div class="form-field">
						<label for="mla-image-height">[+Height+]</label>
						<input name="mla_image_item[height]" id="mla-image-height" type="text" value="[+height+]" size="10" />
						<p class="description">[+The height+]</p>
					</div>
					<div>
						<input type="checkbox" name="mla_image_item[crop]" id="mla-image-crop" [+crop+] value="1" />
						[+Crop+]
						<p class="description">[+Check crop+]</p>
					</div>
					<div class="form-field">
						<label for="mla-crop-horizontal">[+Horizontal+]</label>
[+horizontal_dropdown+]
						<p class="description">[+The horizontal+]</p>
					</div>
					<div class="form-field">
						<label for="mla-crop-vertical">[+Vertical+]</label>
[+vertical_dropdown+]
						<p class="description">[+The vertical+]</p>
					</div>
					<div>
						<input type="checkbox" name="mla_image_item[disabled]" id="mla-image-disabled" [+disabled+] value="1" />
						[+Inactive+]
						<p class="description">[+Check inactive+]</p>
					</div>
					<div class="form-field">
						<label for="mla-image-description">[+Description+]</label>
						<textarea name="mla_image_item[description]" id="mla-image-description" rows="5" cols="40">[+description+]</textarea>
						<p class="description">[+The description+]</p>
					</div>
					<p class="submit mla-settings-submit">
						<input type="submit" name="mla-add-image-submit" id="mla-add-image-submit" class="button button-primary" value="[+Add Size+]" />
					</p>
				</form><!-- /id=mla-add-image --> 
			</div><!-- /form-wrap --> 
		</div><!-- /col-wrap -->
	</div><!-- /col-left --> 
</div><!-- /col-container -->
<script type="text/javascript">
try{document.forms.addtag['mla-image-slug'].focus();}catch(e){}
</script> 
<form>
	<table width="99%" style="display: none">
		<tbody id="inlineedit">
			<tr id="inline-edit" class="inline-edit-row inline-edit-row-image inline-edit-image quick-edit-row quick-edit-row-image quick-edit-image" style="display: none">
				<td colspan="[+colspan+]" class="colspanchange">
					<fieldset class="inline-edit-col">
						<div class="inline-edit-col">
							<h4>[+Quick Edit+]</h4>
							<label class="alignleft"> <span class="title">[+Slug+]</span> <span class="input-text-wrap">
								<input type="text" readonly="readonly" name="slug" class="ptitle" value="" />
								</span> </label>
							<label class="alignleft"> <span class="title">[+Name+]</span> <span class="input-text-wrap">
								<input type="text" name="name" class="ptitle" value="" />
								</span> </label>
							<label class="alignleft"> <span class="title">[+Width+]</span> <span class="input-text-wrap">
								<input type="text" name="width" class="ptitle" value="" />
								</span> </label>
							<label class="alignleft"> <span class="title">[+Height+]</span> <span class="input-text-wrap">
								<input type="text" name="height" class="ptitle" value="" />
								</span> </label>
							<label class="alignleft"> <span class="title">[+Horizontal+]</span> <span class="input-text-wrap">
[+horizontal_dropdown_inline+]
							</span> </label>
							<label class="alignleft"> <span class="title">[+Vertical+]</span> <span class="input-text-wrap">
[+vertical_dropdown_inline+]
							</span> </label>
							<label class="alignleft checkbox-label">
								<input type="checkbox" name="crop" class="ptitle" checked="checked" value="1" />
								<span class="checkbox-title">[+Crop+]</span>
							</label>
							<label class="alignleft checkbox-label">
								<input type="checkbox" name="disabled" class="ptitle" checked="checked" value="1" />
								<span class="checkbox-title">[+Inactive+]</span>
							</label>
						</div>
					</fieldset>
					<p class="inline-edit-save submit"> <a accesskey="c" href="#inline-edit" title="Cancel" class="cancel button-secondary alignleft">[+Cancel+]</a> <a accesskey="s" href="#inline-edit" title="[+Update+]" class="save button-primary alignright">[+Update+]</a>
						<input type="hidden" name="original_slug" value="" />
						<input type="hidden" name="page" value="mla-settings-menu-image" />
						<input type="hidden" name="mla_tab" value="image" />
						<input type="hidden" name="screen" value="settings_page_mla-settings-menu-image" />
						<span class="spinner"></span>
						<span class="error" style="display: none;"></span>
						<br class="clear" />
					</p>
				</td>
			</tr>
			<tr id="bulk-edit" class="inline-edit-row inline-edit-row-image inline-edit-image bulk-edit-row bulk-edit-row-image bulk-edit-image" style="display: none">
				<td colspan="[+colspan+]" class="colspanchange">
					<h4>[+Bulk Edit+]</h4>
					<fieldset class="inline-edit-col-left">
						<div class="inline-edit-col">
							<div id="bulk-title-div">
								<div id="bulk-titles"></div>
							</div>
						</div>
					</fieldset>
					<fieldset class="inline-edit-col-right">
						<div class="inline-edit-col">
							<label class="inline-edit-crop"> <span class="title">[+Crop+]</span> <span class="input-text-wrap">
								<select name="crop">
									<option selected="selected" value="-1">&mdash; [+No Change+] &mdash;</option>
									<option value="0">[+No+]</option>
									<option value="1">[+Yes+]</option>
								</select>
								</span> </label>
							<br />
							<label class="inline-edit-table-image"> <span class="title">[+Horizontal+]</span> <span class="input-text-wrap">
								<select name="horizontal">
									<option selected="selected" value="-1">&mdash; [+No Change+] &mdash;</option>
									<option value="left">[+Left+]</option>
									<option value="center">[+Center+]</option>
									<option value="right">[+Right+]</option>
								</select>
								</span> </label>
							<br />
							<label class="inline-edit-table-image"> <span class="title">[+Vertical+]</span> <span class="input-text-wrap">
								<select name="vertical">
									<option selected="selected" value="-1">&mdash; [+No Change+] &mdash;</option>
									<option value="top">[+Top+]</option>
									<option value="center">[+Center+]</option>
									<option value="bottom">[+Bottom+]</option>
								</select>
								</span> </label>
							<br />
							<label class="inline-edit-disabled"> <span class="title">[+Status+]</span> <span class="input-text-wrap">
								<select name="disabled">
									<option selected="selected" value="-1">&mdash; [+No Change+] &mdash;</option>
									<option value="0">[+Active+]</option>
									<option value="1">[+Inactive+]</option>
								</select>
								</span> </label>
						</div>
					</fieldset>
					<p class="submit inline-edit-save"> <a accesskey="c" href="#inline-edit" title="[+Cancel+]" class="button-secondary cancel alignleft">[+Cancel+]</a>
						<input accesskey="s" type="submit" name="bulk_edit" id="bulk_edit" class="button-primary alignright" value="[+Update+]"  />
						<input type="hidden" name="page" value="mla-settings-menu-image" />
						<input type="hidden" name="mla_tab" value="image" />
						<input type="hidden" name="screen" value="settings_page_mla-settings-menu-image" />
						<span class="error" style="display:none"></span> <br class="clear" />
					</p>
				</td>
			</tr>
		</tbody>
	</table>
</form>
