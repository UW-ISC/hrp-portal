<!-- loaded in class-mla-main.php function mla_add_help_tab for the Media/Assistant submenu screen -->
<!-- invoked as /wp-admin/upload.php?page=mla-menu -->
<!-- template="mla-thumbnail-generation" -->
<!-- title="Thumbnail Generation" order="95" -->
<p>WordPress 4.7 and later generates thumbnail images for PDF documents as they are uploaded to the Media Library. You can use MLA's "WP" thumbnail generation feature to create or modify WordPress-style thumbnails for PDF documents.
</p>
<p>As an alternative to WordPress-style thumbnails, MLA lets you assign a "Featured Image" to your Media Library non-image items such as PDF documents. The "JPG" and "PNG" types generate thumbnails as Media Library items and assign them as Featured Images to their corresponding non-image items, although you can use any Media Library image as the Featured Image.</p>
<p>You can find more information regarding thumbnail generation in the Documentation section by clicking the link in the "For more information" list on the right.</p>
<p>You can use the following fields to control the thumbnail generation parameters:</p>
<table>
<tr>
<td class="mla-doc-table-label">Width</td>
<td>the maximum width in pixels (default "0" for WP 4.7+, "150" for earlier versions) of the thumbnail image. The height (unless also specified) will be adjusted to maintain the page proportions.</td>
</tr>
<tr>
<td class="mla-doc-table-label">Height</td>
<td>the maximum height in pixels (default "0") of the thumbnail image. The width (unless also specified) will be adjusted to maintain the page proportions.</td>
</tr>
<tr>
<td class="mla-doc-table-label">Best Fit</td>
<td>retain page proportions when both height and width are explicitly stated. If unchecked, the image will be stretched as required to exactly fit the height and width. If checked, the image will be reduced in size to fit within the bounds, but proportions will be preserved.</td>
</tr>
<tr>
<td class="mla-doc-table-label">Page</td>
<td>the page number (default "1") to be used for the thumbnail image. If the page does not exist for a particular document the first page will be used instead.</td>
</tr>
<tr>
<td class="mla-doc-table-label">Resolution</td>
<td>the pixels/inch resolution (default "128" for WP 4.7+, "72" for earlier versions) of the page before reduction.</td>
</tr>
<tr>
<td class="mla-doc-table-label">Quality</td>
<td>the compression quality (default 90) of the final page.</td>
</tr>
<tr>
<td class="mla-doc-table-label">Suffix</td>
<td>the suffix added to the source item's Title to create the thumbnail's Title. Suffix is ignored when generating WordPress-style thumbnails (Type "WP").</td>
</tr>
</table>
<p>You can use the following fields to control the thumbnail type and handling of existing thumbnails:</p>
<table>
<tr>
<td class="mla-doc-table-label">Type</td>
<td>the MIME type, <strong>"JPG"</strong> (image/jpeg, default) or <strong>"PNG"</strong> (image/png), of the final thumbnail. For these types, a new image file is generated and added as a Media Library item.
<div style="font-size:8px; line-height:8px">&nbsp;</div>
Select <strong>"WP"</strong> to generate WordPress-style thumbnails (for PDF documents). These are part of the PDF item itself, not a separate item.
<div style="font-size:8px; line-height:8px">&nbsp;</div>
Select <strong>"None"</strong> to skip the generation of a new thumbnail.</td>
<tr>
<td class="mla-doc-table-label">Existing Features</td>
<td>the action to take if an item already has a Featured Image. Select "<strong>Keep</strong>" to retain the Featured Image and not generate anything. Select "<strong>Ignore</strong>" to leave the old Featured Image, if any, unchanged. Select "<strong>Remove</strong>" to remove the old Featured Image but leaving the old image in the Media Library. Select "<strong>Trash</strong>" (if available) to remove the old Featured Image and move the old image item to the Media Trash. Select "<strong>Delete</strong>" to remove the old Featured Image and permanently delete the old image item.
</td>
</tr>
<tr>
<td class="mla-doc-table-label">Existing Thumbnails</td>
<td>the action to take if an item already has a native WordPress-style thumbnail (Type "WP"). Select "<strong>Keep</strong>" to retain the thumbnail and not generate anything. Select "<strong>Ignore</strong>" to leave the old thumbnail, if any, unchanged. Select <strong>"Delete"</strong> to delete the old thumbnail.
</td>
</tr>
</table>
<p>After you click Generate Thumbnails, the Media/Assistant submenu table will be refreshed to display the new thumbnails and any new Featured Image items generated and added to the Media Library. You can use Quick Edit and Bulk Edit to make additional changes to any new Featured Image items.</p>
<!-- template="sidebar" -->
<p><a href="[+settingsURL+]?page=mla-settings-menu-documentation&mla_tab=documentation#thumbnail_generation" target="_blank">MLA Documentation for Thumbnail Generation Support (Media/Assistant Bulk Action)</a></p>
<p><a href="[+settingsURL+]?page=mla-settings-menu-documentation&mla_tab=documentation#thumbnail_substitution" target="_blank">MLA Documentation for Thumbnail Substitution Support, mla_viewer</a></p>
