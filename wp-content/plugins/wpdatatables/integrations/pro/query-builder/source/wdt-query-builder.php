<?php

namespace WDTIntegration;

defined('ABSPATH') or die('Access denied.');

// Full path to the WDT WP Posts Builder root directory
define('WDT_WP_QUERY_PATH', WDT_PRO_INTEGRATIONS_PATH . 'query-builder/');
// Full URL to the WDT WP Posts Builder root directory
define('WDT_WP_QUERY_URL', WDT_PRO_INTEGRATIONS_URL . 'query-builder/');
// Path to the assets directory of the WP Posts Builder integration
define('WDT_WP_QUERY_ASSETS_PATH', WDT_WP_QUERY_URL . 'assets/');
define('WDT_WP_QUERY_INTEGRATION', true);

use Exception;
use WDTConfigController;
use WDTIntegration\wpDataTableQueryConstructor as wpDataTableQueryConstructor;
use WP_Query;
use WP_User_Query;

/**
 * Class WPQueryIntegration
 *
 * @package WDTQueryIntegration
 */
class WPQueryIntegration
{
    /**
     * @return void
     */
    public static function init()
    {
        add_action('wpdatatables_enqueue_constructor_scripts', array('WDTIntegration\WPQueryIntegration', 'enqueueWPQueryConstructorScripts'));

        add_action('wpdatatables_add_table_constructor_type_in_wizard', array('WDTIntegration\WPQueryIntegration', 'addNewTableTypes'));

        add_action('wp_ajax_wpdatatables_generate_live_wp_posts_preview', array('WDTIntegration\WPQueryIntegration', 'wdtGenerateWpQueryLivePreview'));

        add_action('wp_ajax_wpdatatables_generate_wp_posts_query_preview', array('WDTIntegration\WPQueryIntegration', 'wdtGenerateWpQueryPreviewTable'));

        add_filter('wpdatatables_filter_insert_table_array', array('WDTIntegration\WPQueryIntegration', 'wdtExtendTableConfig'), 10, 1);

        add_action('wp_ajax_wpdatatables_constructor_generate_wp_query_wdt', array('WDTIntegration\WPQueryIntegration', 'wdtGenerateWpQueryTable'));

        add_action('wpdatatables_generate_wp_posts_query', array('WDTIntegration\WPQueryIntegration', 'wpPostsBasedConstruct'), 10, 3);

        add_action('wpdatatables_add_data_source_tab', array('WDTIntegration\WPQueryIntegration', 'wdtAddIntegrationDataSourceTab'), 10, 1);

        add_action('wpdatatables_enqueue_on_edit_page', array('WDTIntegration\WPQueryIntegration', 'wdtEnqueueWpPostsScripts'));

        add_filter('wpdatatables_possible_values_wp_posts_query', array('WDTIntegration\WPQueryIntegration', 'getPossibleWPQueryValuesRead'), 10, 3);

        add_filter('wpdatatables_filter_cell_output', array('WDTIntegration\WPQueryIntegration', 'allowHtmlInColumn'), 10, 3);
    }

    /**
     * @return void
     */
    public static function wdtEnqueueWpPostsScripts()
    {
        wp_enqueue_script(
            'wdt-wp-post-config',
            WDT_WP_QUERY_ASSETS_PATH . 'wp_posts_table_config.js',
            array(),
            WDT_CURRENT_VERSION,
            true
        );
    }

    /**
     * @return void
     */
    public static function enqueueWPQueryConstructorScripts()
    {
        wp_enqueue_script(
            'wdt-wp-query-constructor-main-js',
            WDT_WP_QUERY_ASSETS_PATH . 'wdt.wp-query-constructor.js',
            array(),
            WDT_CURRENT_VERSION,
            true
        );
        wp_enqueue_style(
            'wdt-wp-query-css',
            WDT_WP_QUERY_ASSETS_PATH . 'wdt-wp-query-builder.css',
            array(),
            WDT_CURRENT_VERSION
        );
    }


    /**
     * Adds new table types options in table wizard
     */
    public static function addNewTableTypes()
    {
        ob_start();
        include 'templates/woo_table_type_block.inc.php';
        $newTableType = ob_get_contents();
        ob_end_clean();

        ob_start();
        include __DIR__ . '/../templates/new_table_type_block.inc.php';
        $newTableTypeBlock = ob_get_contents();
        ob_end_clean();

        echo $newTableTypeBlock;
    }

    /**
     * @param $tableType
     * @return void
     */
    public static function wdtAddIntegrationDataSourceTab($tableType)
    {
        if ($tableType == 'wp_posts_query') {
            ob_start();
            include WDT_TEMPLATE_PATH . 'admin/constructor/steps/query_builder_parts/constructor_2_posts.inc.php';
            $wpPostsDataSource = apply_filters('wpdatatables_query_data_source_block', ob_get_contents());
            ob_end_clean();
            echo $wpPostsDataSource;
        }

        if ($tableType == 'woo_commerce') {
            ob_start();
            include WDT_TEMPLATE_PATH . 'admin/constructor/steps/query_builder_parts/constructor_2_woo_commerce.inc.php';
            $wooCommerceDataSource = apply_filters('wpdatatables_query_data_source_block', ob_get_contents());
            ob_end_clean();
            echo $wooCommerceDataSource;
        }
    }

    /**
     * @throws Exception
     */
    public static function wdtGenerateWpQueryTable()
    {
        $tableData = self::sanitizeArray($_POST['tableData']);
        $tableData = apply_filters('wpdatatables_before_generate_wp_posts_query_table', $tableData);

        include_once dirname(__FILE__) . '/class.query.constructor.php';
        $constructor = new wpDataTableQueryConstructor($tableData['method'], $tableData['connection'],
            self::sanitizePostsQueryData($_POST['queryData']));
        $res = $constructor->generateWdtBasedOnWpQuery($tableData);
        if (empty($res->error)) {
            $res->link = get_admin_url() . "admin.php?page=wpdatatables-constructor&source&table_id=$res->table_id";
        }

        echo json_encode($res);
        exit();
    }

    /**
     * @return void
     */
    public static function wdtGenerateWpQueryPreviewTable()
    {
        $method = sanitize_text_field(isset($_POST['tableData']['method']) ? $_POST['tableData']['method'] : 'wp_posts_query');

        $queryData = self::sanitizePostsQueryData($_POST['queryData']);
        $query = self::buildQuery($queryData);

        $result = array(
            'queryParameters' => $query,
            'preview' => $method == 'wp_posts_query' ?
                self::buildQueryPreview($query) :
                WooCommerceIntegration::buildQueryPreview($query)
        );

        echo json_encode($result);
        exit();
    }

    /**
     * @return void
     */
    public static function wdtGenerateWpQueryLivePreview()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
            exit();
        }

        $tableData = self::sanitizeArray($_POST['tableData']);
        $method = $tableData['method'];

        if (!isset($_POST['queryData'])) {
            $result = array(
                'preview' => $method == 'wp_posts_query' ? __('No posts found.', 'wpdatatables') : __('No products found.', 'wpdatatables')
            );

            echo json_encode($result);
            exit();
        }
        $queryData = self::sanitizePostsQueryData($_POST['queryData']);
        $query = self::buildQuery($queryData);
        $result = array(
            'preview' => $method == 'wp_posts_query' ?
                self::buildQueryPreview($query) :
                WooCommerceIntegration::buildQueryPreview($query)
        );

        echo json_encode($result);
        exit();
    }

    /**
     * @param $query
     * @return string
     */
    public static function buildQueryPreview($query): string
    {
        if ($query->have_posts()) {
            $columns = [
                'ID', 'Post Author', 'Post Date', 'Post Date GMT', 'Post Content', 'Post Title',
                'Post Excerpt', 'Post Status', 'Comment Status', 'Ping Status', 'Post Password',
                'Post Name', 'To Ping', 'Pinged', 'Post Modified', 'Post Modified GMT',
                'Post Content Filtered', 'Post Parent', 'GUID', 'Menu Order', 'Post Mime Type',
                'Comment Count', 'Filter', 'Ancestors', 'Page Template', 'Post Category', 'Tags Input'
            ];

            $preview = '<table class="table table-condensed"><thead><tr>';
            foreach ($columns as $column) {
                $preview .= '<th>' . esc_html($column) . '</th>';
            }
            $preview .= '</tr></thead><tbody>';
            while ($query->have_posts() && $query->current_post < 5) {
                $query->the_post();
                $preview .= '<tr>';
                $preview .= '<td>' . esc_html(get_the_ID()) . '</td>';
                $preview .= '<td>' . esc_html(get_the_author()) . '</td>';
                $preview .= '<td>' . esc_html(get_the_date()) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('post_date_gmt')) . '</td>';
                $preview .= '<td>' . wp_kses_post(get_the_content()) . '</td>';
                $preview .= '<td>' . esc_html(get_the_title()) . '</td>';
                $preview .= '<td>' . wp_kses_post(get_the_excerpt()) . '</td>';
                $preview .= '<td>' . esc_html(get_post_status()) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('comment_status')) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('ping_status')) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('post_password')) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('post_name')) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('to_ping')) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('pinged')) . '</td>';
                $preview .= '<td>' . esc_html(get_the_modified_date()) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('post_modified_gmt')) . '</td>';
                $preview .= '<td>' . wp_kses_post(get_post_field('post_content_filtered')) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('post_parent')) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('guid')) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('menu_order')) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('post_mime_type')) . '</td>';
                $preview .= '<td>' . esc_html(get_comments_number()) . '</td>';
                $preview .= '<td>' . esc_html(get_post_field('filter')) . '</td>';

                // Ancestors
                $ancestors = get_post_field('ancestors');
                if (is_array($ancestors)) {
                    $ancestors = implode(', ', $ancestors);
                }
                $preview .= '<td>' . esc_html($ancestors) . '</td>';

                // Custom meta and taxonomies
                $preview .= '<td>' . esc_html(get_post_meta(get_the_ID(), '_wp_page_template', true)) . '</td>';
                $preview .= '<td>' . esc_html(implode(', ', wp_get_post_categories(get_the_ID(), array('fields' => 'names')))) . '</td>';
                $preview .= '<td>' . esc_html(implode(', ', wp_get_post_tags(get_the_ID(), array('fields' => 'names')))) . '</td>';
                $preview .= '</tr>';
            }

            $preview .= '</tbody></table>';
            wp_reset_postdata();
            return $preview;
        }
        return __('No posts found.', 'wpdatatables');
    }


    /**
     * @param $params
     * @return WP_Query
     */
    public static function buildQuery($params): WP_Query
    {
        $args = array();

        foreach ($params as $param => $value) {
            // Handle _in and _not_in parameters which need to be arrays
            if (strpos($param, '__in') !== false || strpos($param, '__not_in') !== false) {
                if (is_string($value)) {
                    $value = array_map('trim', explode(',', $value));
                    $args[$param] = $value;
                } elseif (is_array($value)) {
                    $args[$param] = $value;
                } else {
                    $args[$param][] = $value;
                }
            } elseif (substr($param, -6) === '_query' && $value !== "false") {

                // Handle special structures - Meta (custom fields), Taxonomy and Date Queries
                $baseName = substr($param, 0, -6);
                $methodName = 'create' . ucfirst($baseName) . 'Query';
                self::$methodName($args, $value);
            } else {
                $args[$param] = $value;
            }
        }
        // Handle product variations if a parent is set for Woo products
        if (isset($args['post_parent']) && $args['post_type'] === 'product') {
            $args['post_type'] = 'product_variation';
        }
        $args = apply_filters('wpdatatables_before_wp_query_arguments', $args);

        return new WP_Query($args);
    }

    /**
     * @param $args
     * @param $taxonomies
     * @return void
     */
    public static function createTaxQuery(&$args, $taxonomies)
    {
        if (!$taxonomies || (is_array($taxonomies) && empty($taxonomies)) ||
            (is_object($taxonomies) && empty((array)$taxonomies))) return;

        $args['tax_query'] = [];
        $relation = '';

        foreach ($taxonomies as $index => $taxonomy) {
            if ($index === 'relation') {
                $relation = $taxonomy;
            } elseif (is_array($taxonomy)) {
                $taxonomy['operator'] = isset($taxonomy['operator'])  ? $taxonomy['operator'] : 'IN';
                $args['tax_query'][] = $taxonomy;
            } elseif (is_string($taxonomy)) {
                $args['tax_query'][] = [
                    'taxonomy' => $taxonomy,
                    'operator' => 'IN',
                ];
            }
        }

        if (!empty($args['tax_query']) && !empty($relation)) {
            $args['tax_query']['relation'] = $relation;
        }
        $args['tax_query'] = apply_filters('wpdatatables_before_wp_query_tax_arguments', $args['tax_query']);
    }

    /**
     * @param $args
     * @param $customFields
     * @return void
     */
    public static function createMetaQuery(&$args, $customFields)
    {
        if (!$customFields || (is_array($customFields) && empty($customFields)) ||
            (is_object($customFields) && empty((array)$customFields))) return;

        // No meta_query set, only "relation"
        if (count($customFields) === 1 && isset($customFields['relation'])) {
            return;
        }

        // Single meta query, doesn't require relation
        if (count($customFields) === 2) {
            $customFieldsArr = (array)$customFields;
            $args['meta_query'][] = $customFieldsArr[0] ?? $customFields;
            $args['meta_query'] = apply_filters('wpdatatables_before_wp_query_meta_arguments', $args['meta_query']);
            return;
        }

        // Multiple custom fields with relation
        $args['meta_query'] = [];
        foreach ($customFields as $index => $customField) {
            if ($index == 'relation') {
                $args['meta_query']['relation'] = $customField;
            } else {
                $args['meta_query'][] = $customField;
            }
        }
        $args['meta_query'] = apply_filters('wpdatatables_before_wp_query_meta_arguments', $args['meta_query']);
    }

    /**
     * @param $args
     * @param $dateClauses
     * @return void
     */
    public static function createDateQuery(&$args, $dateClauses)
    {
        if (!$dateClauses || (is_array($dateClauses) && empty($dateClauses)) ||
            (is_object($dateClauses) && empty((array)$dateClauses))) return;
        $containsSubArrays = false;
        foreach ($dateClauses as $key => $value) {
            if ($key === "relation") {
                $containsSubArrays = true;
                break;
            }
        }

        // Only one date clause
        if (!$containsSubArrays) {
            $dateClauseArr = (array)$dateClauses;
            $args['date_query'] = $dateClauseArr[0] ?? $dateClauses;
            $args['date_query'] = apply_filters('wpdatatables_before_wp_query_date_arguments', $args['date_query']);
            return;
        }

        // Multiple date clauses with relation
        $relation = isset($dateClauses['relation']) ? $dateClauses['relation'] : null;
        $args['date_query'] = [];

        // Loop through the date clauses, skipping 'relation'
        foreach ($dateClauses as $key => $dateClause) {
            if ($key !== 'relation') {
                $args['date_query'][] = $dateClause;
            }
        }

        // Add the relation to the date_query if it was set
        if ($relation) {
            $args['date_query']['relation'] = $relation;
        }

        // Apply filter to date_query
        $args['date_query'] = apply_filters('wpdatatables_before_wp_query_date_arguments', $args['date_query']);
    }


    /**
     * @param $wpDataTable
     * @param $content
     * @param $wdtParameters
     * @return mixed|void
     */
    public static function wpPostsBasedConstruct($wpDataTable, $content, $wdtParameters)
    {
        $queryData = json_decode($content);
        $queryData = self::sanitizePostsQueryData($queryData);

        if ($queryData == '') {
            $result = array(
                'success' => '',
                'error' => __('Cannot create an empty table', 'wpdatatables')
            );
            echo json_encode($result);
            exit();
        }

        if ($wpDataTable->isAjaxReturn()) {
            self::ajaxReturnConstruct($queryData, $wpDataTable, $wdtParameters);
        } else {
            // Retrieve all posts if pagination isn't set, as WP default pagination is set to 10
            $queryData->posts_per_page = $queryData->posts_per_page ?? "-1";
            $query = self::buildQuery($queryData);
            $postTableColumns = self::getPostTableColumns($query);

            return $wpDataTable->arrayBasedConstruct($postTableColumns, $wdtParameters);
        }
    }

    /**
     * @param $query
     * @return array|void
     */
    public static function getPostTableColumns($query)
    {
        $postTableColumns = array();
        if ($query->have_posts()) {
            $excludedTaxonomies = ['category', 'post_tag', 'post_format', 'product_visibility', 'product_tag',
                'product_cat','product_type', 'product_shipping_class', 'pa_product_style', 'pa_weight'];
            $excludedTaxonomies = apply_filters('wpdatatables_filter_taxonomies_before_building', $excludedTaxonomies);
            $usedTaxonomies = [];
            // Loop through all posts to find which taxonomies have terms
            while ($query->have_posts()) {
                $query->the_post();
                $postId = get_the_ID();

                $taxonomies = get_object_taxonomies(get_post_type($postId));
                foreach ($taxonomies as $taxonomy) {
                    if (in_array($taxonomy, $excludedTaxonomies)) {
                        continue;
                    }
                    $usedTaxonomies[$taxonomy] = true;
                }
            }
            wp_reset_postdata();

            while ($query->have_posts()) {
                $query->the_post();
                $postId = get_the_ID();

                $postData = [
                    'ID' => '<a href="' . get_permalink($postId) . '">' . $postId . '</a>',
                    'post_author' => '<a href="' . get_author_posts_url(get_the_author_meta('ID')) . '">' . get_the_author() . '</a>',
                    'post_date' => get_the_date(),
                    'post_date_gmt' => get_post_field('post_date_gmt', $postId),
                    'post_content' => self::getPostContentForDisplay($postId),
                    'post_title' => '<a href="' . get_permalink($postId) . '">' . get_the_title() . '</a>',
                    'post_excerpt' => get_the_excerpt(),
                    'post_status' => get_post_status(),
                    'post_category' => self::getPostCategoriesForDisplay($postId),
                    'tags_input' => self::getPostTagsForDisplay($postId),
                    'comment_status' => get_post_field('comment_status', $postId),
                    'ping_status' => get_post_field('ping_status', $postId),
                    'post_password' => self::getPostPasswordForDisplay($postId),
                    'post_name' => get_post_field('post_name', $postId),
                    'to_ping' => get_post_field('to_ping', $postId),
                    'pinged' => get_post_field('pinged', $postId),
                    'post_modified' => get_the_modified_date(),
                    'post_modified_gmt' => get_post_field('post_modified_gmt', $postId),
                    'post_content_filtered' => get_post_field('post_content_filtered', $postId),
                    'post_parent' => get_post_field('post_parent', $postId),
                    'guid' => get_post_field('guid', $postId),
                    'menu_order' => get_post_field('menu_order', $postId),
                    'post_mime_type' => get_post_field('post_mime_type', $postId),
                    'comment_count' => get_comments_number($postId),
                    'filter' => get_post_field('filter', $postId),
                    'ancestors' => implode(', ', get_post_ancestors($postId)),
                    'page_template' => get_post_meta($postId, '_wp_page_template', true),
                ];

                foreach (array_keys($usedTaxonomies) as $taxonomy) {
                    $terms = get_the_terms($postId, $taxonomy);
                    if ($terms && !is_wp_error($terms)) {
                        $term_links = array_map(function ($term) {
                            return '<a href="' . get_term_link($term) . '">' . $term->name . '</a>';
                        }, $terms);
                        $postData[$taxonomy] = implode(', ', $term_links);
                    } else {
                        $postData[$taxonomy] = '';
                    }
                }

                self::convertArraysToStrings($postData);
                $postTableColumns[] = $postData;
            }
            wp_reset_postdata();
            return $postTableColumns;
        }

    }

    /**
     * @param $postId
     * @return string
     */
    private static function getPostContentForDisplay($postId): string
    {
        if (is_admin()) {
            return get_the_content();
        }
        if (post_password_required($postId)) {
            return get_the_password_form();
        }

        return get_the_content();
    }

    /**
     * @param $postId
     * @return string
     */
    private static function getPostPasswordForDisplay($postId): string
    {
        if (post_password_required($postId)) {
            return get_the_password_form();
        }

        return get_post_field('post_password', $postId);
    }


    /**
     * @param $postId
     * @return string
     */
    private static function getPostCategoriesForDisplay($postId): string
    {
        $categories = wp_get_post_categories($postId, ['fields' => 'all']);
        $categoryLinks = [];

        foreach ($categories as $category) {
            $categoryLinks[] = '<a href="' . get_category_link($category->term_id) . '">' . esc_html($category->name) . '</a>';
        }

        return implode(', ', $categoryLinks);
    }

    /**
     * @param $postId
     * @return string
     */
    private static function getPostTagsForDisplay($postId): string
    {
        $tags = wp_get_post_tags($postId, ['fields' => 'all']);
        $tagLinks = [];

        foreach ($tags as $tag) {
            $tagLinks[] = '<a href="' . get_tag_link($tag->term_id) . '">' . esc_html($tag->name) . '</a>';
        }

        return implode(', ', $tagLinks);
    }

    /**
     * @param array $array
     * @return void
     */
    public static function convertArraysToStrings(array &$array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            } elseif (is_array($key)) {
                self::convertArraysToStrings($key);
            }
        }
    }

    /**
     * @param $queryData
     * @return mixed
     */
    public static function sanitizePostsQueryData($queryData)
    {
        $allowedOperators = ['<', '<=', '>', '>=', '=', '!='];

        foreach ($queryData as $key => &$value) {
            if (empty($value)) {
                unset($queryData->{$key});
                continue;
            }

            if (is_numeric($value)) {
                if (strpos($value, '.') !== false) {
                    $value = floatval($value);
                } else {
                    $value = intval($value);
                }
            }

            switch (gettype($value)) {
                case 'integer':
                    $value = intval($value);
                    break;
                case 'string':
                    if ($value === 'true') {
                        $value = true;
                    } elseif ($value === 'false') {
                        $value = false;
                    } else {
                        $value = sanitize_text_field($value);
                    }
                    break;
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'object':
                    $value = self::sanitizeObject($value);
                    break;
                case 'array':
                    array_walk_recursive($value, function (&$item, $arrayKey) use ($allowedOperators) {
                        if (is_object($item)) {
                            $item = (array)$item;
                        }

                        if (in_array($item, $allowedOperators, true)) {
                            return;
                        }

                        if (empty($item) && $item !== 0 && $item !== false) {
                            $item = null;
                        } elseif (is_numeric($item)) {
                            $item = strpos($item, '.') !== false ? floatval($item) : intval($item);
                        } elseif (is_string($item)) {
                            if ($item === 'true') {
                                $item = true;
                            } elseif ($item === 'false') {
                                $item = false;
                            } else {
                                $item = sanitize_text_field($item);
                            }
                        }
                    });
                    $value = array_filter($value, function ($item) {
                        return $item !== null;
                    });
                    break;
                default:
                    $value = sanitize_text_field($value);
                    break;
            }
        }

        return $queryData;
    }

    /**
     * @param $object
     * @return array
     */
    public static function sanitizeObject($object): array
    {
        $result = [];
        foreach ($object as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $sanitizedValue = self::sanitizeObject($value);
                if (!empty($sanitizedValue) || is_numeric($sanitizedValue) || !$sanitizedValue) {
                    $result[$key] = $sanitizedValue;
                }
            } elseif (empty($value)) {
                continue;
            } elseif (is_numeric($value)) {
                $result[$key] = intval($value);
            } elseif (is_string($value)) {
                $result[$key] = sanitize_text_field($value);
            } else {
                $result[$key] = sanitize_text_field($value);
            }
        }

        return $result;
    }


    /**
     * @param $tableConfig
     * @return mixed
     *
     * Extend the table config object to enable server-side processing
     */
    public static function wdtExtendTableConfig($tableConfig)
    {
        if ($tableConfig['table_type'] !== 'wp_posts_query' && $tableConfig['table_type'] !== 'woo_commerce') {
            return $tableConfig;
        }

        $advancedSettings = json_decode($tableConfig['advanced_settings']);
        $advancedSettings->wp_posts_query = array(
            'hasServerSideIntegration' => 1
        );
        $advancedSettings->woo_commerce = array(
            'hasServerSideIntegration' => 1
        );

        $tableConfig['advanced_settings'] = json_encode($advancedSettings);

        return $tableConfig;
    }

    /**
     * @param $queryData
     * @param $wpDataTable
     * @param $wdtParameters
     * @return void
     */
    public static function ajaxReturnConstruct($queryData, $wpDataTable, $wdtParameters)
    {
        $query = self::buildQuery($queryData);
        $totalLength = $query->found_posts;

        $columns = self::sanitizeArray($_POST['columns']);
        $order = self::sanitizeArray($_POST['order']);
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $globalSearchValue = isset($_POST['search']['value']) ? sanitize_text_field($_POST['search']['value']) : '';

        // Sorting
        if (!empty($order)) {
            $orderColumnIndex = $order[0]['column'];
            $orderDirection = $order[0]['dir'] === 'asc' ? 'ASC' : 'DESC';
            if (isset($columns[$orderColumnIndex])) {
                $queryData->orderby = $columns[$orderColumnIndex]["name"];
                $queryData->order = $orderDirection;
            }
        }

        // Filtering
        foreach ($columns as $column) {
            $columnName = $column['name'];
            $searchValue = $column['search']['value'];
            if ($searchValue) {
                switch ($columnName) {
                    case 'ID':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $queryData->post__in = array($searchValue);
                        } else {
                            $allPostIDsQuery = new WP_Query(array_merge(json_decode(json_encode($queryData), true), array(
                                'fields' => 'ids',
                                'posts_per_page' => -1,
                            )));
                            if ($allPostIDsQuery->have_posts()) {
                                $allPostIDs = $allPostIDsQuery->posts;
                            } else {
                                $allPostIDs = array();
                            }
                            $matchedIDs = array();
                            foreach ($allPostIDs as $postID) {
                                if (strpos($postID, $searchValue) !== false) {
                                    $matchedIDs[] = $postID;
                                }
                            }
                            if (!empty($matchedIDs)) {
                                $queryData->post__in = $matchedIDs;
                            } else {
                                $queryData->post__in = array(0);
                            }
                        }
                        break;
                    case 'post_author':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $queryData->author_name = $searchValue;
                        } else {
                            $authorQuery = new WP_User_Query(array(
                                'search' => '*' . esc_attr($searchValue) . '*',
                                'search_columns' => array('display_name'),
                            ));
                            $matchedAuthors = wp_list_pluck($authorQuery->get_results(), 'ID');
                            if (!empty($matchedAuthors)) {
                                $queryData->author__in = $matchedAuthors;
                            } else {
                                $queryData->author__in = array(0);
                            }
                        }
                        break;
                    case 'post_date':
                    case 'post_date_gmt':
                    case 'post_modified':
                    case 'post_modified_gmt':
                        $dateRange = explode('|', $searchValue);
                        if (count($dateRange) === 2 && $searchValue !== "|") {
                            $start_timestamp = is_numeric($dateRange[0]) ? $dateRange[0] : strtotime(str_replace('/', '-', $dateRange[0]));
                            $end_timestamp = is_numeric($dateRange[1]) ? $dateRange[1] : strtotime(str_replace('/', '-', $dateRange[1]));
                            $start_date = date('Y-m-d H:i:s', $start_timestamp);
                            $end_date = date('Y-m-d H:i:s', $end_timestamp);

                            if ($start_timestamp && $end_timestamp) {
                                $queryData->date_query = array(
                                    array(
                                        'column' => $columnName,
                                        'after' => $start_date,
                                        'before' => $end_date,
                                        'inclusive' => true,
                                    ),
                                );
                            } elseif ($start_timestamp) {
                                $queryData->date_query = array(
                                    array(
                                        'column' => $columnName,
                                        'after' => $start_date,
                                        'inclusive' => true,
                                    ),
                                );
                            } else if ($end_timestamp) {
                                $queryData->date_query = array(
                                    array(
                                        'column' => $columnName,
                                        'before' => $end_date,
                                        'inclusive' => true,
                                    ),
                                );
                            }

                        } elseif (!empty($searchValue) && $searchValue !== "|") {
                            $timestamp = is_numeric($dateRange) ? $dateRange : strtotime(str_replace('/', '-', $dateRange));
                            $date = date('Y-m-d H:i:s', $timestamp);
                            $queryData->date_query = array(
                                array(
                                    'column' => $columnName,
                                    'compare' => '=',
                                    'value' => $date,
                                )
                            );
                        }
                        break;
                    case 'post_category':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $queryData->category__in = array(get_term_by('name', $searchValue, 'category')->term_id);
                        } else {
                            $allCategories = get_terms(array(
                                'taxonomy' => 'category',
                                'hide_empty' => false,
                            ));
                            $matchedCategories = array();
                            foreach ($allCategories as $category) {
                                if (strpos($category->name, $searchValue) !== false || strpos($category->slug, $searchValue) !== false) {
                                    $matchedCategories[] = $category->term_id;
                                }
                            }
                            if (!empty($matchedCategories)) {
                                $queryData->category__in = $matchedCategories;
                            } else {
                                $queryData->category__in = array(0);
                            }
                        }
                        break;
                    case 'tags_input':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $queryData->tag__in = array(get_term_by('name', $searchValue, 'post_tag')->term_id);
                        } else {
                            $allTags = get_terms(array(
                                'taxonomy' => 'post_tag',
                                'hide_empty' => false,
                            ));
                            $matchedTags = array();
                            foreach ($allTags as $tag) {
                                if (strpos($tag->name, $searchValue) !== false || strpos($tag->slug, $searchValue) !== false) {
                                    $matchedTags[] = $tag->term_id;
                                }
                            }
                            if (!empty($matchedTags)) {
                                $queryData->tag__in = $matchedTags;
                            } else {
                                $queryData->tag__in = array(0);
                            }
                        }
                        break;
                    case 'post_content':
                    case 'post_excerpt':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $matchedPostIDs = self::getPostIdsByExactContent($searchValue, $columnName);
                        } else {
                            $matchedPostIDs = self::getPostIdsByContent($searchValue, $columnName);
                        }
                        if (!empty($matchedPostIDs)) {
                            $queryData->post__in = $matchedPostIDs;
                        } else {
                            $queryData->post__in = array(0);
                        }
                        break;
                    case 'comment_status':
                    case 'ping_status':
                    case 'post_status':
                    case 'post_type':
                    case 'post_password':
                    case 'post_mime_type':
                    case 'menu_order':
                    case 'comment_count':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $queryData->$columnName = $searchValue; // Exact match
                        } else {
                            global $wpdb;
                            $searchValueEscaped = '%' . $wpdb->esc_like($searchValue) . '%';
                            $matchingIDs = $wpdb->get_col(
                                $wpdb->prepare(
                                    "
                    SELECT ID
                    FROM {$wpdb->posts}
                    WHERE $columnName LIKE %s
                    ",
                                    $searchValueEscaped
                                )
                            );

                            if (!empty($matchingIDs)) {
                                $queryData->post__in = isset($queryData->post__in)
                                    ? array_intersect($queryData->post__in, $matchingIDs)
                                    : $matchingIDs;
                            } else {
                                $queryData->post__in = array(0);
                            }
                        }
                        break;
                    case 'post_name':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $queryData->name = $searchValue;
                        } else {
                            $matchedPostIDs = self::getPostIdsBySlugPartial($searchValue);
                            $queryData->post__in = $matchedPostIDs ?: array(0);
                        }
                        break;
                    case 'post_title':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $matchedPostIDs = self::getPostIdsByExactTitle($searchValue);

                        } else {
                            $matchedPostIDs = self::getPostIdsByTitle($searchValue);
                        }
                        if (!empty($matchedPostIDs)) {
                            $queryData->post__in = $matchedPostIDs;
                        } else {
                            $queryData->post__in = array(0);
                        }
                        break;
                    case 'to_ping':
                    case 'pinged':
                    case 'post_content_filtered':
                    case 'guid':
                    case 'filter':
                    case 'ancestors':
                    case 'page_template':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $queryData->{$columnName} = $searchValue;
                        } else {
                            $queryData->{$columnName . '__like'} = $searchValue;
                        }
                        break;
                    case 'post_parent':
                        $queryData->post_parent = intval($searchValue);
                        break;
                    default:
                        $queryData->{$columnName} = $searchValue;
                        break;
                }
            }

        }

        // Global Search
        if (!empty($globalSearchValue)) {
            $queryData->s = $globalSearchValue;
        }

        // Pagination
        $queryData->posts_per_page = $length;
        $queryData->offset = $start;

        $query = self::buildQuery($queryData);
        $tableArray = self::getPostTableColumns($query);

        $resultLength = $query->found_posts;
        $output = array(
            "draw" => (int)$_POST['draw'],
            "recordsTotal" => $totalLength,
            "recordsFiltered" => $resultLength ?: 0,
        );

        $colObjs = $wpDataTable->prepareColumns($wdtParameters);
        $output['data'] = $wpDataTable->prepareOutputData($tableArray, $wdtParameters, $colObjs);
        $output['data'] = apply_filters('wpdatatables_custom_prepare_output_data', $output['data'], $wpDataTable, $tableArray, $wdtParameters, $colObjs);
        $json = json_encode($output);
        $json = apply_filters('wpdatatables_filter_server_side_data', $json, $wpDataTable->getWpId(), $_GET);

        echo $json;
        exit();
    }

    /**
     * @param $searchValue
     * @return array
     */
    public static function getPostIdsByTitle($searchValue): array
    {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE %s",
            '%' . $wpdb->esc_like($searchValue) . '%'
        );

        return $wpdb->get_col($query);
    }

    /**
     * @param $searchTitle
     * @return array
     */
    public static function getPostIdsByExactTitle($searchTitle): array
    {
        global $wpdb;

        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s",
                $searchTitle
            )
        );
    }

    /**
     * @param $searchValue
     * @param $field
     * @return array
     */
    public static function getPostIdsByExactContent($searchValue, $field): array
    {
        global $wpdb;

        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE $field = %s",
                $searchValue
            )
        );
    }

    /**
     * @param $searchValue
     * @param $field
     * @return array
     */
    public static function getPostIdsByContent($searchValue, $field): array
    {
        global $wpdb;
        $like = '%' . $wpdb->esc_like($searchValue) . '%';
        return $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE $field LIKE %s",
            $like
        ));
    }

    /**
     * @param $searchValue
     * @return array
     */
    public static function getPostIdsBySlugPartial($searchValue): array
    {
        global $wpdb;

        $query = $wpdb->prepare(
            "
        SELECT ID
        FROM {$wpdb->posts}
        WHERE post_name LIKE %s
        ",
            '%' . $wpdb->esc_like($searchValue) . '%'
        );

        return $wpdb->get_col($query);
    }

    /**
     * @param $column
     * @param $filterByUserId
     * @param $tableData
     * @return array
     */
    public static function getPossibleWPQueryValuesRead($column, $filterByUserId, $tableData = null): array
    {
        $distValues = array();
        $parentTable = $column->getParentTable();
        $columnOrigHeader = $column->getOriginalHeader();

        $wpQueryParams = json_decode($parentTable->getTableContent(), true);

        if ($posts = get_posts($wpQueryParams)) {
            foreach ($posts as $post) {
                switch ($columnOrigHeader) {
                    case 'post_author':
                        $authorId = $post->post_author;
                        $authorData = get_userdata($authorId);
                        $postValue = $authorData ? $authorData->display_name : '';
                        break;
                    case 'post_category':
                        $categories = get_the_category($post->ID);
                        $postValue = array_map(function ($cat) {
                            return $cat->name;
                        }, $categories);
                        $postValue = implode(', ', $postValue);
                        break;
                    case 'tags_input':
                        $tags = get_the_tags($post->ID);
                        $postValue = array_map(function ($tag) {
                            return $tag->name;
                        }, $tags);
                        $postValue = implode(', ', $postValue);
                        break;
                    case 'post_title':
                        $postValue = get_the_title($post->ID);
                        break;
                    case 'ID':
                    case 'post_status':
                    case 'comment_status':
                    case 'comment_count':
                    case 'ping_status':
                    case 'post_name':
                    case 'to_ping':
                    case 'pinged':
                    case 'post_parent':
                    case 'menu_order':
                    case 'post_mime_type':
                    case 'filter':
                        $postValue = $post->{$columnOrigHeader};
                        break;
                    case 'ancestors':
                        $postValue = $post->ancestors;
                        break;
                    case 'page_template':
                        $postValue = get_post_meta($post->ID, '_wp_page_template', true);
                        break;
                    default:
                        $postValue = '';
                        break;
                }

                if ((!empty($postValue) || $postValue == "0") && !in_array($postValue, $distValues)) {
                    $distValues[] = $postValue;
                }
            }
        }

        return array_values(array_filter($distValues, function ($value) {
            return ($value !== null && $value !== false && $value !== '');
        }));
    }

    /**
     * @throws Exception
     */
    public static function allowHtmlInColumn($cellOutput, $table_id, $column_name)
    {
        $tableType = WDTConfigController::loadTableFromDB($table_id)->table_type;

        if ($tableType === 'wp_posts_query' && $column_name === 'post_content') {
            $postId = self::extractPostId($cellOutput);
            if ($postId) {
                return self::getPostContentForDisplay($postId);
            }
        }
        return $cellOutput;
    }

    /**
     * @param $cellOutput
     * @return int|null
     */
    public static function extractPostId($cellOutput): ?int
    {
        preg_match('/for="pwbox-(\d+)"/', $cellOutput, $matches);
        return isset($matches[1]) ? (int)$matches[1] : null;
    }

    public static function sanitizeArray($data) {
        $sanitizedData = [];

        if (isset($data) && is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $sanitizedData[$key] = self::sanitizeArray($value);
                } else {
                    $sanitizedData[$key] = sanitize_text_field($value);
                }
            }
        }

        return $sanitizedData;
    }
}

WPQueryIntegration::init();