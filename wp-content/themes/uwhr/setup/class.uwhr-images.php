<?php
/**
 * Images
 *
 * Installs the custom image sizes
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Images {

    // If `$show` is true it will appear in the image dropdown menu
    public $IMAGE_SIZES = array(

        // Used in widgets for featured images
        'thimble' => array(
            'name'    => 'Thimble',
            'width'   => 50,
            'height'  => 50,
            'crop'    => true,
            'show'    => false
        ),

        // Used in galleries
        'thumbnail-large' => array(
            'name'  => 'Thumbnail large',
            'width' => 300,
            'height'=> 300,
            'crop'  => true,
            'show'  => false
        ),

        // Pano Large Images
        'pano-large' => array(
            'name'  => 'Pano large',
            'width' => 1600,
            'height'=> 450,
            'crop'  => true,
            'show'  => false
        ),

        // Pano Small Images
        'pano-small' => array(
            'name'  => 'Pano small',
            'width' => 1600,
            'height'=> 300,
            'crop'  => true,
            'show'  => false
        ),

        // RSS Feed
        'rss' => array(
            'name' => 'RSS',
            'width' => 108,
            'height' => 81,
            'crop' => true,
            'show' => false,
        )

    );

    function __construct() {
        add_action( 'after_setup_theme', array( $this, 'add_image_sizes' ) );
        add_filter( 'image_size_names_choose', array( $this, 'show_image_sizes') );
        add_filter( 'img_caption_shortcode', array( $this, 'img_caption_shortcode_filter' ), 10, 3 );
    }

    /**
     * Add Image Sizes
     *
     * Creates several new images sizes for many purposes
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    function add_image_sizes() {
        foreach ( $this->IMAGE_SIZES as $name=>$image ) {
            add_image_size(
                $name,
                $image['width'],
                $image['height'],
                $image['crop']
            );
        }
    }

    /**
     * Show Image Sizes
     *
     * Filter and add image sizes available to users in Media Uploader
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.4.0
     * @package UWHR
     *
     * @param $defaultSizes array 
     *
     * @return $imagesToShow array The selectable image sizes
     */
    function show_image_sizes( $defaultSizes ) {
        $imagesToShow = array_filter( $this->IMAGE_SIZES, function($image) {
            return $image['show'];
        });

        foreach ($imagesToShow as $id=>$image) {
            $imagesToShow[$id] = $image['name'];
        }

        unset($defaultSizes['full']);

        return (array_merge( $imagesToShow , $defaultSizes ));
    }

    /**
     * Improves the caption shortcode with HTML5 figure & figcaption; microdata & wai-aria attributes
     *
     * @param  string $val     Empty
     * @param  array  $attr    Shortcode attributes
     * @param  string $content Shortcode content
     * @return string          Shortcode output
     */
    function img_caption_shortcode_filter($val, $attr, $content = null) {
        extract(shortcode_atts(array(
            'id'      => '',
            'class'   => '',
            'align'   => 'aligncenter',
            'width'   => '',
            'caption' => ''
        ), $attr));

        if ( $id ) {
            $id = esc_attr( $id );
        }

        $figure = '';

        if ( $width >= get_option( 'large_size_w' ) ) {
            $class = 'size-large';
        } else if ( $width < get_option( 'large_size_w' ) AND $width > get_option( 'thumbnail_size_w' ) ) {
            $class = 'size-medium';
        } else {
            $class = 'size-thumbnail';
        }

        $figure = '<figure class="image ' . esc_attr($align) . ' ' . $class . '" id="' . $id . '" aria-describedby="figcaption_' . $id . '">' . do_shortcode( $content ) . '<figcaption id="figcaption_'. $id . '" class="image-caption" itemprop="description">' . $caption . '</figcaption></figure>';

        return $figure;
    }
}
