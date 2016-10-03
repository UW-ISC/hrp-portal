<?php

/**
 * Search UI
 *
 * Search UI handles all aspects of the UI search components, from rendering
 * search forms to displaying results.
 *
 * @author Steven Speicher <stvns@uw.edu>
 * @copyright Copyright (c) 2015, University of Washington
 * @since 0.3.0
 * @package UWHR
 */

class UWHR_Search_UI {

	/**
     * Parent Reference
     *
     * A local private reference to the parent object that constructs $this.
     * Needed to get access to several members and methods of parent object.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
	private $parent;

    /**
     * Constructor
     *
     * Only used to initialize a few things
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $parent UWHR_Search Object A reference to the object that calls constructor
     */
    function __construct( $parent ) {
    	$this->parent = $parent;
    }

    /**
     * Master Search Form
     *
     * The search form that exists on every page of UWHR websites.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param $class string (Optional) Assign a class to the search form
     * @param $return boolean (Optional) Whether or not to return or echo $html
     *
     * @return string $html   Returns a string of markup if $return flag is set to true
     */
    public function render_search_form( $class = '', $return = false ) {

        // Build out the actual form markup
        $html = '';

        $html .= '<form class="uwhr-search ' . $class . '" role="search" method="POST" action="' . get_home_url( 1, '/' ) . '">';

            $html .= '<div class="form-group search-wrapper">';

                $html .= '<label class="sr-only" for="s">Enter search text: </label>';
                $html .= '<input class="search-input form-control search-filed" type="search" placeholder="Search UWHR" value="' . get_search_query() . '" name="s" title="Search for:">';

                $html .= '<div class="search-icons">';
                    $html .= '<div class="icon erase-icon hide"><i class="fa fa-close"></i></div>';
                    $html .= '<div class="icon search-icon"><i class="fa fa-search"></i></div>';
                $html .= '</div>';

            $html .= '</div>';

            $filters = $this->parent->default_search['filter'];

            foreach ( $filters as $filter ) {
                $html .= '<input type="hidden" name="filter[]" value="' . $filter . '">';
            }

            $html .= '<input type="hidden" name="sort" value="rel">';

        $html .= '</form>';

        if ( $return ) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
     * UI - Search
     *
     * The actual search page which contains a search form, some filtering and sorting
     * elements, the search results, and error handling.
     *
     * Our operating procedure is to display
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     */
    public function search_ui() {
        $parent = $this->parent;

        // Get search params
        $searchParams = $parent->get_search_params();

        // Get the search results!
        $results = $parent->get_search_results( $searchParams );

        // Include each filtering option
        foreach( $parent->filter_options as $fo ) {
            $filtering[] = $searchParams[$fo['slug']];
        }

        echo '<script type="text/javascript">var searchQuery="' . get_search_query() . '";</script>';

        // Build out the actual form markup
        $html = '';

        $html .= '<div class="uwhr-search-results">';
            $html .= '<form class="uwhr-search" id="searchResultsForm" role="search" method="POST" action="' . get_home_url( 1, '/' ) . '">';
                $html .= '<div class="row">';

                    // Search controls sidebar
                    $html .= '<div class="col-md-3 col-xl-2">';
                        $html .= '<div class="sidebar-filtering hidden-sm-down">';
                            $html .= $this->_ui_form_get_filtering( $filtering );
                            $html .= $this->_ui_submit_button(array('submitBtnText' => 'Filter Results'));
                        $html .= '</div>';
                    $html .= '</div>';

                    $html .= '<div class="col-md-9 col-xl-8">';
                        $html .= '<div class="form-group row">';
                            $html .= '<div class="col-xs-9 col-sm-10 col-md-12">';
                                $html .= '<div class="search-wrapper">';

                                    $html .= '<label class="sr-only" for="s">Enter search text: </label>';
                                    $html .= '<input class="search-input form-control form-control-lg" type="search" placeholder="Search UWHR" value="' . get_search_query() . '" name="s" title="Search for:">';

                                    $html .= '<div class="search-icons">';
                                        $html .= '<div class="icon erase-icon hide"><i class="fa fa-close"></i></div>';
                                        $html .= '<div class="icon search-icon"><i class="fa fa-search"></i></div>';
                                    $html .= '</div>';

                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="col-xs-3 col-sm-2 hidden-md-up">';
                                $html .= '<div class="text-xs-center">';
                                    $html .= '<button class="uwhr-mobile-filter-toggle btn btn-success-outline collapsed gold no-bb" type="button" data-toggle="collapse" href="#mobileFiltering" aria-expanded="false" aria-controls="mobileFiltering">';
                                        $html .= '<span class="fa fa-sliders fa-2x"></span>';
                                    $html .= '</button>';
                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '</div>';

                        $html .= '<div class="mobile-filtering">';
                            $html .= '<div class="collapse" id="mobileFiltering">';
                                $html .= '<div class="mobile-filtering-wrapper m-b p-b">';

                                    $html .= $this->_ui_form_get_filtering( $filtering, array('makeTwoColumns' => true, 'submitBtnText' => 'Save') );
                                    $html .= $this->_ui_form_get_sorting( $searchParams, array('showSelectOptions' => true) );

                                    $html .= $this->_ui_submit_button(array('submitBtnText' => 'Save'));

                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="search-feedback row"></div>';

                        $html .= '<div class="search-meta row m-b">';
                            $html .= '<div class="col-xs-12">';

                            if ( $results['error'] ) {
                                $html .= $this->_ui_error_search_handler( $results['error'] );
                            } else {
                                $html .= $this->_ui_form_get_results_meta( $results['total'] );
                                $html .= $this->_ui_form_get_sorting( $searchParams );
                            }

                            $html .= '</div>';
                        $html .= '</div>';

                        $html .= '<div class="search-warning row m-b ' . ( ( $results['warning'] ) ? '' : 'hidden' ) . '">';
                            $html .= '<div class="col-xs-12">';

                            if ( $results['warning'] ) {
                            	$html .= $this->_ui_warning_search_handler( $results['warning'] );
                           	}

                            $html .= '</div>';
                        $html .= '</div>';

                        // Container for all the .search-result(s)
                        $html .= '<div class="search-results row">';
                            $html .= '<div class="col-xs-12">';

                                $html .= $this->_search_results_ui( $results['results'] );

                            $html .= '</div>';
                        $html .= '</div>'; // .search-results

                        $html .= $this->_ui_searching_dubs();

                        // Pagination at bottom
                        $html .= '<div class="search-results-pagination row"></div>';

                    $html .= '</div>'; // .col
                $html .= '</div>'; // .row
            $html .= '</form>';
        $html .= '</div>'; // .uwhr-search-results

        echo $html;
    }

    /**
     * UI - Search Results
     *
     * Returns some markup for the entire search results list. Makes calls to the various
     * search result displays.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param $posts array The array of WP Post Objects
     *
     * @return $html string Markup to be rendered
     */
    private function _search_results_ui( $posts ) {
        $html = '';

        $html .= '<h3 class="sr-only">Search Results</h3>';

        // Keep track of how many we've printed
        $i = 0;

        foreach( $posts as $post ) {
            $i++;

            // Jump out of loop if we've reached the result cap
            if ( $this->parent->cap_check($i) ) {
                break;
            }

            if ( isset( $post->is_peep ) AND $post->is_peep ) {
                $html .= $this->_search_result_peep_ui( $post );
            } else {
                // Switch to post's blog so we can grab stuff from it
                switch_to_blog( $post->blog_id );
                $html .= $this->_search_result_ui( $post );
                #restore_current_blog();
            }
        } // end foreach $posts

        return $html;
    }

    /**
     * UI - Search Result
     *
     * Returns some markup for a single search result.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param $post WP_Post The WP Post Object
     *
     * @return $html string Markup to be rendered
     */
    private function _search_result_ui( $post ) {

        // Grab post_id
        $post_id = $post->ID;

        $html = '';

        // Build the search result markup
        $html .= '<li class="search-result">';

        $form = ( get_post_type( $post ) == 'form' ) ? true : false;

        // The search result is a form
        if ( $form ) {

            $form_file = get_form_file( $post_id );

            $mime_type = $form_file['mime_type'];
            $card_file_class = uwhr_mime_type_format($mime_type, 'card');

            $html .= '<div class="card-file ' . $card_file_class . ' bg-lightgold p-a ">';
                $html .= '<h4 class="beefy" id="post-' . $post_id . '">' . get_the_title($post_id) . '</h4>';
                $html .= '<p class="h6"><a class="btn btn-success btn-sm text-xs-center" href="' . $form_file['url'] . '">Download</a></p>';
            $html .= '</div>';

        // The search result is anything else
        } else {

            $html .= '<h4 id="post-' . $post_id . '" class="search-result-title">';
                $html .= '<a href="' . get_permalink($post_id) . '" rel="bookmark" title="Permanent Link to ' . get_the_title($post_id) . '"> ' . get_the_title($post_id) . '</a> ';
            $html .= '</h4>';

            $html .= '<p class="m-a-0">' . uwhr_get_the_excerpt($post_id) . '</p>';
            $html .= '<p class="m-a-0"></p>';
            $html .= '<p><em><small><a class="black" href="' . get_permalink($post_id) . '" rel="bookmark" title="Permanent Link to ' . get_the_title($post_id) . '">' . get_permalink($post_id) . '</a>';

                // Useful for debugging sorting stuff
                $html .= ' | Published: ' . get_the_date( 'F j, Y', $post_id ) . ' | Weight: ' . $post->weight;

            $html .= '</small></em></p>';

        }

        $html .= '</li>';

        return $html;
    }

    /**
     * UI - Search Result Peep
     *
     * Returns some markup for a single people search result.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @see _get_people()
     *
     * @param $peep Object A custom person object
     *
     * @return $html string Markup to be rendered
     */
    private function _search_result_peep_ui( $peep ) {
        $html = '';

        // Build the peep search result markup
        $html .= '<li class="search-result">';
            $html .= '<div class="bg-lightgold p-a peep">';
                $html .= '<h4 class="gold m-a-0">' . $peep->full_name . '</h4>';
                $html .= '<p class="bold m-a-0">' . $peep->jobtitle . '</p>';
                $html .= '<p class="m-a-0">' . $peep->unit . '</p>';
                $html .= '<p class="m-a-0">' . $peep->phone . '</p>';
                $html .= '<p class="m-a-0"><a href="mailto:' . $peep->email .'">' . $peep->email . '</a></p>';
            $html .= '</div>';
        $html .= '</li>';

        return $html;
    }

    /**
     * UI - Search Results - Meta
     *
     * Returns some markup for search results meta section.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $displaying int Number of posts that will be displayed, will not be great than self::CAP
     * @param $tooManyWarningFlag boolean
     *
     * @return $html string Markup to be rendered
     */
    private function _ui_form_get_results_meta( $total ) {
        $html = '';

        // The span #countCurrentPage will be changed via javascript's pagination implemention
        $html .= '<div class="search-results-meta pull-md-left text-xs-center">';
            $html .= '<p class="h6 text-sm thin display-inline-block">Displaying <span id="countCurrentPage">' . $total . '</span> of ' . $total . ' result' . ( $total === 1 ? '' : 's' ) . '</p>';
        $html .= '</div>';

        return $html;
    }

    /**
     * UI - Search Results - Sorting
     *
     * Returns some markup for search sorting.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $search_params array All the selected search parameters
     * @param $args array Array of optional arguments for displaying the ui element
     *
     * @return $html string Markup to be rendered
     */
    private function _ui_form_get_sorting( $search_params, $args = array() ) {

        $defaults = array(
            'showSelectOptions' => false
        );
        $args = array_merge($defaults, $args);

        $html = '';

        if ( $args['showSelectOptions']) {

            $html .= '<div class="search-sorting m-b" role="region" aria-label="Sort Options">';
                $html .= '<p class="h5 heading gold">Sort</p>';

                $html .= '<select class="c-select" name="sort">';
                    foreach ( $this->parent->sort_options as $so ) {
                        if ( $search_params['sort'] === $so['slug'] ) {
                            $html .= '<option value="' . $so['slug'] . '" selected>' . $so['clean'] . '</option>'; ;
                        } else {
                            $html .= '<option value="' . $so['slug'] . '">' . $so['clean'] . '</option>';
                        }
                    }

                $html .= '</select>';

            $html .= '</div>';

        } else {
            // Check to see if we've already filtered the other options
            foreach ( $this->parent->filter_options as $fo ) {
                $filteredURL = '';
                $filteredURL .= isset($_GET[$fo['slug']]) ? '&'.$fo['slug'].'=' . $_GET[$fo['slug']] : null;
            }

            $html .= '<div class="search-sorting pull-md-right m-b">';

                $html .= '<div class="no-js-hide">';
                    $html .= '<p class="h6 text-sm m-r thin display-inline-block">Sort by: </p>';
                    $html .= '<div class="dropdown dropdown-sm display-inline-block">';
                        $html .= '<a class="btn btn-info btn-sm text-left" href="#" id="sortLabel" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                            foreach ( $this->parent->sort_options as $so ) {
                                if ( $search_params['sort'] === $so['slug'] ) {
                                    $html .= $so['clean'];
                                }
                            }
                        $html .= ' <i class="fa fa-angle-down icon"></i></a>';
                        $html .= '<div class="dropdown-menu dropdown-menu-right" aria-labelledby="sortLabel">';

                        // Output each type of sorting option
                        foreach ( $this->parent->sort_options as $so ) {
                            $sort = $so['slug'] !== $this->parent->default_search['sort'] ? '&sort=' . $so['slug'] : '';
                            $html .= '<small><a class="dropdown-item no-bb" href="' . get_site_url( 1, '/' ) . '?s=' . urlencode( get_search_query() ) . $filteredURL . $sort . '">' . $so['clean'] . '</a></small>';
                        }

                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';

                // No JS Version
                $html .= '<div class="no-js-display-block">';
                    $html .= '<p class="h6 thin display-inline-block">Sort by: </p>';
                    // Output each type of sorting option
                    foreach ( $this->parent->sort_options as $so ) {
                        $sort = $so['slug'] !== $this->parent->default_search['sort'] ? '&sort=' . $so['slug'] : '';
                        $html .= '<a class="btn btn-info btn-sm m-l" href="' . get_site_url( 1, '/' ) . '?s=' . urlencode( get_search_query() ) . $filteredURL . $sort . '">' . $so['clean'] . '</a>';
                    }
                $html .= '</div>';

            $html .= '</div>';

        }

        return $html;
    }

    /**
     * UI - Search Results - Filtering
     *
     * Returns some markup for search results filtering.
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @param $filtering array An array of the filtered param data
     * @param $args array Array of optional arguments for displaying the ui element
     *
     * @return $html string Markup to be rendered
     */
    private function _ui_form_get_filtering( $filtering, $args = array() ) {

        $defaults = array(
            'makeTwoColumns' => false,
        );
        $args = array_merge($defaults, $args);

        // Because this function gets called more than once, let's name checkbox
        // groups differently so user interactions dont interfere
        $unique = wp_generate_password(4,false);

        $html = '';

        $html .= '<div class="search-filters" role="region" aria-label="Filter Options">';

            $html .= '<p class="h5 heading gold">Filter <span class="fa fa-sliders fa-lg pull-right hidden-sm-down"></span></p>';

            // Keep track of
            $filterIndex = 0;
            foreach ( $this->parent->filter_options as $filter ) {

                // Are all things checked?
                ${'allChecked'.$filter['slug']} = $filtering[$filterIndex] === $this->parent->default_search[$filter['slug']] ? true : false;

                $i = 0;
                $half = floor(count($filter['options'])) / 2;

                // Let's print out all the checkboxes
                $html .= '<ul class="list-unstyled">';
                foreach ( $filter['options'] as $option ) {

                    if ( $i == $half AND $args['makeTwoColumns'] ) {
                        $html .= '</ul>';
                        $html .= '<ul class="list-unstyled">';
                    }

                    if ( ${'allChecked'.$filter['slug']} ) {
                        $checked = '';
                    } else {
                        $checked = ( in_array( $option['slug'], $filtering[$filterIndex] ) ) ? ' checked="checked"' : '';
                    }

                    $html .= '<li>';
                        $html .= '<label for="' . $option['slug'] . '-' . $unique . '" class="c-input c-checkbox filter">';
                            $html .= '<input id="' . $option['slug'] . '-' . $unique . '" type="checkbox" name="'.$filter['slug'].'[]" value="' . $option['slug'] . '"' . $checked . ' aria-label="Filter results by ' . $option['title'] . '">';
                            $html .= '<span class="c-indicator"></span>';
                            $html .= $option['title'];
                        $html .= '</label>';
                    $html .= '</li>';

                    $i++;
                }
                $html .= '</ul>';

                $filterIndex++;
            }

        $html .= '</div>';

        return $html;
    }

    /**
     * UI - Sumbit Button
     *
     * Returns some markup for a sumbit button
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2016, University of Washington
     * @since 0.7.0
     * @package UWHR
     *
     * @param $args array Array of optional arguments for displaying the ui element
     *
     * @return $html string Markup to be rendered
     */
    private function _ui_submit_button( $args = array() ) {
        $defaults = array(
            'submitBtnText' => 'Filter Results'
        );
        $args = array_merge($defaults, $args);

        $html = '';

        $html .= '<div class="text-xs-right text-md-left">';
            $html .= '<label class="sr-only" for="search-submit">' . $args['submitBtnText'] . '</label>';
            $html .= '<button class="btn btn-success btn-sm m-b" id="search-submit" role="button" aria-label="' . $args['submitBtnText'] . '">' . $args['submitBtnText'] . '</button>';

            $params = $this->parent->get_search_params();
            if ( count( $params['filter'] ) != count($this->parent->filter_options[0]['options']) ) {
                $html .= '<p><a href="#" class="btn btn-clear btn-xs" id="formReset">Reset Filters</a></p>';
            }

        $html .= '</div>';

        return $html;
    }

    /**
     * UI - Search Results End
     *
     * Returns some markup for search results end included a picture of dubs the searching dog
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.3.0
     * @package UWHR
     *
     * @return $html string Markup to be rendered
     */
    private function _ui_searching_dubs() {
        $html = '';

        $html .= '<div class="search-searching-dubs row" id="searchingDubs">';
            $html .= '<div class="col-md-8 searching-dubs-col-text">';
                $html .= get_option( 'search_settings_ui' )['search_end'];
            $html .= '</div>';

            $html .= '<div class="col-md-4 searching-dubs-col-img">';
                $html .= '<img src="' . get_template_directory_uri() . '/assets/images/search-end.png" alt="Dubs with a magnifying glass">';
            $html .= '</div>';
        $html .= '</div>';

        return $html;

    }

    /**
     * Error Search Handler
     *
     * If there's a server error when searching, this will handle it. Errors on the client are handled with JS
     *
     * @see assets/js/search-results.js
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param $error string The error that occurred
     *
     * @return $html string The markup to be rendered
     */
    private function _ui_error_search_handler( $error ) {
        $html = '';
        switch ( $error ) {
        	case 'no_results':
                $html .= '<p class="text-xs-center h2 beefy purple">Sorry, no results for \'' . get_search_query() . '\'</p>';
                break;

            default:
                $html .= '<p class="text-xs-center h2 beefy purple">Uh oh. There was an error when searching. Try again.</p>';
        }
        return $html;
    }

    /**
     * Warning Search Handler
     *
     * If there's a server warning when searching, this will handle it. Errors on the client are handled with JS
     *
     * @see assets/js/search-results.js
     *
     * @author Steven Speicher <stvns@uw.edu>
     * @copyright Copyright (c) 2015, University of Washington
     * @since 0.1.0
     * @package UWHR
     *
     * @param $warning string The warning that occurred
     *
     * @return $html string The markup to be rendered
     */
    private function _ui_warning_search_handler( $warning ) {
        $html = '';
        switch ( $warning ) {
            case 'cap':
                $html .= '<p class="text-xs-center h4 beefy">Your search for \'' . get_search_query() . '\' yielded more than ' . $this->parent->cap_search_results . ' results.</p>';
                break;

            default:
                $html .= '<p class="text-xs-center h4 beefy">Something went wrong while searching.</p>';
        }
        return $html;
    }
}
