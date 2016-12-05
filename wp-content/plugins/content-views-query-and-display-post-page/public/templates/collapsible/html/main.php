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
$html		 = array();
$random_id	 = PT_CV_Functions::string_random();
$layout		 = $dargs[ 'layout-format' ];

if ( $layout == '2-col' && !isset( $dargs[ 'field-settings' ][ 'thumbnail' ] ) ) {
	$layout = '1-col';
}

$heading = isset( $fields_html[ 'title' ] ) ? $fields_html[ 'title' ] : '';
unset( $fields_html[ 'title' ] );

if ( $layout == '2-col' ) {
	$thumbnail = $fields_html[ 'thumbnail' ];
	unset( $fields_html[ 'thumbnail' ] );

	if ( apply_filters( PT_CV_PREFIX_ . '2col_separate', false ) ) {
		$fields_html = array( sprintf( '<div class="%s">%s</div>', PT_CV_PREFIX . '2colse', implode( '', $fields_html ) ) );
	}

	array_unshift( $fields_html, $thumbnail );
}

$html			 = $fields_html;
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
		echo implode( "\n", $html );
		?>
	</div>
</div>