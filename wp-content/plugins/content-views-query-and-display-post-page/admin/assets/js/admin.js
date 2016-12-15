/**
 * Main script file for WP admin
 *
 * @package   PT_Content_Views_Admin
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */

( function ( $ ) {
	"use strict";

	$.PT_CV_Admin = $.PT_CV_Admin || { };
	PT_CV_ADMIN = PT_CV_ADMIN || { };
	ajaxurl = ajaxurl || { };
	var _prefix = PT_CV_ADMIN._prefix;

	$.PT_CV_Admin = function ( options ) {
		this.options = $.extend( {
			onload: 1,
			scroll_time: 500,
			can_preview: 1
		}, options );

		this.init();
	};

	$.PT_CV_Admin.prototype = {
		init: function () {
			this.custom();

			// Validate number
			this.validate_number();
			// Select2
			$( 'select.' + _prefix + 'select2' ).select2();

		},
		custom: function () {
			var $self = this;

			this.preview();
			this._preview_btn_toggle();
			this._content_type();
			this._toggle_taxonomy_relation();
			this._thumbnail_settings();

			// Toggle panel
			this._toggle_panel( '.' + _prefix + 'group .panel-heading' );
			// 'Advance Settings'
			this.toggle_group( '.' + _prefix + 'advanced-settings-item' );
			// 'Terms' (in "Taxonomy Settings")
			this.toggle_group( '.' + _prefix + 'taxonomy-item' );
			// 'Content type'
			this.toggle_group( '[name="' + _prefix + 'content-type' + '"]', false );
			// 'View type settings'
			this.toggle_group( '[name="' + _prefix + 'view-type' + '"]', false );
			// Toggle dependencies
			this.dependence_do_all();

			// Prevent click on links
			$( '#' + _prefix + 'preview-box' ).on( 'click', 'a', function ( e ) {
				e.preventDefault();
			} );
			$( '.pt-accordion-a' ).click( function ( e ) {
				e.preventDefault();
			} );

			// Show alert when leave page without saving View
			var checked = 0;
			$( '#' + _prefix + 'form-view input[type="submit"]' + ',' + 'a[href*="action=duplicate"]' ).click( function () {
				checked = 1;
			} );
			window.onbeforeunload = function ( event ) {
				if ( !$self.options.onload && !checked ) {
					var message = 'The changes you made will be lost if you navigate away from this page.';
					if ( typeof event === 'undefined' ) {
						event = window.event;
					}
					if ( event ) {
						event.returnValue = message;
					}
					return message;
				}
			};

			// Handle Pagination actions
			$( 'body' ).bind( _prefix + 'admin-preview', function () {
				new $.PT_CV_Public();
			} );
		},
		/**
		 * Toggle panel when click Show/Hide icon on Heading
		 *
		 * @param {type} $selector
		 * @returns {undefined}
		 */
		_toggle_panel: function ( $selector ) {
			$( 'body' ).on( 'click', $selector, function ( e ) {
				var $heading = $( this );
				var $span = $heading.find( 'span.clickable' );
				var time = 100;

				if ( !$span.hasClass( 'panel-collapsed' ) ) {
					$heading.next( '.panel-body' ).slideUp( time );
					$span.addClass( 'panel-collapsed' );
					$span.find( 'i' ).removeClass( 'glyphicon-minus' ).addClass( 'glyphicon-plus' );
				} else {
					$heading.next( '.panel-body' ).slideDown( time );
					$span.removeClass( 'panel-collapsed' );
					$span.find( 'i' ).removeClass( 'glyphicon-plus' ).addClass( 'glyphicon-minus' );
				}
			} );
		},
		/**
		 * Toggle Taxonomy Relation setting on page load & on change
		 *
		 * @returns void
		 */
		_toggle_taxonomy_relation: function () {
			var $self = this;
			$self._do_toggle_taxonomy_relation();
			$( '.' + _prefix + 'taxonomy-item' ).change( function () {
				$self._do_toggle_taxonomy_relation();
			} );
		},
		/**
		 * Toggle Taxonomy Relation setting by number of selected taxonomies
		 *
		 * @returns {undefined}
		 */
		_do_toggle_taxonomy_relation: function () {
			var $taxonomy_relation = $( '.' + _prefix + 'taxonomy-relation' ).parent().parent( '.form-group' );
			var $wrap_taxonomies = $( '#' + _prefix + 'group-taxonomy' );

			// If there is no taxonomies
			var is_multi = false;
			if ( $wrap_taxonomies.find( '.' + _prefix + 'taxonomies .checkbox' ).filter( function () {
				return !$( this ).hasClass( 'hidden' ) && $( this ).find( 'input:checked' ).length;
			} ).length > 1 ) {
				$taxonomy_relation.removeClass( 'hidden' );
				is_multi = true;
			} else {
				$taxonomy_relation.addClass( 'hidden' );
			}

			$( '.pt-wrap' ).trigger( _prefix + 'multiple-taxonomies', [ is_multi ] );
		},
		/**
		 * Get field value, depends on field type & its parent is show/hide
		 *
		 * @param {type} el     : string to selector
		 * @returns {undefined}
		 */
		_get_field_val: function ( el ) {
			var $this = $( el );
			var value = $( el ).val();

			if ( $this.is( ':checkbox' ) || $this.is( ':radio' ) ) {
				value = $( el + ':checked' ).val();
			}

			return value;
		},
		/**
		 * Do toggle all dependency groups
		 *
		 * @returns {undefined}
		 */
		dependence_do_all: function () {
			var $self = this;
			var $toggle_data_js = $.parseJSON( $self.options._toggle_data );

			$.each( $toggle_data_js, function ( idx, obj ) {
				// Obj_sub: an object contains (dependence_id, operator, expect_val)
				$.each( obj, function ( key, obj_sub ) {
					// Get name of depended element (which other elements depend on it)
					var el_name = _prefix + key;

					var el = "[name='" + el_name + "']";

					// Run on page load
					$self._dependence_group( $self._get_field_val( el ), obj_sub );

					// Run on change
					$( el ).change( function () {
						$self._dependence_group( $self._get_field_val( el ), obj_sub );
					} );
				} );
			} );
		},
		/**
		 * Toggle each dependency group
		 * @param {type} this_val   : current value of depended element (which other elements depend on it)
		 * @param {type} obj_sub    : an object contains (dependence_id, expect_val, operator)
		 * @returns {undefined}
		 */
		_dependence_group: function ( this_val, obj_sub ) {
			var $self = this;
			$.each( obj_sub, function ( key, data ) {
				$self._dependence_element( data[0], this_val, data[2], data[1] );
			} );
		},
		/**
		 * Toggle each dependency element
		 *
		 * @param {type} dependence_id  : id of group A which depends on an element B
		 * @param {type} this_val   : current value of B
		 * @param {type} operator   : operator to comparing A's value & B's value : =, >, < ...
		 * @param {type} expect_val : expect value of B to show A group
		 * @returns {undefined}
		 */
		_dependence_element: function ( dependence_id, this_val, operator, expect_val ) {
			var dependence_el = $( "#" + dependence_id );
			var pass = 0;
			switch ( operator ) {
				case "=":
					{
						if ( typeof expect_val === 'string' )
							expect_val = [ expect_val ];
						pass = ( $.inArray( this_val, expect_val ) >= 0 );
					}
					break;
				case "!=":
					{
						if ( typeof expect_val === 'string' )
							expect_val = [ expect_val ];
						pass = ( $.inArray( this_val, expect_val ) < 0 );
					}
					break;
				default :
					if ( typeof expect_val !== 'object' )
						pass = eval( "this_val " + operator + " expect_val" );
					break;

			}
			var action = '';
			var result = 0;
			if ( pass ) {
				dependence_el.removeClass( 'hidden' );

				action = 'remove';
				result = !dependence_el.hasClass( 'hidden' );
			} else {
				dependence_el.addClass( 'hidden' );

				action = 'add';
				result = dependence_el.hasClass( 'hidden' );
			}

			// Log if something is wrong
			if ( !result )
				console.log( dependence_id, this_val, operator, expect_val, action );
		},
		/**
		 * Toggle a group inside Panel group when check/uncheck a checkbox inside checboxes list
		 *
		 * @param {type} selector
		 * @param {type} toggle
		 * @returns {undefined}
		 */
		toggle_group: function ( selector, toggle ) {
			var $self = this;
			// Run on page load
			$( selector ).each( function () {
				$self._toggle_each_group( $( this ), toggle );
			} );
			// Run on change
			$( selector ).each( function () {
				$( this ).change( function () {
					var this_ = $( this );
					setTimeout( function () {
						$self._toggle_each_group( this_, toggle );
					}, 200 );
				} );
			} );
		},
		/**
		 * Toggle group depends on selector value
		 *
		 * @param {type} $this
		 * @param {type} toggle
		 * @returns {undefined}
		 */
		_toggle_each_group: function ( $this, toggle ) {
			var $self = this;
			if ( $this.is( 'select' ) || ( ( $this.is( ':checkbox' ) || $this.is( ':radio' ) ) && $this.is( ':checked' ) ) ) {
				// Get id of element A which needs to toggle
				var toggle_id = '#' + PT_CV_ADMIN._group_prefix + $this.val();

				// Get siblings groups of A
				var other_groups = $( toggle_id ).parent().children( '.' + _prefix + 'group' ).not( toggle_id );

				if ( $( toggle_id ).hasClass( _prefix + 'only-one' ) ) {
					// Hide other group in a same Panel group
					other_groups.addClass( 'hidden' );
				} else {
				}

				// Show group
				$( toggle_id ).removeClass( 'hidden' );

				// Show the content
				$( toggle_id ).find( '.panel-body' ).show();

				// Scroll to
				if ( toggle !== false && !$self.options.onload && $( toggle_id ).offset() ) {
					$( 'html, body' ).animate( {
						scrollTop: $( toggle_id ).offset().top - 40
					}, $self.options.scroll_time );
				}

				// Highlight color
				var activate_group = _prefix + 'group-activate';
				$( toggle_id ).addClass( activate_group );

				// Remove highlight color
				setTimeout( function () {
					$( toggle_id ).removeClass( activate_group );
				}, 2000 );

			} else {
				$( '#' + PT_CV_ADMIN._group_prefix + $this.val() ).addClass( 'hidden' );
			}
		},
		/**
		 * Custom function for 'Content Type'
		 *
		 * @returns {undefined}
		 */
		_content_type: function () {
			var $self = this;
			var $wrap_taxonomies = $( '#' + _prefix + 'group-taxonomy' );
			var $taxonomy_other_settings = $( '.' + _prefix + 'taxonomy-extra' );
			var $taxonomies = $( '.' + _prefix + 'taxonomy-item' );

			var $no_taxonomy = $( '<div/>', {
				'id': _prefix + 'no-taxonomy',
				'class': _prefix + 'text',
				'text': PT_CV_ADMIN.text.no_taxonomy
			} ).appendTo( $( '.' + _prefix + 'taxonomies' ) );

			var fn_taxonomy_hide = function ( taxonomies ) {
				// Hide all taxonomy checkboxes
				taxonomies.each( function () {
					$( this ).parents( '.checkbox' ).addClass( 'hidden' );
				} );

				$no_taxonomy.addClass( 'hidden' );

				// Hide all sections of taxonomies
				$( '.panel-group.terms' ).find( '.' + _prefix + 'group' ).addClass( 'hidden' );
			};

			// Run on page load
			fn_taxonomy_hide( $taxonomies );

			// For content type
			var content_type = '[name="' + _prefix + 'content-type' + '"]';
			var fn_content_type = function ( is_change ) {
				var this_val;
				if ( $( content_type ).is( 'input:radio' ) ) {
					this_val = $( content_type + ':checked' ).val();
				} else {
					this_val = $( content_type ).val();
				}

				if ( typeof this_val === 'undefined' ) {
					return;
				}

				if ( is_change ) {
					// Uncheck all checkbox of taxonomies
					$taxonomies.attr( 'checked', false );

					// Toggle Taxonomy Relation setting
					$self._do_toggle_taxonomy_relation();
				}

				// Show taxonomies of selected post type
				if ( this_val !== '' ) {
					fn_taxonomy_hide( $taxonomies );

					$taxonomies.filter( function () {
						var val = $( this ).val();
						var $taxonomies_of_this = PT_CV_ADMIN.data.post_types_vs_taxonomies[this_val] || '';
						return $.inArray( val, $taxonomies_of_this ) >= 0;
					} ).parents( '.checkbox' ).removeClass( 'hidden' );
				}

				// Show message "there is no taxonomy"
				if ( $wrap_taxonomies.find( '.' + _prefix + 'taxonomies .checkbox' ).filter( function () {
					return !$( this ).hasClass( 'hidden' );
				} ).length === 0 ) {
					$no_taxonomy.removeClass( 'hidden' );
					$taxonomy_other_settings.addClass( 'hidden' );
				}

				// Trigger custom actions
				$( '.pt-wrap' ).trigger( 'content-type-change', [ this_val ] );
			};

			// Run on page load
			fn_content_type();

			// Run on change
			$( content_type ).change( function () {
				fn_content_type( 1 );
			} );
		},
		/**
		 * Preview handle
		 *
		 * @returns {undefined}
		 */
		preview: function () {
			var $self = this;
			var offset_top;

			$( '#' + _prefix + 'show-preview' ).click( function ( e ) {
				e.stopPropagation();
				e.preventDefault();

				$( 'body' ).trigger( _prefix + 'admin-preview-start' );

				var $this_btn = $( this );

				// Get Preview box
				var $preview = $( '#' + _prefix + 'preview-box' );

				// Show/hide Preview box
				if ( $self.options.can_preview ) {
					$preview.addClass( 'in' );
				} else {
					$preview.removeClass( 'in' );
				}

				/**
				 * Animation
				 */
				// Scroll to preview box if want to show it
				if ( $self.options.can_preview ) {
					// Get current offset top to go back later
					offset_top = $( document ).scrollTop();

					// Scroll to preview box
					$( 'html, body' ).animate( {
						scrollTop: $preview.offset().top - 100
					}, $self.options.scroll_time );

					/// Send request
					$preview.css( 'opacity', '0.2' );
					// Show loading icon
					$preview.next().removeClass( 'hidden' );

					// Get settings data
					var data = $( '#' + _prefix + 'form-view' ).serialize();
					// Call handle function
					$self._preview_request( $preview, data, $this_btn );
				} else {
					// Scroll to previous position
					$( 'html, body' ).animate( {
						scrollTop: offset_top
					}, $self.options.scroll_time );

					// Toggle text of this button
					$this_btn.html( PT_CV_ADMIN.btn.preview.show );

					// Enable preview
					setTimeout( function () {
						$self.options.can_preview = 1;
					}, $self.options.scroll_time );
				}
			} );
		},
		/**
		 * Send preview Ajax request
		 *
		 * @param {object} preview_box The jqurey object
		 * @param {string} _data
		 * @param {object} $this_btn The Show/Hide preview button
		 * @returns void
		 */
		_preview_request: function ( preview_box, _data, $this_btn ) {
			var $self = this;
			var data = {
				action: 'preview_request',
				data: _data,
				ajax_nonce: PT_CV_ADMIN._nonce
			};

			// Sent POST request
			$.ajax( {
				type: "POST",
				url: ajaxurl,
				data: data
			} ).done( function ( response ) {
				var reload = false;
				if ( response == -1 || response == 0 ) {
					reload = true;
					response = "Your session has expired. This page will be reloaded.";
				}

				preview_box.css( 'opacity', '1' );

				// Hide loading icon
				preview_box.next().addClass( 'hidden' );

				// Update content of Preview box
				preview_box.html( response );

				if ( reload ) {
					location.reload();
				}

				$self._filter_response( preview_box );

				// Toggle text of this button
				$this_btn.html( PT_CV_ADMIN.btn.preview.hide );

				// Disable preview
				$self.options.can_preview = 0;

				// Trigger action, to recall function such as pagination, pinterest render layout...
				$( 'body' ).trigger( _prefix + 'admin-preview' );
			} );
		},
		_filter_response: function ( preview_box ) {
			var fn_alert_visible_sc = function () {
				$( '.' + _prefix + 'content', preview_box ).each( function () {
					var tclass = _prefix + 'caution';
					$( '.' + tclass ).remove();

					var content = $( this ).html();
					var regex = /\[[^\]]+\]/;
					if ( regex.test( content ) ) {
						$( '<div />', { html: PT_CV_ADMIN.text.visible_shortcode, class: tclass } ).insertBefore( preview_box );
						return false;
					}
				} );
			};
			fn_alert_visible_sc();

			$( 'body' ).trigger( _prefix + 'preview-response', [ preview_box ] );
		},
		/**
		 * Toggle 'Thumbnail settings'
		 *
		 * @returns {undefined}
		 */
		_thumbnail_settings: function () {
			// Toggle 'Thumbnail settings' when change 'Layout format'
			var fn_thumbnail_setting = function ( this_val ) {
				var $show_thumbnail = $( '[name="' + _prefix + 'show-field-thumbnail' + '"]' );

				// Force to show thumbnail IF it is not enabled
				if ( this_val === '2-col' && !$show_thumbnail.is( ':checked' ) ) {
					$show_thumbnail.trigger( 'click' );
				}
			};

			var layout_format = '[name="' + _prefix + 'layout-format' + '"]';

			// Run on page load
			fn_thumbnail_setting( $( layout_format + ':checked' ).val() );

			// Run on change
			$( layout_format ).change( function () {
				fn_thumbnail_setting( $( layout_format + ':checked' ).val() );
			} );

			/**
			 * Toggle 'Layout format' when change 'View type'
			 *
			 * @param {type} this_val
			 * @param {type} layout_format
			 * @returns {undefined}
			 */
			var view_type = '[name="' + _prefix + 'view-type' + '"]';
			var fn_layout_format = function ( layout_format ) {
				var this_val;
				if ( $( view_type ).is( 'input:radio' ) ) {
					this_val = $( view_type + ':checked' ).val();
				} else {
					this_val = $( view_type ).val();
				}

				var expect_val = [ 'scrollable' ];

				// Add more layouts
				$( '.pt-wrap' ).trigger( 'toggle-layout-format', [ expect_val ] );

				if ( $.inArray( this_val, expect_val ) >= 0 ) {
					// Trigger select 1-col
					$( layout_format + '[value="1-col"]' ).trigger( 'click' );
					// Disable 2-col
					$( layout_format + '[value="2-col"]' ).attr( 'disabled', true );
				} else {
					// Enable 2-col
					$( layout_format + '[value="2-col"]' ).attr( 'disabled', false );
				}
			};

			// Run on page load
			fn_layout_format( layout_format );

			// Run on change
			$( view_type ).change( function () {
				fn_layout_format( layout_format );
			} );
		},
		/**
		 * Toggle text of Preview button
		 * @returns {undefined}
		 */
		_preview_btn_toggle: function () {
			var $self = this;

			var _fn = function ( is_trigger ) {
				if ( !is_trigger ) {
					$self.options.onload = 0;
				}

				// Toggle text of this button
				$( '#' + _prefix + 'show-preview' ).html( PT_CV_ADMIN.btn.preview.update );

				// Enable preview
				$self.options.can_preview = 1;
			};
			// Bind on change input after page load
			$( '.pt-wrap .tab-content' ).on( 'change', 'input, select, textarea', function ( evt, is_trigger ) {
				_fn( is_trigger );
			} );

			$( 'body' ).bind( _prefix + 'preview-btn-toggle', function () {
				_fn();
			} );
		},
		/**
		 * Validate number: prevent negative value
		 * @returns {undefined}
		 */
		validate_number: function () {
			$( 'input[type="number"]' ).on( 'keypress', function ( event ) {
				var min = $( this ).prop( 'min' );
				if ( min == 0 && !( event.charCode >= 48 && event.charCode <= 57 ) )
					event.preventDefault();
			} );
		}
	};

}( jQuery ) );