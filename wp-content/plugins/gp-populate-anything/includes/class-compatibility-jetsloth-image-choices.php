<?php

class GPPA_Compatibility_JetSloth_Image_Choices {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		if ( ! function_exists( 'gf_image_choices' ) ) {
			return;
		}

		add_filter( 'gppa_input_choice', array( $this, 'add_image_to_choice' ), 10, 4 );
		add_action( 'gform_editor_js', array( $this, 'add_image_choice_template' ), 1 );

		remove_filter( 'gform_footer_init_scripts_filter', array( gf_image_choices(), 'add_inline_options_label_lookup' ) );
		add_filter( 'gform_footer_init_scripts_filter', array( $this, 'add_inline_options_label_lookup' ), 10, 3 );
	}

	public function add_image_to_choice( $choice, $field, $object, $objects ) {
		$templates = rgar( $field, 'gppa-choices-templates', array() );

		if ( rgar( $templates, 'imageChoices_image' ) ) {
			$choice['imageChoices_image'] = gp_populate_anything()->process_template( $field, 'imageChoices_image', $object, 'choices', $objects );

			/* Only use attachment_url_to_postid() if the Lightbox is used otherwise it's a performance waste. */
			if ( rgar( $field, 'imageChoices_useLightbox' ) ) {
				/* In lieu of another template row for choices, just try to get the attachment ID from the URL. */
				$attachment_id = attachment_url_to_postid( $choice['imageChoices_image'] );

				if ( $attachment_id ) {
					$choice['imageChoices_imageID'] = $attachment_id;
				}
			} else {
				$choice['imageChoices_imageID'] = '';
			}
		}

		return $choice;
	}

	public function add_image_choice_template() {
		?>
		<script type="text/javascript">
			window.gform.addFilter( 'gppa_template_rows', function ( templateRows, field, populate ) {
				if ( populate !== 'choices' ) {
					return templateRows;
				}

				if ( typeof window['imageChoicesAdmin'] !== 'undefined' && imageChoicesAdmin.fieldCanHaveImages( field ) ) {
					templateRows.push( {
						id: 'imageChoices_image',
						label: '<?php echo esc_js( __( 'Image', 'gp-populate-anything' ) ); ?>',
						required: false,
						shouldShow: function( field, populate ) {
							if ( populate !== 'choices' ) {
								return false;
							}

							return ! ! field['imageChoices_enableImages'];
						},
					} );
				}

				return templateRows;
			} );
		</script>
		<?php
	}

	/**
	 * Intercept JetSloth Image Choices method to properly hydrate the form and its choices.
	 *
	 * @param string $form_string
	 * @param array $form
	 * @param number $current_page
	 *
	 * @return string
	 */
	public function add_inline_options_label_lookup( $form_string, $form, $current_page ) {
		$form = gp_populate_anything()->populate_form( $form );

		// @phpstan-ignore-next-line
		return gf_image_choices()->add_inline_options_label_lookup( $form_string, $form, $current_page );
	}

}


function gppa_compatibility_jetsloth_image_choices() {
	return GPPA_Compatibility_JetSloth_Image_Choices::get_instance();
}
