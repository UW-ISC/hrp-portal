// defines Global ajaxurl if needed
var ajaxurl,  mlaDefineAjaxurl;

if ( typeof ajaxurl === 'undefined' ) {
	mlaDefineAjaxurl = mlaDefineAjaxurl || {}, mla_define_ajaxurl_vars;

	/**
	 * Localized settings and strings
	 */
	mlaDefineAjaxurl.settings = typeof mla_define_ajaxurl_vars === 'undefined' ? { defaultAjaxURL: '/wp-admin/admin-ajax.php' } : mla_define_ajaxurl_vars;
	mla_define_ajaxurl_vars = void 0; // delete won't work on Globals

	// Default location at worst
	ajaxurl = mlaDefineAjaxurl.settings.defaultAjaxURL;

	// See if we can do better
	try {
		if ( typeof window._wpMediaViewsL10n === 'object' ) {
			if ( typeof window._wpMediaViewsL10n.mla_strings === 'object' ) {
				if ( typeof window._wpMediaViewsL10n.mla_strings.ajaxurl === 'string' ) {
					ajaxurl = window._wpMediaViewsL10n.mla_strings.ajaxurl;
				} else {
					//console.log( 'mla-define-ajaxurl-scripts.js window._wpMediaViewsL10n.mla_strings.ajaxurl failed' );
				}
			} else {
				//console.log( 'mla-define-ajaxurl-scripts.js window._wpMediaViewsL10n.mla_strings failed' );
			}
		} else {
			//console.log( 'mla-define-ajaxurl-scripts.js window._wpMediaViewsL10n failed' );
		}
	} catch ( error ) {
		//console.log( 'mla-define-ajaxurl-scripts.js error thrown' );
	};
}
