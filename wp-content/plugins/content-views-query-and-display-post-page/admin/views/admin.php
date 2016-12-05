<?php
/**
 * Setting page
 *
 * @package   PT_Content_Views_Admin
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<style>
		.wrap > .row {
			padding-bottom: 20px;
		}

		.wrap p, .wrap form {
			font-size: 14px;
		}

		.wrap h3 {
			font-size:   16px;
			font-weight: bold;
			color:       #FF6A5A;
		}

		.wrap h6 {
			font-size:   15px;
			font-weight: bold;
		}

		.wrap img {
			max-width: 100%;
		}

		.wrap .label-for-option {
			font-weight:   normal;
			margin:        auto;
			margin-bottom: -5px;
			margin-left:   4px;
		}
	</style>

	<?php
	PT_CV_Plugin::settings_page_section_one();
	// Settings form
	PT_CV_Plugin::settings_page_form();

	PT_CV_Plugin::settings_page_section_two();
	?>
</div>
