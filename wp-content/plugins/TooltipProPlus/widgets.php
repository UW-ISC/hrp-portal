<?php

class CMTT_RandomTerms_Widget extends WP_Widget {

	public static function init() {
		add_action( 'widgets_init', create_function( '', 'return register_widget("' . get_class() . '");' ) );
	}

	/**
	 * Create widget
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'CMTT_RandomTerms_Widget', 'description' => 'Show random glossary terms' );
		parent::__construct( 'CMTT_RandomTerms_Widget', 'Glossary Random Terms', $widget_ops );
	}

	/**
	 * Widget options form
	 * @param WP_Widget $instance
	 */
	public function form( $instance ) {
		$instance	 = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 5, 'glink' => '', 'slink' => 'yes' ) );
		$title		 = $instance[ 'title' ];
		$count		 = $instance[ 'count' ];
		$glink		 = $instance[ 'glink' ];
		$slink		 = $instance[ 'slink' ];
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'count' ); ?>">Number of Terms: <input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $count ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'glink' ); ?>">Glossary Link Title: <input class="widefat" id="<?php echo $this->get_field_id( 'glink' ); ?>" name="<?php echo $this->get_field_name( 'glink' ); ?>" type="text" value="<?php echo esc_attr( $glink ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'slink' ); ?>">Show Tooltip for Terms:</br>
				<input id="<?php echo $this->get_field_id( 'slink' ); ?>" name="<?php echo $this->get_field_name( 'slink' ); ?>" type="radio" <?php
				if ( $slink == 'yes' )
					echo 'checked="checked"';
				?> value="yes" /> Yes</br>
				<input id="<?php echo $this->get_field_id( 'slink' ); ?>" name="<?php echo $this->get_field_name( 'slink' ); ?>" type="radio" <?php
				if ( $slink == 'no' )
					echo 'checked="checked"';
				?> value="no" />  No</br>
			</label></p>
		<?php
	}

	/**
	 * Update widget options
	 * @param WP_Widget $new_instance
	 * @param WP_Widget $old_instance
	 * @return WP_Widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance			 = $old_instance;
		$instance[ 'title' ] = $new_instance[ 'title' ];
		$instance[ 'count' ] = $new_instance[ 'count' ];
		$instance[ 'glink' ] = $new_instance[ 'glink' ];
		$instance[ 'slink' ] = $new_instance[ 'slink' ];
		return $instance;
	}

	/**
	 * Render widget
	 *
	 * @param array $args
	 * @param WP_Widget $instance
	 */
	public function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );

		echo $before_widget;
		$title = empty( $instance[ 'title' ] ) ? ' ' : apply_filters( 'widget_title', $instance[ 'title' ] );

		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}


		// WIDGET CODE GOES HERE
		$queryArgs = array(
			'post_type'		 => 'glossary',
			'post_status'	 => 'publish',
			'posts_per_page' => $instance[ 'count' ] > 0 ? $instance[ 'count' ] : 5,
			'orderby'		 => 'rand'
		);
		global $wp_version;
		if ( version_compare( $wp_version, '4.4', '>=' ) ) {
			$queryArgs[ 'orderby' ] = 'RAND(' . time() . ')';
		}
		$query = new WP_Query( $queryArgs );
		echo '<ul class="glossary_randomterms_widget">';
		foreach ( $query->get_posts() as $term ) {
			$tooltipPart = '';

			/*
			 * Check if we display tooltip at all
			 */
			$slink = $instance[ 'slink' ];
			if ( $slink == 'yes' ) {

				/*
				 * In case we do where we take the content from
				 */
				if ( get_option( 'cmtt_glossaryTooltip' ) == 1 ) {

					if ( get_option( 'cmtt_glossaryExcerptHover' ) && $term->post_excerpt ) {
						$glossaryItemContent = $term->post_excerpt;
					} else {
						$glossaryItemContent = $term->post_content;
					}
					$glossaryItemContent = CMTT_Pro::cmtt_glossary_filterTooltipContent( $glossaryItemContent, get_permalink( $term->ID ) );
					if ( get_option( 'cmtt_glossary_addSynonymsTooltip' ) == 1 ) {
						$synonyms = CMTT_Synonyms::getSynonyms( $term->ID );
						if ( !empty( $synonyms ) ) {
							$glossaryItemContent.=esc_attr( '<br /><strong>' . get_option( 'cmtt_glossary_addSynonymsTitle' ) . '</strong> ' . $synonyms );
						}
					}
					$tooltipPart = ' data-cmtooltip="' . $glossaryItemContent . '"';
				}
				echo '<li><a href="' . get_permalink( $term->ID ) . '" class="glossaryLink"' . $tooltipPart . '>' . $term->post_title . '</a></li>';
			} else {
				/*
				 * We do not display tooltip just link to term
				 */
				echo '<li><a href="' . get_permalink( $term->ID ) . '" >' . $term->post_title . '</a></li>';
			}
		}

		$glink		 = $instance[ 'glink' ];
		$mainPageId	 = get_option( 'cmtt_glossaryID' );

		if ( !empty( $glink ) && $mainPageId > 0 )
			echo '<li><a href="' . get_permalink( $mainPageId ) . '">' . $glink . '</a></li>';

		echo '</ul>';
		echo $after_widget;
	}

}

class CMTT_Search_Widget extends WP_Widget {

	public static function init() {
		add_action( 'widgets_init', create_function( '', 'return register_widget("' . get_class() . '");' ) );
	}

	/**
	 * Create widget
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'CMTT_Search_Widget', 'description' => 'Show search box for glossary term items' );
		parent::__construct( 'CMTT_Search_Widget', 'Glossary Search Widget', $widget_ops );
	}

	/**
	 * Widget options form
	 * @param WP_Widget $instance
	 */
	public function form( $instance ) {
		$instance	 = wp_parse_args( (array) $instance,
		array(
			'title' => '',
			'label' => '',
			'buttonlabel' => '',
			'hide_abbrevs' => 0,
			'hide_synonyms' => 0,
			) );
		$title		 = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
		$label		 = isset( $instance[ 'label' ] ) ? $instance[ 'label' ] : '';
		$buttonlabel = isset( $instance[ 'buttonlabel' ] ) ? $instance[ 'buttonlabel' ] : '';
		$hide_abbrevs = isset( $instance[ 'hide_abbrevs' ] ) ? $instance[ 'hide_abbrevs' ] : '';
		$hide_synonyms = isset( $instance[ 'hide_synonyms' ] ) ? $instance[ 'hide_synonyms' ] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				Title: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
			<label for="<?php echo $this->get_field_id( 'label' ); ?>">
				Search label: <input class="widefat" id="<?php echo $this->get_field_id( 'label' ); ?>" name="<?php echo $this->get_field_name( 'label' ); ?>" type="text" value="<?php echo esc_attr( $label ); ?>" />
			</label>
			<label for="<?php echo $this->get_field_id( 'buttonlabel' ); ?>">
				Button label: <input class="widefat" id="<?php echo $this->get_field_id( 'buttonlabel' ); ?>" name="<?php echo $this->get_field_name( 'buttonlabel' ); ?>" type="text" value="<?php echo esc_attr( $buttonlabel ); ?>" />
			</label>
			<label for="<?php echo $this->get_field_id( 'hide_abbrevs' ); ?>"><?php _e( 'Don\'t search in abbreviations' ); ?>
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'hide_abbrevs' ); ?>" name="<?php echo $this->get_field_name( 'hide_abbrevs' ); ?>" value="1" <?php checked( $hide_abbrevs ); ?> />
			</label><br/>
			<label for="<?php echo $this->get_field_id( 'hide_synonyms' ); ?>"><?php _e( 'Don\'t search in synonyms/variations' ); ?>
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'hide_synonyms' ); ?>" name="<?php echo $this->get_field_name( 'hide_synonyms' ); ?>" value="1" <?php checked( $hide_synonyms ); ?> />
			</label><br/>
		</p>
		<?php
	}

	/**
	 * Update widget options
	 * @param WP_Widget $new_instance
	 * @param WP_Widget $old_instance
	 * @return WP_Widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance					 = $old_instance;
		$instance[ 'title' ]		 = $new_instance[ 'title' ];
		$instance[ 'label' ]		 = $new_instance[ 'label' ];
		$instance[ 'buttonlabel' ]	 = $new_instance[ 'buttonlabel' ];
		$instance[ 'hide_abbrevs' ]	 = $new_instance[ 'hide_abbrevs' ];
		$instance[ 'hide_synonyms' ]	 = $new_instance[ 'hide_synonyms' ];
		return $instance;
	}

	/**
	 * Render widget
	 *
	 * @param array $args
	 * @param WP_Widget $instance
	 */
	public function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );

		echo $before_widget;

		$title				 = empty( $instance[ 'title' ] ) ? ' ' : apply_filters( 'widget_title', $instance[ 'title' ] );
		$searchLabel		 = empty( $instance[ 'label' ] ) ? __( 'Search', 'cm-tooltip-glossary' ) : $instance[ 'label' ];
		$searchButtonLabel	 = empty( $instance[ 'buttonlabel' ] ) ? __( 'Search', 'cm-tooltip-glossary' ) : $instance[ 'buttonlabel' ];
		$hideAbbrevs	 = empty( $instance[ 'hide_abbrevs' ] ) ? 0 : $instance[ 'hide_abbrevs' ];
		$hideSynonyms	 = empty( $instance[ 'hide_synonyms' ] ) ? 0 : $instance[ 'hide_synonyms' ];

		$mainPageId		 = CMTT_Glossary_Index::getGlossaryIndexPageId();
		$mainPageLink	 = get_permalink( $mainPageId );
		$searchTerm		 = (string) filter_input( INPUT_POST, 'search_term' );

		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		?>
		<div class="glossary_search_widget">
			<form action="<?php echo $mainPageLink ?>" method="post">
				<span><?php echo $searchLabel ?></span>
				<input value="<?php echo $searchTerm ?>" class="glossary-widget-search-term" name="search_term" id="glossary-widget-search-term" />
				<input type="hidden" class="glossary-hide-abbrevs" name="hide_abbrevs" value="<?php echo (int) ($hideAbbrevs); ?>" />
				<input type="hidden" class="glossary-hide-synonyms" name="hide_synonyms" value="<?php echo (int) ($hideSynonyms); ?>" />
				<input type="submit" value="<?php echo $searchButtonLabel ?>" id="glossary-search" class="glossary-search" />
			</form>
		</div>
		<?php
		echo $after_widget;
	}

}

class CMTT_LatestTerms_Widget extends WP_Widget {

	public static function init() {
		add_action( 'widgets_init', create_function( '', 'return register_widget("' . get_class() . '");' ) );
	}

	/**
	 * Create widget
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'cmtt_latestterms_widget widget_recent_entries', 'description' => 'Show latest glossary terms' );
		parent::__construct( 'cmtt_latestterms_widget', 'Glossary Latest Terms', $widget_ops );
	}

	/**
	 * Widget options form
	 * @param WP_Widget $instance
	 */
	public function form( $instance ) {
		$instance	 = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 5, 'length' => 0, 'ending' => '(...)', 'showExcerpt' => 'yes' ) );
		$title		 = $instance[ 'title' ];
		$count		 = $instance[ 'count' ];
		$length		 = $instance[ 'length' ];
		$ending		 = $instance[ 'ending' ];
		$showExcerpt = $instance[ 'showExcerpt' ];
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'count' ); ?>">Number of Terms: <input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $count ); ?>" /></label></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'showExcerpt' ); ?>">Show Excerpt/Content (excerpt if set - content otherwise): <br/>
				<input id="<?php echo $this->get_field_id( 'showExcerpt' ); ?>" name="<?php echo $this->get_field_name( 'showExcerpt' ); ?>" type="radio" <?php checked( 'yes', $showExcerpt ); ?> value="yes" /> Yes</br>
				<input id="<?php echo $this->get_field_id( 'showExcerpt' ); ?>" name="<?php echo $this->get_field_name( 'showExcerpt' ); ?>" type="radio" <?php checked( 'no', $showExcerpt ); ?> value="no" />  No</br>
			</label>
		</p>
		<p><label for="<?php echo $this->get_field_id( 'length' ); ?>">Excerpt/Content char limit (0 means no limit): <input class="widefat" id="<?php echo $this->get_field_id( 'length' ); ?>" name="<?php echo $this->get_field_name( 'length' ); ?>" type="text" value="<?php echo esc_attr( $length ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'ending' ); ?>">Excerpt/Content limit end markup: <input class="widefat" id="<?php echo $this->get_field_id( 'ending' ); ?>" name="<?php echo $this->get_field_name( 'ending' ); ?>" type="text" value="<?php echo esc_attr( $ending ); ?>" /></label></p>
		<?php
	}

	/**
	 * Update widget options
	 * @param WP_Widget $new_instance
	 * @param WP_Widget $old_instance
	 * @return WP_Widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance					 = $old_instance;
		$instance[ 'title' ]		 = $new_instance[ 'title' ];
		$instance[ 'count' ]		 = $new_instance[ 'count' ];
		$instance[ 'length' ]		 = $new_instance[ 'length' ];
		$instance[ 'ending' ]		 = $new_instance[ 'ending' ];
		$instance[ 'showExcerpt' ]	 = $new_instance[ 'showExcerpt' ];
		return $instance;
	}

	/**
	 * Render widget
	 *
	 * @param array $args
	 * @param WP_Widget $instance
	 */
	public function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );

		echo $before_widget;
		$title = empty( $instance[ 'title' ] ) ? ' ' : apply_filters( 'widget_title', $instance[ 'title' ] );

		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		// WIDGET CODE GOES HERE
		$queryArgs	 = array(
			'post_type'		 => 'glossary',
			'post_status'	 => 'publish',
			'posts_per_page' => $instance[ 'count' ] > 0 ? $instance[ 'count' ] : 5,
			'orderby'		 => 'date',
			'order'			 => 'DESC'
		);
		$query		 = new WP_Query( $queryArgs );
		?>
		<style>
			ul.glossary_latestterms_widget{

			}
			ul.glossary_latestterms_widget li {
				margin: 10px 0;
			}
			ul.glossary_latestterms_widget li .title {
				font-weight: bold;
				font-size: 11pt;
			}
			ul.glossary_latestterms_widget li div.description {
				font-size: 10pt;
			}
		</style>
		<?php
		echo '<ul class="glossary_latestterms_widget">';

		foreach ( $query->get_posts() as $term ) {
			echo '<li>';
			echo '<a class="title" href="' . get_permalink( $term->ID ) . '" >' . $term->post_title . '</a>';

			/*
			 * Check if we display tooltip at all
			 */
			$showExcerpt = $instance[ 'showExcerpt' ];
			if ( $showExcerpt == 'yes' ) {

				if ( $term->post_excerpt ) {
					$glossaryItemContent = $term->post_excerpt;
				} else {
					$glossaryItemContent = $term->post_content;
				}

				if ( $instance[ 'length' ] ) {
					$glossaryItemContent = cminds_truncate( $glossaryItemContent, $instance[ 'length' ], $instance[ 'ending' ] );
				}
				echo '<div class="description">' . $glossaryItemContent . '</div>';
			}
			echo '</li>';
		}
		echo $after_widget;
		echo '</ul>';
	}

}

/**
 * Based on core class used to implement a Categories widget.
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */
class CMTT_Categories_Widget extends WP_Widget {

	public static function init() {
		add_action( 'widgets_init', create_function( '', 'return register_widget("' . get_class() . '");' ) );
	}

	/**
	 * Sets up a new Categories widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'cmtt_widget_categories',
			'description'					 => __( 'A list or dropdown of CM Tooltip Glossary Categories.' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'cmtt_categories', __( 'Tooltip Categories' ), $widget_ops );
	}

	/**
	 * Outputs the content for the current Categories widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Categories widget instance.
	 */
	public function widget( $args, $instance ) {
		static $first_dropdown = true;

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Tooltip Categories' ) : $instance['title'], $instance, $this->id_base );

		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
		$d = ! empty( $instance['dropdown'] ) ? '1' : '0';

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$cat_args = array(
			'orderby'      => 'name',
			'taxonomy'      => 'glossary-categories',
			'show_count'   => $c,
			'hierarchical' => $h
		);

		if ( $d ) {
			$dropdown_id = ( $first_dropdown ) ? 'cat' : "{$this->id_base}-dropdown-{$this->number}";
			$first_dropdown = false;

			echo '<label class="screen-reader-text" for="' . esc_attr( $dropdown_id ) . '">' . $title . '</label>';

			$cat_args['show_option_none'] = __( 'Select Category' );
			$cat_args['id'] = $dropdown_id;

			/**
			 * Filters the arguments for the Categories widget drop-down.
			 *
			 * @since 2.8.0
			 *
			 * @see wp_dropdown_categories()
			 *
			 * @param array $cat_args An array of Categories widget drop-down arguments.
			 */
			wp_dropdown_categories( apply_filters( 'cmtt_widget_categories_dropdown_args', $cat_args ) );
			?>

<script type='text/javascript'>
/* <![CDATA[ */
(function() {
	var dropdown = document.getElementById( "<?php echo esc_js( $dropdown_id ); ?>" );
	function onCatChange() {
		if ( dropdown.options[ dropdown.selectedIndex ].value > 0 ) {
			location.href = "<?php echo home_url(); ?>/?cat=" + dropdown.options[ dropdown.selectedIndex ].value;
		}
	}
	dropdown.onchange = onCatChange;
})();
/* ]]> */
</script>

<?php
		} else {
?>
		<ul>
<?php
		$cat_args['title_li'] = '';

		/**
		 * Filters the arguments for the Categories widget.
		 *
		 * @since 2.8.0
		 *
		 * @param array $cat_args An array of Categories widget options.
		 */
				wp_list_categories( apply_filters( 'cmtt_widget_categories_args', $cat_args ) );
?>
		</ul>
<?php
		}

		echo $args['after_widget'];
	}

	/**
	 * Handles updating settings for the current Categories widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;

		return $instance;
	}

	/**
	 * Outputs the settings form for the Categories widget.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = sanitize_text_field( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy' ); ?></label></p>
		<?php
	}

}
