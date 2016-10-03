<?php

/**
 * Search
 *
 * Search handles all business logic of search in our multisite environment. Search is
 * also the container object for the UI and Settings components of search. You can access
 * those functions through the global $UWHR and the child $Search object.
 *
 * @author Mixed <uwhrweb@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.1.0
 * @package UWHR
 */

class UWHR_Search {

    // Relevance multipliers are used to weight search results; Set in Search Options
    private $rel_multiplier_query_title;
    private $rel_multiplier_word_title;
    private $rel_multiplier_query_lead_content;
    private $rel_multiplier_word_lead_content;
    private $rel_multiplier_query_content;
    private $rel_multiplier_word_content;
    private $rel_multiplier_query_keyword;
    private $rel_multiplier_query_peep;

    // Maximum number of search results to display on the page
    public $cap_search_results;

    // Some public members that will help in a search lifecycle
    public $default_search;
    public $sort_options;
    public $filter_options;

    /**
     * Constructor
     *
     * Calls a function to setup some private and public data members.
     * And initalizes additional Objects that provide search support.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     */
    function __construct() {
        add_action( 'init', array( $this, 'create_search_bucket_taxonomy' ) );
        add_action( 'init', array( $this, '_setup' ), 11 );

        $this->UI = new UWHR_Search_UI( $this );
        $this->Options = new UWHR_Search_Options();
    }

    /**
     * Register Search Buckets Taxonomy
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.5.0
     * @package UWHR
     */
    function create_search_bucket_taxonomy() {
        global $UWHR;

        $labels = array(
            'name'              => 'Search Buckets',
            'singular_name'     => 'Search Bucket'
        );

        if ( current_user_can( $UWHR->get_edit_structure_cap() ) ) {

            $args = array(
                'hierarchical'      => true,
                'labels'            => $labels,
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => false,
                'rewrite'           => false,
            );

        } else {

            $args = array(
                'hierarchical'      => true,
                'labels'            => $labels,
                'public'            => true,
                'show_ui'           => false,
                'rewrite'           => false,
            );
        }

        $post_types = get_post_types( array( 'exclude_from_search' => false ) );
        unset($post_types['attachment']);

        register_taxonomy(
            'search_bucket',
            $post_types,
            $args
        );
    }

    /**
     * Setup
     *
     * Put together some of the private static members that'll be used throughout
     * a search lifecycle
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    public function _setup() {

        if ( ! is_admin() ) {
            $this->rel_multiplier_query_title = get_option( 'search_settings_weights' )['title_rel_weight'];
            $this->rel_multiplier_word_title = get_option( 'search_settings_weights' )['title_word_rel_weight'];
            $this->rel_multiplier_query_lead_content = get_option( 'search_settings_weights' )['lead_content_rel_weight'];
            $this->rel_multiplier_word_lead_content = get_option( 'search_settings_weights' )['lead_content_word_rel_weight'];
            $this->rel_multiplier_query_content = get_option( 'search_settings_weights' )['content_rel_weight'];
            $this->rel_multiplier_word_content = get_option( 'search_settings_weights' )['content_word_rel_weight'];
            $this->rel_multiplier_query_keyword = get_option( 'search_settings_weights' )['keyword_rel_weight'];
            $this->rel_multiplier_query_peep = get_option( 'search_settings_weights' )['staff_rel_weight'];

            $this->cap_search_results = get_option( 'search_settings_sites' )['max'];

            $this->sort_options = array(
                array(
                    'clean' => 'Alphabetical A-Z',
                    'slug'  => 'alpha'
                ),
                array(
                    'clean' => 'Recently edited',
                    'slug'  => 'date'
                ),
                array(
                    'clean' => 'Relevance',
                    'slug'  => 'rel'
                ),
            );

            $this->filter_options = array(
                array(
                    'title'     => 'Filters',
                    'slug'      => 'filter',
                    'options'   => array(
                        array(
                            'title' => 'Page',
                            'slug'  => 'page'
                        ),
                        array(
                            'title' => 'People',
                            'slug'  => 'people'
                        ),
                        array(
                            'title' => 'Contract',
                            'slug'  => 'contract'
                        ),
                        array(
                            'title' => 'Policy',
                            'slug'  => 'policy'
                        ),
                        array(
                            'title' => 'News',
                            'slug'  => 'news'
                        ),
                        array(
                            'title' => 'Form',
                            'slug'  => 'form'
                        )
                    )
                ),
            );

            $siteIDs = get_option( 'search_settings_sites' )['sites'];
            // Searching Post Types
            $postTypes = array( 'post', 'page', 'consultant' );
            foreach( $this->filter_options as $filter ) {
                foreach( $filter['options'] as $fo ) {
                    ${$filter['slug'].'s'}[] = $fo['slug'];
                }
            }

            $this->default_search = array(
                'site'      => $siteIDs,
                'post_type' => $postTypes,
                'filter'    => $filters,
                'sort'      => 'rel',
            );
        }
    }

    /**
     * Post to Get Method Converter
     *
     * This little trick function runs before any of the search page loads. It converts
     * the $_POST variables into $_GET variables and writes a nicer looking URL.
     *
     * @author Thom Thorpe <twthorpe@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @see search.php
     */
    public function convert_post_to_get_variables() {
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $url = $this->_build_url_from_variables();
            wp_redirect( $url );
            exit;
        }
    }

    /**
     * Build URL From Post Backs
     *
     * This function grabs a bunch of the POST variables and writes them to a new URL.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @return $url string The URL that the user will be redirected to
     */
    private function _build_url_from_variables() {
        $url = $filter = $sort = '';

        // Get the POST variables if they exist
        $postFilter = isset($_POST['filter']) ? array_unique($_POST['filter']) : false;
        $postSort = isset($_POST['sort']) ? $_POST['sort'] : false;

        // Check to see if the POST vars are different from the defaults
        $filter  = ( $postFilter == $this->default_search['filter'] )     ? false   : $postFilter;
        $sort    = ( $postSort == $this->default_search['sort'] )         ? false   : $postSort;

        // If the selected values are the same, we aren't going to append it to the query string
        $s          = urlencode( get_search_query() );
        $filterURL  = $filter   ? '&filter=' . implode( ',', $filter)   : null;
        $sortURL    = $sort     ? '&sort=' . $sort                      : null;

        $url = get_home_url( 1, '/' ) . '?s=' . $s . $filterURL . $sortURL;
        return $url;
    }

    /**
     * Search Parameters
     *
     * Checks the global $_GET variables and parses or returns default values
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @return $search_params array Array of search parameters or defaults
     */
    public function get_search_params( $param = '' ) {

        // Get selectsion from previous search or defaults for the form
        $query  = get_search_query();
        $filter = isset( $_GET['filter'] )  ? explode( ',', $_GET['filter'] )   : $this->default_search['filter'];
        $sort   = isset( $_GET['sort'] )    ? $_GET['sort']                     : $this->default_search['sort'];

        // Stick it all into an array for passing around
        $search_params = array(
            's' => $query,
            'filter' => $filter,
            'sort' => $sort,
        );

        if ( ! empty($param) ) {
            if ( array_key_exists ( $param, $search_params ) ) {
                return $search_params[$param];
            } else {
                return false;
            }
        } else {
            return $search_params;
        }
    }

    /**
     * Search Results
     *
     * Setups all the things needed to eventually perform the search. It then calls
     * the _perform_search function and returns those results for display.
     *
     * Functional programming for the win!!
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $query_string
     *
     * @param $searchParams array $_POST variables or defaults
     *
     * @return $results array Array of search result info
     */
    public function get_search_results( $searchParams ) {

        // Get all variables we need
        $searchingQuery     = $searchParams['s'];
        $searchingFilter    = $searchParams['filter'];
        $searchingSort      = $searchParams['sort'];
        $searchingPostTypes = apply_filters( 'uwhr_search_post_types', $this->default_search['post_type'] );

        // Initialize the results that'll be returned
        $results = array(
            'results'   => array(),
            'total'     => 0,
            'error'     => '',
            'warning'   => '',
        );

        // Ooops, searched for an empty string
        if ( '' == $searchingQuery ) {
            $results['error'] = 'empty';
            return $results;
        }

        // Setting up search_query as defined in WordPress docs
        global $query_string;
        $query_args = explode("&", $query_string);
        $search_query = array();

        foreach($query_args as $key => $string) {
            $query_split = explode("=", $string);
            $search_query[$query_split[0]] = urldecode($query_split[1]);
        }

        // Are we searching for people?
        $searchingPeople = in_array( 'people', $searchingFilter ) ? true : false;

        if ( $searchingPeople ) {
            // Remove people from postTypes
            if( ( $key = array_search( 'people', $searchingFilter ) ) !== false ) {
                unset( $searchingFilter[$key] );
            }
        }

        // Build main search query
        $search_query['posts_per_page'] = $this->cap_search_results;
        $search_query['post_status'] = 'publish';
        $search_query['post_type'] = $searchingPostTypes;
        $search_query['tax_query'] = array(
            array(
                'taxonomy' => 'search_bucket',
                'field'    => 'slug',
                'terms'    => $searchingFilter,
            ),
        );

        // Get all the search results
        $searchResults = $this->_perform_search( $this->default_search['site'], $search_query );

        // Perfrom people search and merge the results together if we're searching for peoeple
        if ( $searchingPeople ) {
            $peepsResult = $this->_get_people( $searchingQuery );
            $searchResults->posts = array_merge( $searchResults->posts, $peepsResult->posts );
            $searchResults->found_posts = $searchResults->found_posts + $peepsResult->found_posts;
            $searchResults->post_count = $searchResults->post_count + $peepsResult->post_count;
        }

        // Are we over our search result limit?
        $tooDamnManyWarning = $this->cap_check( $searchResults->found_posts );

        // Get the total number of search results
        if ( $tooDamnManyWarning ) {
            $results['warning'] = 'cap';
            $searchResultsTotal = $this->cap_search_results;
        } else {
            $searchResultsTotal = $searchResults->found_posts;
        }

        // Pack it in
        $results['total'] = $searchResultsTotal;

        // No results
        if ( $searchResultsTotal === 0 ) {
            $results['error'] = 'no_results';
            return $results;
        }

        // Calculate relevance weight of each post in search result, only needed if sorting by relevance
        if ( $searchingSort === 'rel' ) {
            foreach( $searchResults->posts as $key => $post ) {
                $searchResults->posts[$key] = $this->_calculate_weight( $post );
            }
        }

        // Sort the posts by selected sorting and order
        $searchResults->posts = $this->_sort_query_posts_by_( $searchingSort, $searchResults->posts );

        // Pack it the sorted results into our array
        $results['results'] = $searchResults->posts;

        // Send it all off
        return $results;
    }

    /**
     * Perform Search
     *
     * This is probably the meat and potatos of the calling function's gravy.
     * Here we actually loop through each site in the $siteIDs array and performing our
     * passed in $search_query. We have to do it twice - once to check for keymatches and
     * a second to check for regular search results. All these results are put into a
     * master object which gets returned.
     *
     * There's a few tricky things that happen between here and there. Noted below.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $siteIDs array The list of searchable sites by site ID
     * @param $search_query array The WP seach query params
     *
     * @return $queryResults Object An object of all our results; includes and post_count and found_posts
     *                       just like the WP Query object; basically, its kinda like a WP Query object
     */
    private function _perform_search( $siteIDs = array(), $search_query ) {

        // Setup a queryResults object and initialize with some zero values
        $queryResults = (object) array(
            'posts' => array(),
            'post_count' => 0,
            'found_posts' => 0
        );

        // Loop over each site and build the $queryResults
        foreach ( $siteIDs as $siteID ) {

            // All the results that are found in $siteID
            $sitePosts = array();

            // All the IDs of results that are found in $siteID; used to prevent dupes later
            $keywordedResultsIds = array();

            // Switch to blog to be able to actually query pages
            switch_to_blog( $siteID );

            // SEARCH NUMBER ONE
            // Before we do the actual search, let's see if there are any things
            // keyworded with the search query
            $keyword_search_query['posts_per_page'] = $search_query['posts_per_page'];
            $keyword_search_query['post_status'] = $search_query['post_status'];
            $keyword_search_query['post_type'] = $search_query['post_type'];
            $keyword_search_query['tax_query'] = array(
                array(
                    'taxonomy' => 'keyword',
                    'field'    => 'slug',
                    'terms'    => strtolower( get_search_query() ),
                ),
            );

            // Do search on site $siteID
            $keywordSearch = new WP_Query( $keyword_search_query );

            // Built out $queryResults with the results of each $keywordSearch
            if ( $keywordSearch->have_posts() ) {

                // Merge post arrays, pack 'em all in there
                $sitePosts = array_merge( $sitePosts, $keywordSearch->posts );
                foreach ( $keywordSearch->posts as $post ) {
                    $keywordedResultsIds[] = $post->ID;
                }

                // Increment total posts found
                $queryResults->found_posts = $queryResults->found_posts + $keywordSearch->found_posts;
                $queryResults->post_count = $queryResults->post_count + $keywordSearch->post_count;
            }

            wp_reset_query();

            // SEARCH NUMBER TWO
            // We still want to cap results, after both searches take place
            $search_query['posts_per_page'] = $this->cap_search_results - $queryResults->found_posts;

            // Prevent the dupes
            if ( ! empty($keywordedResultsIds) ) {
                $search_query['post__not_in'] = $keywordedResultsIds;
            }

            // Do search on site $siteID
            $search = new WP_Query( $search_query );

            // Built out $queryResults with the results of each $search
            if ( $search->have_posts() ) {

                // Merge post arrays, pack 'em all in there
                $sitePosts = array_merge( $sitePosts, $search->posts );

                // Increment total posts found
                $queryResults->found_posts = $queryResults->found_posts + $search->found_posts;
                $queryResults->post_count = $queryResults->post_count + $search->post_count;
            }

            foreach( $sitePosts as $post ) {
                // Stick in there the siteID we found that post; we'll need it later
                $post->blog_id = $siteID;

                // Check if sorting by relevance and grab the keyword terms if any exist;
                // we use this to calculate weight later
                if ( $this->get_search_params('sort') === 'rel' ) {
                    $term_objects = wp_get_object_terms( $post->ID, 'keyword' );
                    if ( !empty( $term_objects ) AND !is_wp_error( $term_objects ) ) {
                        $keywords = array();
                        foreach( $term_objects as $term_object ) {
                            $keywords[] = strtolower($term_object->name);
                        }
                        $post->keywords = $keywords;
                    }
                }
            }

            $queryResults->posts = array_merge( $queryResults->posts, $sitePosts );

            // Restore back to current blog - always comes with switch_to_blog()
            ##restore_current_blog();

        } // end site loop

        // Phew!
        return $queryResults;
    }

    /**
     * Get People Results
     *
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @global $wpdb The WordPress database
     */
    private function _get_people( $s ) {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $p = $wpdb->prefix;

        $query = '
                SELECT ' . $p . 'contacts_individuals.nameFirst, ' . $p . 'contacts_individuals.nameLast, ' .
                $p . 'contacts_individuals.jobTitle, ' . $p . 'contacts_individuals.phone, ' .
                $p . 'contacts_individuals.email, ' . $p . 'contacts_individuals.isManager, ' .
                $p . 'contacts_individuals.unit, ' . $p . 'contacts_units.name AS unit
                FROM ' . $p . 'contacts_individuals
                JOIN ' . $p . 'contacts_units
                ON ' . $p . 'contacts_individuals.unit=' . $p . 'contacts_units.id
                WHERE ( nameFirst LIKE "%'.$s.'%" OR nameLast LIKE "%'.$s.'%" OR jobTitle LIKE "%'.$s.'%" ) AND onWeb = 1 AND isDuplicate != 1
        ';

        $peeps = $wpdb->get_results( $query );

        $array = array();
        foreach ( $peeps as $peep ) {
            $array[] = (object) array(
                'full_name'     => $peep->nameLast . ', ' . $peep->nameFirst,
                'unit'          => $peep->unit,
                'jobtitle'      => $peep->jobTitle,
                'phone'         => $peep->phone,
                'email'         => $peep->email,
                'is_peep'       => true
            );
        }

        $results = (object) array(
            'post_count'    => count($peeps),
            'found_posts'   => count($peeps),
            'posts'         => $array
        );

        return $results;
    }

    /**
     * Calculate post weight
     *
     * Calculates the post's weight in search results by a set of characteristics and considerations
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param $result Object A search result object, most often a WP_Post object but maybe peep or other
     *
     * @return $result Object Same as above with added weight
     */
    private function _calculate_weight( $result ) {

        // Set starting weight to 0
        $weight = 0;

        if ( ! isset( $result->is_peep ) ) {

            // Gather some things
            $query = strtolower( get_search_query() );
            $post_title = strtolower( $result->post_title );
            $post_content = strtolower( $result->post_content );
            $post_content_striped = wp_strip_all_tags( $post_content );
            $post_content_striped_length = strlen( $post_content_striped );

            // Query is in the title
            $weight += substr_count( $post_title, $query ) * $this->rel_multiplier_query_title;

            if ($post_content) {

                // Query is in the intro content
                if ( $post_content_striped_length > 200 ) {
                    $weight += substr_count( $post_content_striped, $query, 0, 100 ) * $this->rel_multiplier_query_lead_content;
                }

                // Query is in the rest of the content
                if ( $post_content_striped_length > 200 ) {
                    $weight += substr_count( $post_content_striped, $query, 100 ) * $this->rel_multiplier_query_content;
                } else {
                    $weight += substr_count( $post_content_striped, $query ) * $this->rel_multiplier_query_lead_content;
                }
            }

            if ( is_array($result->keywords) ) {
                if (in_array( $query, $result->keywords)) {
                    $weight += $this->rel_multiplier_query_keyword;
                }
            }

            $words = explode(' ', $query);
            $words = ( count( $words ) > 1 ) ? $words : false;

            // Strip out the s if query ends in 's'
            if (! $words) {
                if (substr($query, -1) == 's') {
                    $query_no_s = substr($query, 0, -1);
                    $words = array($query, $query_no_s);
                }
            }

            // Do the same as above but for each word in query string
            if ( ! empty($words) ) {

                // Make sure there's stuff in there
                $words = array_filter($words);

                // Filter out the undesirable words
                $words = $this->blacklist_stopwords($words);

                foreach( $words as $word ) {
                    $weight += substr_count( $post_title, $word ) * $this->rel_multiplier_word_title;

                    if ($post_content) {

                        if ( $post_content_striped_length > 200 ) {
                            $weight += substr_count( $post_content_striped, $word, 0, 100 ) * $this->rel_multiplier_word_lead_content;
                        }

                        // Query is in the rest of the content
                        if ( $post_content_striped_length > 200 ) {
                            $weight += substr_count( $post_content_striped, $word, 100 ) * $this->rel_multiplier_word_content;
                        } else {
                            $weight += substr_count( $post_content_striped, $word ) * $this->rel_multiplier_word_lead_content;
                        }
                    }
                }
            }

        } else {
            // This results is a person and should be weighted
            $weight += $this->rel_multiplier_query_peep;
        }

        // Set weight property to post object
        $result->weight = $weight;

        // Return the same @param post with calculated weight property
        return $result;
    }

    /**
     * Sort Queried Posts
     *
     * Sort the WP_Query object post array according to $param
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $param string The param to sort the posts by
     * @param $posts array( WP_Post Objects )
     *
     * @return $posts array( WP_Post Objects )
     */
    private function _sort_query_posts_by_( $param, $posts ) {

        switch ( $param ) {
            case 'rel':

                function sort_weight($a, $b) {
                    $aw = $a->weight;
                    $bw = $b->weight;
                    if ( $aw == $bw ) {
                        return 0;
                    }
                    return ( $aw > $bw ) ? -1 : 1;
                }

                usort($posts, "sort_weight");

                break;

            case 'date':

                function sort_date($a, $b) {
                    $aDate = isset($a->post_date ) ? $a->post_date : '';
                    $bDate = isset($b->post_date ) ? $b->post_date : '';
                    $ad = strtotime($aDate);
                    $bd = strtotime($bDate);
                    if ($ad == $bd) {
                        return 0;
                    }
                    return $ad > $bd ? -1 : 1;
                }

                usort($posts, "sort_date");

                break;

            case 'alpha':

                function sort_alpha($a, $b) {
                    $at = $a->is_peep ? $a->full_name : $a->post_title;
                    $bt = $b->is_peep ? $b->full_name : $b->post_title;
                    if ( $at == $bt ) {
                        return 0;
                    }
                    return ( $at < $bt ) ? -1 : 1;
                }

                usort($posts, "sort_alpha");

                break;

            default:
                break;
        }

        // Return sorted $posts
        return $posts;
    }

    /**
     * Cap Checker
     *
     * Checks to see if we have more results than our cap.
     *
     * Let's not overload the browser with tooooons of results. Sort of a
     * future proofing function.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $total int The number of results
     *
     * @return boolean Are we over our cap?
     */
    public function cap_check( $total ) {
        if ( $total > $this->cap_search_results ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Blacklist Stopwords
     *
     * Take in an array of words and then spit back out the words that aren't blacklisted
     * Used in our weighting system. We don't need to weight these words.
     *
     * @see _calculate_weights()
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.8.0
     * @package UWHR
     *
     * @param $words array An array of words
     *
     * @return $filtered array A filtered array of words
     */
    public function blacklist_stopwords( $words ) {
        $stopwords = explode( ',', _x( 'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www', 'Comma-separated list of search stopwords in your language' ) );
        $words = array_diff($words, $stopwords);
        return $words;
    }
}
