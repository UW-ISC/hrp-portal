var wp, acf, jQuery, _,
	mlaACFModal = {
		// Properties
		strings: {},
		settings: {},
	},
	mlaModal;

( function( $ ){
	var acfGallery = typeof acf.models.GalleryField === 'undefined' ? null : acf.models.GalleryField;
/*	for debug : trace every event triggered in the ACF GalleryField model * /
	var originalACFTrigger = acf.models.GalleryField.prototype.trigger;
	acf.models.GalleryField.prototype.trigger = function(){
		console.log('ACF Event: ', arguments[0]);
		originalACFTrigger.apply(this, Array.prototype.slice.call(arguments));
	} // */

	/**
	 * Localized settings and strings
	 */
	mlaACFModal.strings = typeof wp.media.view.l10n.mla_acf_strings === 'undefined' ? {} : wp.media.view.l10n.mla_acf_strings;
	delete wp.media.view.l10n.mla_acf_strings;

	mlaACFModal.settings = typeof wp.media.view.settings.mla_acf_settings === 'undefined' ? {} : wp.media.view.settings.mla_acf_settings;
	delete wp.media.view.settings.mla_acf_settings;

	// Do not proceed unless the MLA enhancements have been enabled!
	if ( typeof mlaModal.settings === 'undefined' ) {
		return;
	}
	
	if ( ! ( mlaModal.settings.enableMediaGrid || mlaModal.settings.enableMediaModal ) ) {
		return;
	}

	/*
	 * We can extend the AttachmentCompat object because it's not instantiated until
	 * the sidebar is created for a selected attachment.
	 */
	if ( mlaModal.settings.enableDetailsCategory || mlaModal.settings.enableDetailsTag ) {
		if ( null !== acfGallery ) {
			acf.models.GalleryField = acf.models.GalleryField.extend({
				onClickSelect: function( t, e ) {
					var thisGallery = this.data['key'];
					
					// Call the base method in the super class
					acfGallery.prototype.onClickSelect.apply( this, arguments );
	
					$( document ).one( 'ajaxSuccess', function( event ) {
						context = $( '#acf-' + thisGallery );
						mlaModal.utility.hookCompatTaxonomies( e.data('id'), context );
					});

					return this;
				},
			});
		}
	}
}( jQuery ) );
