var jQuery,
	mla_dismiss_notice_vars,
	mlaDismissNotice = {
		// Properties
		// mlaDismissNotice.settings.dismissNoticeAction
		// mlaDismissNotice.settings.ajaxNonce
		settings: {},

		// Components
		dismissNotice: null
	};

( function( $ ) {
	// Localized settings and strings
	mlaDismissNotice.settings = typeof mla_dismiss_notice_vars === 'undefined' ? {} : mla_dismiss_notice_vars;
	mla_dismiss_notice_vars = void 0; // delete won't work on Globals
	mlaDismissNotice.settings.sectionText = [];

	mlaDismissNotice.dismissNotice = {
		init : function(){
			$('.mla-admin-notice' ).each( function(){
				$( this ).on( 'click', '.notice-dismiss', function(){
					var post, id = $( this ).closest( '.mla-admin-notice' ).attr( 'mla-notice-id' );
					post = {
						_ajax_nonce: mlaDismissNotice.settings.ajaxNonce,
						action: mlaDismissNotice.settings.dismissNoticeAction,
						notice_id: id,
					};
					$.ajax( ajaxurl, {
						type: 'POST',
						data: post,
					});
				});
			});
		}
	}; // mlaDismissNotice.dismissNotice

	$( document ).ready( function() {
		mlaDismissNotice.dismissNotice.init();
	});
})( jQuery );
