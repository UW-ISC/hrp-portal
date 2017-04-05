<!-- template="mla-progress-div" -->
<div class="wrap" id="mla-progress-div" style="display:none; border-bottom:1px solid #cccccc">
	<h2>[+Mapping Progress+]</h2>
	<p style="font-weight:bold">[+DO NOT+]:</p>
	<ol>
		<li>[+DO NOT Close+]</li>
		<li>[+DO NOT Reload+]</li>
		<li>[+DO NOT Click+]</li>
	</ol>
	<p style="font-weight:bold">[+Progress+]:</p>
	<div id="mla-progress-meter-div" style="padding: 3px; border: 1px solid rgb(101, 159, 255); border-image: none; width: 80%; height: 11px;">
		<div id="mla-progress-meter" style="width: 100%; height: 11px; text-align: center; color: rgb(255, 255, 255); line-height: 11px; font-size: 6pt; background-color: rgb(101, 159, 255);">100%
		</div>
	</div>
	<div id="mla-progress-message">&nbsp;</div>
	<p class="submit inline-edit-save">
		<a title="[+Pause+]" class="button-secondary alignleft" id="mla-progress-pause" accesskey="p" href="#mla-progress">[+Pause+]</a>
		<a title="[+Cancel+]" class="button-secondary alignleft" id="mla-progress-cancel" accesskey="c" href="#mla-progress">[+Cancel+]</a>
		<a title="[+Resume+]" class="button-secondary alignleft" id="mla-progress-resume" accesskey="r" href="#mla-progress">[+Resume+]</a>
		<input name="mla_resume_offset" id="mla-progress-offset" type="text" size="3" />
		<a title="[+Close+]" class="button-primary alignright" id="mla-progress-close" accesskey="x" href="#mla-progress">[+Close+]</a>
		<a title="[+Refresh+]" class="button-primary alignright" id="mla-progress-refresh" accesskey="f" href="[+refresh_href+]">[+Refresh+]</a>
		<span class="spinner"></span>
		<span id="mla-progress-error" style="display:inline"></span><br class="clear" />
	</p>
</div>

<!-- template="iptc-exif-tab" -->
[+mla-progress-div+]
<h2>[+IPTX/EXIF Options+]</h2>
<p>
[+In this tab+]
</p>
<p>
[+You can find+]
</p>
<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-iptc-exif-tab">
&nbsp;<br />
[+options_list+]
<h3>[+Standard field mapping+] <input name="iptc-exif-options-process-standard" class="button-secondary mla-mapping" id="iptc-exif-options-process-standard" type="submit" value="[+Map Standard Fields+]" /></h3>
    <table class="optiontable">
[+standard_options_list+]
	</table>
<h3>[+Taxonomy term mapping+] <input name="iptc-exif-options-process-taxonomy" class="button-secondary mla-mapping" id="iptc-exif-options-process-taxonomy" type="submit" value="[+Map Taxonomy Terms+]" /></h3>
    <table class="optiontable">
[+taxonomy_options_list+]
	</table>
<h3>[+Custom field mapping+] <input name="iptc-exif-options-process-custom" class="button-secondary mla-mapping" id="iptc-exif-options-process-custom" type="submit" value="[+Map Custom Fields+]" /></h3>
    <table class="optiontable">
[+custom_options_list+]
	</table>
    <p class="submit mla-settings-submit">
        <input name="iptc-exif-options-save" class="button-primary" id="iptc-exif-options-save" type="submit" value="[+Save Changes+]" />
	<div class="mla-settings-help">[+Click Save Changes+]</div>
    </p>
[+_wpnonce+]
[+_wp_http_referer+]
</form>
