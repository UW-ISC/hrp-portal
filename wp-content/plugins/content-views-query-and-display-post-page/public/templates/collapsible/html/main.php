<?php
/**
 * Layout Name: Collapsible List
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
$random_id		 = PT_CV_Functions::string_random();
$heading		 = isset( $fields_html[ 'title' ] ) ? $fields_html[ 'title' ] : '';
unset( $fields_html[ 'title' ] );
?>

<div class="panel-heading">
	<a class="panel-title" data-toggle="collapse" data-parent="#<?php echo esc_attr( PT_CV_PREFIX_UPPER . 'ID' ); ?>" href="#<?php echo esc_attr( $random_id ); ?>">
		<?php
		$allowable_tags	 = (array) apply_filters( PT_CV_PREFIX_ . 'collapsible_heading_tags', array( '<b>', '<br>', '<code>', '<em>', '<i>', '<img>', '<big>', '<small>', '<span>', '<strong>', '<sub>', '<sup>', '<label>', '<cite>', ) );
		echo strip_tags( $heading, implode( '', $allowable_tags ) );
		?>
	</a>
	<?php
	echo apply_filters( PT_CV_PREFIX_ . 'scrollable_toggle_icon', '' );
	?>
</div>
<div id="<?php echo esc_attr( $random_id ); ?>" class="panel-collapse collapse <?php echo esc_attr( PT_CV_PREFIX_UPPER . 'CLASS' ); ?>">
	<div class="panel-body">
		<?php
		echo implode( "\n", $fields_html );
		?>
	</div>
</div>