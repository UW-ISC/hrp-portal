<?php

/**
 * Add Root stylesheet to $UWHR object
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 1.0.0
 * @package UWHR-root
 */
function add_stylesheet( $UWHR ) {
    $ver = wp_get_theme()->version;
	$root = array(
        'id'      => 'root',
        'url'     => get_stylesheet_directory_uri() . '/style.css',
        'deps'    => array( $UWHR->Styles->styles['main']['id'] ),
        'version' => $ver
    );

    $UWHR->Styles->styles['root'] = $root;
}
add_action( 'extend_uwhr_object', 'add_stylesheet' );

/**
 * Install some custom objects
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2016, University of Washington
 * @since 1.0.0
 * @package UWHR-root
 */
function custom_objects( $UWHR ) {
    require_once(get_stylesheet_directory() . '/setup/class.uwhr-post-type-slide.php');
    require_once(get_stylesheet_directory() . '/setup/class.uwhr-post-type-card.php');

	$UWHR->Slide = new UWHR_Slide;
    $UWHR->Card = new UWHR_Card;
}

add_action( 'extend_uwhr_object', 'custom_objects' );

/**
 * Display a custom slideshow on the homepage
 *
 * Grab a set of slides from the database and display them in the hero section of the homepage.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 1.0
 * @package UWHR-root
 */
if ( ! function_exists( 'uwhr_homepage_slideshow' ) ) :
    function uwhr_homepage_slideshow() {
        $args = array(
            'post_type'         => 'slide',
            'posts_per_page'    => -1
        );

        // The Query
        $slide_query = new WP_Query( $args );

        // Initialize some things
        $html = '';
        $i = $totalCount = 0;
        $secondSlideTitle = $secondSlideID = $activeClass = '';

        // The Loop
        if ( $slide_query->have_posts() ) :
            $totalCount = $slide_query->found_posts;
            while ( $slide_query->have_posts() ) :
                $slide_query->the_post();

                $i++;
                $post_id = get_the_ID();

                $text = get_post_meta(      $post_id, '_uwhr_slide_text', true );
                $button = get_post_meta(    $post_id, '_uwhr_slide_button', true );
                $link = get_post_meta(      $post_id, '_uwhr_slide_link', true );
                $color = get_post_meta(     $post_id, '_uwhr_slide_color', true );
                $v = get_post_meta(         $post_id, '_uwhr_slide_image_v_position', true );
                $h = get_post_meta(         $post_id, '_uwhr_slide_image_h_position', true );
                $url = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'pano-large' );

                if ( $i == 2 ) {
                    $secondSlideTitle = get_the_title();
                    $secondSlideID = $post_id;
                }

                $slideClass = 'uwhr-slide uwhr-hero-image hero-image-height-lg';

                $slideClass .= ' slide-text-' . esc_attr($color);

                $activeClass = $i == 1 ? ' active' : '';

                $html .= '<div class="' . $slideClass . $activeClass . '" id="slide-' . $post_id . '" style="background-image: url(' . $url[0] . '); background-position: ' . esc_attr($h) . ' ' . esc_attr($v) . '">';
                    $html .= '<div class="container">';
                        $html .= '<div class="row">';
                            $html .= '<div class="col-md-12 col-lg-7">';
                                $html .= '<h3 class="slide-title">' . get_the_title() . '</h3>';
                                $html .= '<span class="slant gold hidden-xs-down"></span>';
                                $html .= '<p class="slide-graf">' . esc_html( $text ) . '</p>';
                                $html .= '<p><a href="' . esc_url( $link ) . '" class="uw-btn btn-gold">' . esc_html( $button ) . '</a></p>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';

            endwhile;
        endif;

        /* Restore original Post Data */
        wp_reset_postdata();

        // We have more than one slide! Let's build some controls
        // JavaScript is handled in uwhr.slideshow.js
        if ( $totalCount > 1 ) {
            $html .= '<div class="uwhr-slideshow-controls no-js-hide">';
                $html .= '<button class="next-slide-button" id="slide-' . $secondSlideID . '">';
                    $html .= '<span>NEXT</span>';
                    $html .= $secondSlideTitle;
                $html .= '</button>';
            $html .= '</div>';
        }

        echo $html;
    }
endif;

/**
 * Display custom post type cards
 *
 * Grab a set of cards from the database and display them where appropriate.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 1.0
 * @package UWHR-root
 *
 * @global  $post int The global post object
 */
if ( ! function_exists( 'uwhr_homepage_cards' ) ) :
    function uwhr_homepage_cards() {
        $num_per_row = 2;
        $column_width = 12 / $num_per_row;
        $i = 0;
        $total_num_cards;

        $args = array(
            'post_type' => 'card',
        );

        // The Query
        $card_query = new WP_Query( $args );

        // The Loop
        if ( $card_query->have_posts() ) :
            $total_num_cards = $card_query->found_posts;
            while ( $card_query->have_posts() ) :
                $card_query->the_post();

                global $post;

                $post_id = $post->ID;

                $html = '';

                $subtitle = get_post_meta(  $post_id, '_uwhr_card_subtitle', true );
                $text = get_post_meta(      $post_id, '_uwhr_card_text', true );
                $button = get_post_meta(    $post_id, '_uwhr_card_button', true );
                $link = get_post_meta(      $post_id, '_uwhr_card_link', true );

                $card_classes = 'card card-light card-hide-text card-fixed-height-md';

                // Print opening .row tag when multiple of num_per_row
                if( ( $i % $num_per_row ) == 0 ) {
                    $html .= '<div class="row">';
                }

                $i++;

                $html .= '<div class="col-md-' . $column_width . '">';

                    $html .= '<article class="' . $card_classes . '" id="post-' . $post_id . '">';

                        $html .= get_the_post_thumbnail( $post_id, 'medium', array( 'class' => 'card-img-top' ) );

                        $html .= '<div class="card-block">';
                            if ( ! empty($subtitle) ) {
                                $html .= '<p class="card-title card-subtitle h6 gold text-uppercase">' . esc_html( $subtitle ) . '</p>';
                            }

                            $html .= '<h3 class="card-title beefy purple"><a href="' . esc_url( $link ) . '" title="Link to ' . esc_html( $button ) . ', ' . get_the_title() . '">' . get_the_title() . '</a></h3>';

                            if ( ! empty($text) ) {
                                $html .= '<p class="card-text">' . esc_html( $text ) . '</p>';
                            }
                        $html .= '</div>';

                        $html .= '<div class="card-block card-block-last">';
                            $html .= '<a class="uw-btn btn-white btn-sm m-a-0 card-block-item-last" href="' . esc_url( $link ) . '" title="Link to ' . esc_html( $button ) . ', ' . get_the_title() . '">' . esc_html( $button ) . '</a>';
                        $html .= '</div>';

                    $html .= '</article>';

                $html .= '</div>'; // Close .col-md-* tag

                if( ( $i % $num_per_row ) == 0 OR $i == $total_num_cards ) {
                    $html .= '</div>'; // Close .row tag
                }

                echo $html;

            endwhile;
        endif;

        wp_reset_postdata();
    }
endif;

/**
 * Display Calendar feed
 *
 * Pulls a feed of calendar events from the UWHR trumba calendar stored in DB
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 1.0
 * @package UWHR-root
 *
 * @param $display int Number of events to display
 */
if ( ! function_exists( 'uwhr_display_calendar_feed' ) ):
    function uwhr_display_calendar_feed( $display = 5 ) {

        $html = $currentMonth = '';

        $html .= '<div class="row m-b-2">';

            $html .= '<div class="col-xs-12">';
                $html .= '<h3 class="h4 gold thin"><i class="fa fa-calendar m-r-xs"></i> Upcoming Events</h3>';
            $html .= '</div>';

            $data = grab_transient_or_api_request( 'calendar_feed_xml', 2 * HOUR_IN_SECONDS, true );

            if ( $data->is_api_called ) {
                uwhr_flush_page_cache_by_url('/');
            }

            $events = $data->data;
            $eventsCount = 0;

            foreach ($events as $event) {
                if( $eventsCount == $display ) {
                    break;
                }

                // Grab the things
                $title = $event['title'];
                $link = $event['link'];
                $where = $event['where'];
                $month = $event['start']->format('F');
                $day = $event['start']->format('j');
                $time = $event['start']->format('g:i') . ' - ' . $event['end']->format('g:i A');

                $html .= '<div class="col-xs-12">';
                    $html .= '<div class="row">';

                        // Show month if new month
                        if ( $currentMonth != $month ) {
                            $html .= '<div class="col-xs-12"><p class="thin gold h4">' . $month . '</p></div>';
                            $currentMonth = $month;
                        }

                        $html .= '<div class="col-xs-2 col-lg-3 col-xl-2">';
                            $html .= '<p class="thin h3 text-right gold">' . $day . '</p>';
                        $html .= '</div>';

                        $html .= '<div class="col-xs-10 col-lg-9 col-xl-10">';
                            $html .= '<h4 class="purple m-a-0"><a href="' . $link . '">' . $title . '</a></h4>';
                            $html .= '<p class="heading m-a-0">' . $where . '</p>';
                            $html .= '<p class="heading">' . $time . '</p>';
                        $html .= '</div>';

                    $html .= '</div>';
                $html .= '</div>';

                // Increment counter
                $eventsCount++;
            }

            $cal_pages = get_pages(array(
                'meta_key' => '_wp_page_template',
                'meta_value' => 'templates/template-calendar.php'
            ));

            if ( ! empty( $cal_pages ) ) {

                $html .= '<div class="col-xs-12">';
                    $html .= '<p class="heading gold text-right text-uppercase"><a href="' . get_permalink( $cal_pages[0]->ID ) . '">View All <i class="fa fa-chevron-right"></i></a></p>';
                $html .= '</div>';

            }

        $html .= '</div>';

        echo $html;
    }
endif;

/**
 * Display Twitter feed
 *
 * Display a feed of tweets from UWHires account
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 1.0
 * @package UWHR-root
 *
 * @param $display int Number of events to display
 */
if ( ! function_exists( 'uwhr_display_twitter_feed' ) ):
    function uwhr_display_twitter_feed( $display = 1 ) {

        $html = '';

        $html .= '<div class="row m-b-2">';

            $html .= '<div class="col-xs-12">';
                $html .= '<p class="h4 gold thin"><i class="fa fa-twitter"></i> @UWHires</p>';
            $html .= '</div>';

            $html .= '<div class="col-xs-12">';

                $data = grab_transient_or_api_request( 'twitter_feed', 2 * HOUR_IN_SECONDS, true );

                if ( $data->is_api_called ) {
                    uwhr_flush_page_cache_by_url('/');
                }

                $tweets = $data->data;
                $tweetCount = 0;

                foreach ($tweets as $tweet) {
                    if( $tweetCount == $display ) {
                        break;
                    }

                    $html .= '<p class="gold">' . $tweet . '</p>';

                    $tweetCount++;
                }

            $html .= '</div>';

            $html .= '<div class="col-xs-12">';
                $html .= '<p><a href="https://twitter.com/UWHires">Follow UWHires on Twitter</a></p>';
            $html .= '</div>';

        $html .= '</div>';

        echo $html;
    }
endif;

/**
 * Get Calendar Feed XML
 *
 * Build an array of XML data for a calendar feed
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 1.0
 * @package UWHR-root
 *
 * @return $xml_data array An array of calendar events
 */
if ( ! function_exists( 'calendar_feed_xml' ) ):
    function calendar_feed_xml() {

        // Remote API URL
        $url_trumba = 'http://www.trumba.com/calendars/sea_hr.xml?weeks=2&html=0&xcal=1';

        // Call the API
        $xml = simplexml_load_file($url_trumba);

        // Multidimensional array
        $xml_data = array();

        foreach ($xml->entry as $event) {

            // Grab the two dates
            $startDate = new DateTime( $event->children('gd', true)->when->attributes()->startTime );
            $endDate = new DateTime( $event->children('gd', true)->when->attributes()->endTime );

            // Skip events that are over 2 days long
            if (  $startDate->diff($endDate)->d >= 2 ) {
                continue;
            }

            // Perform time change
            $pacific_time = new DateTimeZone('America/Los_Angeles');
            $startDate->setTimezone($pacific_time);
            $endDate->setTimezone($pacific_time);

            // Array value to save
            $item = array (
                'title' => (string) $event->title,
                'link' => (string) $event->link['href'],
                'where' => (string) $event->children('gd', true)->where->attributes()->valueString,
                'start' => $startDate,
                'end' => $endDate,
            );

            array_push($xml_data, $item);
        }

        // Send the data to our transient for saving to database
        return $xml_data;
    }
endif;

/**
 * Get Twitter Feed JSON
 *
 * Build an array of tweets from the UWHires Twitter handle using the Twitter REST API
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 1.0
 * @package UWHR-root
 *
 * Based off of @link http://stackoverflow.com/a/21290729
 *
 * @return $tweets array An array of tweet content
 */
function twitter_feed() {

    $token = '625048691-VjNaKTa6l3L6haQn0WeUljPTsR1Ux61p6mzsLZhA';
    $token_secret = '3kJHkRab0ky5tnIJyZFT3Ku2XnqH12YgCWGxc5i6jzFey';
    $consumer_key = 'vP6VUJZa8UfJtijtNxYWKXGQI';
    $consumer_secret = 'CijElFQ2u70grGcQ92JZtXA1np1TtkcAGTmdtxiHy145SSbr2U';

    $host = 'api.twitter.com';
    $method = 'GET';
    $path = '/1.1/statuses/user_timeline.json'; // api call path

    $query = array( // query parameters
        'screen_name' => 'UWHires',
        'count' => '5'
    );

    $oauth = array(
        'oauth_consumer_key' => $consumer_key,
        'oauth_token' => $token,
        'oauth_nonce' => (string)mt_rand(), // a stronger nonce is recommended
        'oauth_timestamp' => time(),
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_version' => '1.0'
    );

    $oauth = array_map("rawurlencode", $oauth); // must be encoded before sorting
    $query = array_map("rawurlencode", $query);

    $arr = array_merge($oauth, $query); // combine the values THEN sort

    asort($arr); // secondary sort (value)
    ksort($arr); // primary sort (key)

    // http_build_query automatically encodes, but our parameters
    // are already encoded, and must be by this point, so we undo
    // the encoding step
    $querystring = urldecode(http_build_query($arr, '', '&'));

    $url = "https://$host$path";

    // mash everything together for the text to hash
    $base_string = $method."&".rawurlencode($url)."&".rawurlencode($querystring);

    // same with the key
    $key = rawurlencode($consumer_secret)."&".rawurlencode($token_secret);

    // generate the hash
    $signature = rawurlencode(base64_encode(hash_hmac('sha1', $base_string, $key, true)));

    // this time we're using a normal GET query, and we're only encoding the query params
    // (without the oauth params)
    $url .= "?".http_build_query($query);
    $url=str_replace("&amp;","&",$url);

    $oauth['oauth_signature'] = $signature; // don't want to abandon all that work!
    ksort($oauth); // probably not necessary, but twitter's demo does it

    // also not necessary, but twitter's demo does this too
    function add_quotes($str) { return '"'.$str.'"'; }
    $oauth = array_map("add_quotes", $oauth);

    // this is the full value of the Authorization line
    $auth = "OAuth " . urldecode(http_build_query($oauth, '', ', '));

    // if you're doing post, you need to skip the GET building above
    // and instead supply query parameters to CURLOPT_POSTFIELDS
    $options = array( CURLOPT_HTTPHEADER => array("Authorization: $auth"),
                      CURLOPT_HEADER => false,
                      CURLOPT_URL => $url,
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_SSL_VERIFYPEER => false);

    // do our business
    $feed = curl_init();
    curl_setopt_array($feed, $options);
    $json = curl_exec($feed);
    curl_close($feed);

    $twitter_data = json_decode($json);

    $tweets = array();

    foreach ($twitter_data as &$value) {

        $tweet = preg_replace("/(http:\/\/|(www\.))(([^\s<]{4,68})[^\s<]*)/", '<a href="http://$2$3">$1$2$4</a>', $value->text);
        $tweet = preg_replace("/(https:\/\/|(www\.))(([^\s<]{4,68})[^\s<]*)/", '<a href="https://$2$3">$1$2$4</a>', $value->text);
        $tweet = preg_replace("/@(\w+)/", "<a class=\"branded\" href=\"https://www.twitter.com/\\1\">@\\1</a>", $tweet);
        $tweet = preg_replace("/#(\w+)/", "<a class=\"branded\" href=\"https://twitter.com/search?q=\\1\">#\\1</a>", $tweet);

        array_push($tweets, $tweet);
    }

    // Send the data to our transient for saving to database
    return $tweets;
}