<?php

namespace WDTIntegration;

use Exception;
use WC_Product_Data_Store_CPT;
use WP_Query;
use WDTConfigController;

defined('ABSPATH') or die('Access denied.');

// Full path to the WDT WooCommerce root directory
define('WDT_WOO_COMMERCE_PATH', WDT_PRO_INTEGRATIONS_PATH . 'woo-commerce/');
// URL of the WDT WooCommerce root directory
define('WDT_WOO_COMMERCE_URL', WDT_PRO_INTEGRATIONS_URL . 'woo-commerce/');
// Path to the assets directory of the WooCommerce integration
define('WDT_WOO_COMMERCE_ASSETS_PATH', WDT_WOO_COMMERCE_URL . 'assets/');
define('WDT_WOO_COMMERCE_INTEGRATION', true);

/**
 * Class WooCommerceIntegration
 *
 * @package WooCommerceIntegration
 */
class WooCommerceIntegration extends WPQueryIntegration
{
    /**
     * @return void
     */
    public static function init()
    {
        add_action('wpdatatables_enqueue_constructor_scripts', array('WDTIntegration\WooCommerceIntegration', 'enqueueWooCommerceIntegrationConstructorScripts'));

        add_action('wp_ajax_wpdatatables_constructor_generate_woo_wdt', array('WDTIntegration\WPQueryIntegration', 'wdtGenerateWpQueryTable'));

        add_action('wpdatatables_generate_woo_commerce', array('WDTIntegration\WooCommerceIntegration', 'wooCommerceBasedConstruct'), 10, 3);

        add_action('wp_ajax_wpdatatables_add_multiple_products_to_cart', array('WDTIntegration\WooCommerceIntegration', 'addProductsToCart'));
        add_action('wp_ajax_nopriv_wpdatatables_add_multiple_products_to_cart', array('WDTIntegration\WooCommerceIntegration', 'addProductsToCart'));

        add_action('wp_ajax_wpdatatables_add_single_product_to_cart', array('WDTIntegration\WooCommerceIntegration', 'addSingleProductToCart'));
        add_action('wp_ajax_nopriv_wpdatatables_add_single_product_to_cart', array('WDTIntegration\WooCommerceIntegration', 'addSingleProductToCart'));

        add_action('wpdatatables_enqueue_on_frontend', array('WDTIntegration\WooCommerceIntegration', 'wdtEnqueueWooScripts'));
        add_action('wpdatatables_enqueue_on_edit_page', array('WDTIntegration\WooCommerceIntegration', 'wdtEnqueueWooScripts'));

        add_action('wp_ajax_wpdatatables_check_woo_commerce', array('WDTIntegration\WooCommerceIntegration', 'wdtCheckIfWooIsInstalled'));
        add_action('wp_ajax_nopriv_wpdatatables_check_woo_commerce', array('WDTIntegration\WooCommerceIntegration', 'wdtCheckIfWooIsInstalled'));

        add_action('wp_ajax_wpdatatables_get_cart_info', array('WDTIntegration\WooCommerceIntegration', 'getCartInfo'));
        add_action('wp_ajax_nopriv_wpdatatables_get_cart_info', array('WDTIntegration\WooCommerceIntegration', 'getCartInfo'));

        add_action('wpdatatables_add_custom_column_type_option', array('WDTIntegration\WooCommerceIntegration', 'addWooColumnTypes'));

        add_filter('wpdatatables_filter_cell_output', array('WDTIntegration\WooCommerceIntegration', 'allowHtmlInColumn'), 10, 3);

        add_action('wpdatatables_add_table_configuration_tab', array('WDTIntegration\WooCommerceIntegration', 'addWooOptionsTab'));
        add_action('wpdatatables_add_table_configuration_tabpanel', array('WDTIntegration\WooCommerceIntegration', 'addWooOptionsTabpanel'));

        add_filter('wpdatatables_possible_values_woo_commerce', array('WDTIntegration\WooCommerceIntegration', 'getPossibleWooCommerceValuesRead'), 10, 3);
    }

    /**
     * @return void
     */
    public static function enqueueWooCommerceIntegrationConstructorScripts()
    {
        wp_enqueue_script('wdt-woo-constructor-main-js', WDT_WOO_COMMERCE_ASSETS_PATH . 'wdt.woo-constructor.js', array(), WDT_CURRENT_VERSION, true);

    }

    /**
     * @return void
     */
    public static function wdtEnqueueWooScripts()
    {
        $jsExt = get_option('wdtMinifiedJs') ? '.min.js' : '.js';
        wp_enqueue_script('wdt-woo-commerce-js', WDT_WOO_COMMERCE_ASSETS_PATH . 'wdt.woo-commerce' . $jsExt, array(), WDT_CURRENT_VERSION, true);
        wp_enqueue_style(
            'wdt-woo-commerce-css',
            WDT_WOO_COMMERCE_ASSETS_PATH . 'wdt-woo-commerce.css',
            array(),
            WDT_CURRENT_VERSION
        );
    }

    /**
     * @return void
     */
    public static function addWooColumnTypes()
    {
        ob_start();
        include 'templates/woo_column_types_option.inc.php';
        $selectColumnType = ob_get_contents();
        ob_end_clean();
        echo $selectColumnType;
    }

    /**
     * @return void
     */
    public static function addWooOptionsTab()
    {
        ob_start();
        include 'templates/woo_settings_block.inc.php';
        $wooOptionsTab = ob_get_contents();
        ob_end_clean();
        echo $wooOptionsTab;
    }

    /**
     * @return void
     */
    public static function addWooOptionsTabpanel()
    {
        ob_start();
        include 'templates/woo_settings_options_block.inc.php';
        $wooOptionsTabpanel = ob_get_contents();
        ob_end_clean();
        echo $wooOptionsTabpanel;
    }

    /**
     * @param $query
     * @return string
     */
    public static function buildQueryPreview($query): string
    {
        if ($query->have_posts()) {
            $preview = '<table class="table table-condensed"><thead><tr>';
            $columns = [
                'Product ID', 'Product Name', 'SKU', 'Price', 'Stock Status', 'Total Sales', 'Categories', 'Tags',
                'Dimensions', 'Average Rating', 'Review Count', 'Featured Image', 'Date Published', 'Short Description'
            ];

            foreach ($columns as $column) {
                $preview .= '<th>' . esc_html($column) . '</th>';
            }
            $preview .= '</tr></thead><tbody>';

            while ($query->have_posts() && $query->current_post < 5) {
                $query->the_post();
                global $product;
                global $post;
                $productId = $product->get_id();

                $preview .= '<tr>';
                $preview .= '<td>' . esc_html($productId) . '</td>';
                $preview .= '<td>' . esc_html(get_the_title()) . '</td>';
                $preview .= '<td>' . esc_html($product->get_sku()) . '</td>';
                $preview .= '<td>' . wp_kses_post(self::getProductPrice($product)) . '</td>';
                $preview .= '<td>' . esc_html($product->get_stock_status()) . '</td>';
                $preview .= '<td>' . esc_html(get_post_meta($productId, 'total_sales', true)) . '</td>';
                $preview .= '<td>' . wp_kses_post(wc_get_product_category_list($productId)) . '</td>';
                $preview .= '<td>' . wp_kses_post(wc_get_product_tag_list($productId)) . '</td>';
                $preview .= '<td>' . esc_html($product->has_dimensions() ? wc_format_dimensions($product->get_dimensions(false)) : null) . '</td>';
                $preview .= '<td>' . esc_html($product->get_average_rating()) . '</td>';
                $preview .= '<td>' . esc_html($product->get_review_count()) . '</td>';
                $preview .= '<td><img src="' . esc_url(get_the_post_thumbnail_url($productId, 'thumbnail')) . '" alt="' . get_the_title() . '"></td>';
                $preview .= '<td>' . wp_kses_post(apply_filters('woocommerce_short_description', $post->post_excerpt)) . '</td>';

                $preview .= '</tr>';
            }
            $preview .= '</tbody></table>';

            wp_reset_postdata();
            return $preview;

        }
        return __('No products found.', 'wpdatatables');
    }

    /**
     * @param $wpDataTable
     * @param $content
     * @param $wdtParameters
     * @return true
     */
    public static function wooCommerceBasedConstruct($wpDataTable, $content, $wdtParameters): bool
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
            $queryData->posts_per_page = $queryData->posts_per_page ?? "-1";
            $query = self::buildQuery($queryData);
            $productTableColumns = self::getWooCommerceTableColumns($query);
            return $wpDataTable->arrayBasedConstruct($productTableColumns, $wdtParameters);
        }
        return true;
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
                $orderByField = $columns[$orderColumnIndex]["name"];

                switch ($orderByField) {
                    case 'price':
                        $queryData->orderby = 'meta_value_num';
                        $queryData->meta_key = '_price';
                        break;
                    case 'total_sales':
                        $queryData->orderby = 'meta_value_num';
                        $queryData->meta_key = 'total_sales';
                        break;
                    case 'average_rating':
                        $queryData->orderby = 'meta_value_num';
                        $queryData->meta_key = '_wc_average_rating';
                        break;
                    case 'review_count':
                        $queryData->orderby = 'meta_value_num';
                        $queryData->meta_key = '_wc_review_count';
                        break;
                    case 'post_date_gmt':
                    case 'post_date':
                    case 'date_published':
                        $queryData->orderby = 'date';
                        break;
                    case 'product_id':
                    case 'ID':
                        $queryData->orderby = 'ID';
                        break;
                    case 'post_title':
                    case 'product_name':
                        $queryData->orderby = 'title';
                        break;
                    case 'post_author':
                        $queryData->orderby = 'author';
                        break;
                    default:
                        $queryData->orderby = $orderByField;
                        break;
                }
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
                            $productIdsQuery = new WP_Query(array_merge(
                                json_decode(json_encode($queryData), true),
                                array(
                                    'fields' => 'ids',
                                    'posts_per_page' => -1,
                                )
                            ));
                            $allProductIds = $productIdsQuery->have_posts() ? $productIdsQuery->posts : array();
                            $matchedIds = array_filter($allProductIds, fn($productID) => strpos((string)$productID, $searchValue) !== false);
                            $queryData->post__in = !empty($matchedIds) ? $matchedIds : array(0);
                        }
                        break;
                    case 'sku':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $queryData->meta_query[] = array(
                                'key' => '_sku',
                                'value' => $searchValue,
                                'compare' => '='
                            );
                        } else {
                            $queryData->meta_query[] = array(
                                'key' => '_sku',
                                'value' => $searchValue,
                                'compare' => 'LIKE'
                            );
                        }
                        break;
                    case 'tags':
                        $productTag = get_term_by('name', $searchValue, 'product_tag');
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $queryData->tax_query[] = array(
                                'taxonomy' => 'product_tag',
                                'field' => 'term_id',
                                'terms' => $productTag ? $productTag->term_id : 0
                            );
                        } else {
                            $allTags = get_terms(array(
                                'taxonomy' => 'product_tag',
                                'hide_empty' => false,
                            ));
                            $matchedTags = array_filter($allTags, fn($tag) => strpos($tag->name, $searchValue) !== false);
                            $queryData->tax_query[] = array(
                                'taxonomy' => 'product_tag',
                                'field' => 'term_id',
                                'terms' => array_column($matchedTags, 'term_id')
                            );
                        }
                        break;
                    case 'price':
                        $priceRange = explode('|', $searchValue);
                        $startPrice = isset($priceRange[0]) && $priceRange[0] !== '' ? floatval($priceRange[0]) : null;
                        $endPrice = isset($priceRange[1]) && $priceRange[1] !== '' ? floatval($priceRange[1]) : null;

                        if ($startPrice !== null && $endPrice !== null) {
                            $queryData->meta_query[] = array(
                                'key' => '_price',
                                'value' => array($startPrice, $endPrice),
                                'compare' => 'BETWEEN',
                                'type' => 'NUMERIC'
                            );
                        } elseif ($startPrice !== null) {
                            $queryData->meta_query[] = array(
                                'key' => '_price',
                                'value' => $startPrice,
                                'compare' => '>=',
                                'type' => 'NUMERIC'
                            );
                        } elseif ($endPrice !== null) {
                            $queryData->meta_query[] = array(
                                'key' => '_price',
                                'value' => $endPrice,
                                'compare' => '<=',
                                'type' => 'NUMERIC'
                            );
                        }
                        break;
                    case 'stock_status':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $queryData->meta_query[] = array(
                                'key' => '_stock_status',
                                'value' => $searchValue,
                                'compare' => '='
                            );
                        } else {
                            $queryData->meta_query[] = array(
                                'key' => '_stock_status',
                                'value' => $searchValue,
                                'compare' => 'LIKE'
                            );
                        }
                        break;
                    case 'product_name':
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $matchedPostIds = self::getPostIdsByExactTitle($searchValue);

                        } else {
                            $matchedPostIds = self::getPostIdsByTitle($searchValue);
                        }
                        if (!empty($matchedPostIds)) {
                            $queryData->post__in = $matchedPostIds;
                        } else {
                            $queryData->post__in = array(0);
                        }
                        break;
                    case 'dimensions':
                        // Check for the presence of either '&times;' (selectbox) or ' × '(text) and split accordingly
                        $dimensionUnit = get_option('woocommerce_dimension_unit');

                        if (strpos($searchValue, ' &times; ') !== false) {
                            $dimensionsArray = explode(' &times; ', str_replace(' ' . $dimensionUnit, '', $searchValue));
                        } else {
                            $dimensionsArray = explode(' × ', str_replace(' ' . $dimensionUnit, '', $searchValue));
                        }
                        $length = isset($dimensionsArray[0]) ? trim($dimensionsArray[0]) : null;
                        $width = isset($dimensionsArray[1]) ? trim($dimensionsArray[1]) : null;
                        $height = isset($dimensionsArray[2]) ? trim($dimensionsArray[2]) : null;

                        if (!isset($queryData->meta_query)) {
                            $queryData->meta_query = array();
                        }
                        if ($length !== null) {
                            $queryData->meta_query[] = array(
                                'key' => '_length',
                                'value' => floatval($length),
                                'compare' => '=',
                                'type' => 'NUMERIC'
                            );
                        }
                        if ($width !== null) {
                            $queryData->meta_query[] = array(
                                'key' => '_width',
                                'value' => floatval($width),
                                'compare' => '=',
                                'type' => 'NUMERIC'
                            );
                        }
                        if ($height !== null) {
                            $queryData->meta_query[] = array(
                                'key' => '_height',
                                'value' => floatval($height),
                                'compare' => '=',
                                'type' => 'NUMERIC'
                            );
                        }
                        break;
                    case 'average_rating':
                        if (is_string($searchValue)) {
                            if (strpos($searchValue, '|') !== false) {
                                $ratingRange = explode('|', $searchValue);
                                $startRating = isset($ratingRange[0]) && $ratingRange[0] ? floatval($ratingRange[0]) : null;
                                $endRating = isset($ratingRange[1]) && $ratingRange[1] ? floatval($ratingRange[1]) : null;

                                if ($startRating !== null && $endRating !== null) {
                                    $queryData->meta_query[] = array(
                                        'key' => '_wc_average_rating',
                                        'value' => array($startRating, $endRating),
                                        'type' => 'DECIMAL',
                                        'compare' => 'BETWEEN',
                                    );
                                } elseif ($startRating !== null) {
                                    $queryData->meta_query[] = array(
                                        'key' => '_price',
                                        'value' => $startRating,
                                        'compare' => '>=',
                                        'type' => 'NUMERIC'
                                    );
                                } elseif ($endRating !== null) {
                                    $queryData->meta_query[] = array(
                                        'key' => '_price',
                                        'value' => $endRating,
                                        'compare' => '<=',
                                        'type' => 'NUMERIC'
                                    );
                                }
                            } else {
                                $specificRating = floatval($searchValue);
                                if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                                    $queryData->meta_query[] = array(
                                        'key' => '_wc_average_rating',
                                        'value' => $specificRating,
                                        'type' => 'DECIMAL',
                                        'compare' => '='
                                    );
                                } else {
                                    $queryData->meta_query[] = array(
                                        'key' => '_wc_average_rating',
                                        'value' => array($specificRating, ceil($specificRating) == $specificRating ?
                                            floor($specificRating + 1) : ceil($specificRating)),
                                        'type' => 'DECIMAL',
                                        'compare' => 'BETWEEN',
                                    );
                                }
                            }
                        }
                        break;
                    case 'date_published':
                        $dateRange = explode('|', $searchValue);
                        if (count($dateRange) === 2 && $searchValue !== "|") {
                            $startTimestamp = is_numeric($dateRange[0]) ? $dateRange[0] : strtotime(str_replace('/', '-', $dateRange[0]));
                            $endTimestamp = is_numeric($dateRange[1]) ? $dateRange[1] : strtotime(str_replace('/', '-', $dateRange[1]));
                            $start_date = date('Y-m-d H:i:s', $startTimestamp);
                            $end_date = date('Y-m-d H:i:s', $endTimestamp);

                            if ($startTimestamp && $endTimestamp) {
                                $queryData->date_query = array(
                                    array(
                                        'column' => $columnName,
                                        'after' => $start_date,
                                        'before' => $end_date,
                                        'inclusive' => true,
                                    ),
                                );
                            } elseif ($startTimestamp) {
                                $queryData->date_query = array(
                                    array(
                                        'column' => $columnName,
                                        'after' => $start_date,
                                        'inclusive' => true,
                                    ),
                                );
                            } else if ($endTimestamp) {
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
                    case 'short_description':
                        if (isset($searchValue) && $searchValue !== '') {
                            add_filter('posts_where', function ($where) use ($searchValue, $wdtParameters, $columnName) {
                                global $wpdb;
                                if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                                    $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_excerpt = %s", $searchValue);
                                } else {
                                    $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_excerpt LIKE %s", '%' . $wpdb->esc_like($searchValue) . '%');
                                }
                                return $where;
                            });
                        }
                        break;
                    case 'categories':
                        $productCategory = get_term_by('name', $searchValue, 'product_cat');
                        if ($wdtParameters["exactFiltering"][$columnName] === 1) {
                            $queryData->tax_query[] = array(
                                'taxonomy' => 'product_cat',
                                'field' => 'term_id',
                                'terms' => $productCategory ? $productCategory->term_id : 0
                            );
                        } else {
                            $allCategories = get_terms(array(
                                'taxonomy' => 'product_cat',
                                'hide_empty' => false,
                            ));
                            $matchedCategories = array_filter($allCategories, fn($category) => strpos($category->name, $searchValue) !== false);
                            $queryData->tax_query[] = array(
                                'taxonomy' => 'product_cat',
                                'field' => 'term_id',
                                'terms' => array_column($matchedCategories, 'term_id')
                            );
                        }
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
        $tableArray = self::getWooCommerceTableColumns($query);

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
     * @param WP_Query $query
     * @return array|void
     */
    public static function getWooCommerceTableColumns(WP_Query $query)
    {
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $product;
                global $post;

                // Get product data
                $productId = $product->get_id();
                $productName = get_the_title();
                $sku = $product->get_sku();
                $price = self::getProductPrice($product);
                $stockStatus = $product->get_stock_status();
                $totalSales = get_post_meta($productId, 'total_sales', true);
                $categories = wc_get_product_category_list($productId);
                $tags = wc_get_product_tag_list($productId);
                $dimensions = $product->has_dimensions() ? wc_format_dimensions($product->get_dimensions(false)) : null;
                $averageRating = $product->get_average_rating();
                $reviewCount = $product->get_review_count();
                $featuredImage = "<img alt='" . $productName . "' src='" . get_the_post_thumbnail_url($productId, 'thumbnail') . "'>";
                $datePublished = get_the_date('Y-m-d', $productId);
                $shortDescription = apply_filters('woocommerce_short_description', $post->post_excerpt);
                $addToCartButton = self::getAddToCartButton($product, $productId, $productName);

                $productData = array(
                    'select' => '<input type="checkbox" class="select-checkbox">',
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'sku' => $sku,
                    'price' => $price,
                    'stock_status' => $stockStatus,
                    'total_sales' => $totalSales,
                    'categories' => $categories,
                    'tags' => $tags,
                    'dimensions' => $dimensions,
                    'average_rating' => $averageRating,
                    'review_count' => $reviewCount,
                    'featured_image' => $featuredImage,
                    'date_published' => $datePublished,
                    'short_description' => $shortDescription,
                    'add_to_cart_button' => $addToCartButton
                );

                $productData = apply_filters('wpdatatables_woo_product_data', $productData, $post);

                $productTableColumns[] = $productData;
            }
            wp_reset_postdata();

            return apply_filters('wpdatatables_before_create_woo_commerce_columns', $productTableColumns);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function addProductsToCart()
    {
        if (isset($_POST['productIds']) && is_array($_POST['productIds'])) {
            $productIds = array_map('intval', $_POST['productIds']);
            $productQuantities = [];

            foreach ($productIds as $productId) {
                do_action('wpdatatables_before_add_product_to_cart', $productId);
                WC()->cart->add_to_cart($productId);
                // Get the quantity of the specific product in the cart
                $productQuantities[$productId] = WC()->cart->get_cart_item_quantities()[$productId];
            }

            $cartUrl = wc_get_cart_url();
            $cartContentsCount = WC()->cart->get_cart_contents_count();
            $cartTotal = WC()->cart->get_cart_total();

            wp_send_json_success([
                'cart_url' => $cartUrl,
                'cart_contents_count' => $cartContentsCount,
                'cart_total' => $cartTotal,
                'product_quantities' => $productQuantities
            ]);
        } else {
            wp_send_json_error();
        }
    }

    /**
     * @throws Exception
     */
    public static function addSingleProductToCart()
    {
        $productId = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        $variations = isset($_POST['variations']) ? self::sanitizeArray($_POST['variations']) : null;

        // Check if product exists and is valid
        if ($productId && $quantity > 0) {
            $product = wc_get_product($productId);

            if ($product->is_type('variable')) {
                $WC_Product_Data_Store_CPT = new WC_Product_Data_Store_CPT();
                $variationId = $WC_Product_Data_Store_CPT->find_matching_product_variation($product, $variations);
                do_action('wpdatatables_before_add_product_to_cart', $variationId);
                if ($variationId && WC()->cart->add_to_cart($variationId, $quantity)) {
                    $cartUrl = wc_get_cart_url();
                    $cartContentsCount = WC()->cart->get_cart_contents_count();
                    $cartTotal = WC()->cart->get_cart_total();
                    $productQuantities = isset(WC()->cart->get_cart_item_quantities()[$variationId]) ? WC()->cart->get_cart_item_quantities()[$variationId] : WC()->cart->get_cart_item_quantities()[$productId];

                    wp_send_json_success([
                        'cart_url' => $cartUrl,
                        'cart_contents_count' => $cartContentsCount,
                        'cart_total' => $cartTotal,
                        'product_quantities' => $productQuantities
                    ]);
                } else if ($variationId) {
                    // Could not add to cart
                    wp_send_json_success([
                        'notAdded' => true
                    ]);
                } else {
                    wp_send_json_error(['error' => __('Invalid variations selected.', 'wpdatatables')]);
                }
            } else {
                do_action('wpdatatables_before_add_product_to_cart', $productId);
                if (!(WC()->cart->add_to_cart($productId, $quantity))) {
                    // Could not add to cart
                    wp_send_json_success([
                        'notAdded' => true
                    ]);
                }
            }
            $cartUrl = wc_get_cart_url();
            $cartContentsCount = WC()->cart->get_cart_contents_count();
            $cartTotal = WC()->cart->get_cart_total();
            $productQuantities = WC()->cart->get_cart_item_quantities()[$productId];

            wp_send_json_success([
                'cart_url' => $cartUrl,
                'cart_contents_count' => $cartContentsCount,
                'cart_total' => $cartTotal,
                'product_quantities' => $productQuantities
            ]);
        } else {
            wp_send_json_error(['error' => __('Invalid product or quantity.', 'wpdatatables')]);
        }
    }

    /**
     * @param $product
     * @return string
     */
    public static function getProductPrice($product): string
    {
        if ($product->is_type('variable')) {
            // For variable products, get the minimum and maximum prices
            $prices = $product->get_variation_prices();
            $minPrice = current($prices['price']);
            $maxPrice = end($prices['price']);

            if ($minPrice !== $maxPrice) {
                $priceHtml = wc_price($minPrice) . ' - ' . wc_price($maxPrice);
            } else {
                $priceHtml = wc_price($minPrice);
            }
        } else {
            // For simple products, get either the sale and regular price, or just the regular price
            $regularPrice = $product->get_regular_price();
            $salePrice = $product->get_sale_price();

            if ($product->is_on_sale() && $salePrice) {
                $priceHtml = '<del>' . wc_price($regularPrice) . '</del> <ins>' . wc_price($salePrice) . '</ins>';
            } else {
                $priceHtml = wc_price($regularPrice);
            }
        }

        return $priceHtml;
    }

    /**
     * @return void
     */
    public static function getCartInfo()
    {
        if (WC()->cart) {
            $itemCount = WC()->cart->get_cart_contents_count();
            $totalSum = WC()->cart->get_cart_total();

            wp_send_json_success(array(
                'item_count' => $itemCount,
                'total_sum' => $totalSum,
            ));
        } else {
            wp_send_json_error();
        }
    }

    /**
     * @param $product
     * @param $productId
     * @param $productName
     * @return string
     */
    public static function getAddToCartButton($product, $productId, $productName): string
    {
        if ($product->is_type('variable')) {
            $attributes = $product->get_variation_attributes();
            $addToCartButton = '<div class="wdt-woo-variable-product">';

            foreach ($attributes as $attributeName => $options) {
                $addToCartButton .= '<select class="wdt-woo-product-attribute" data-attribute_name="' . esc_attr($attributeName) . '">';
                $addToCartButton .= '<option value="">' . __('Choose an option', 'wpdatatables') . '</option>';
                foreach ($options as $option) {
                    $addToCartButton .= '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                }
                $addToCartButton .= '</select>';
            }

            // Quantity input
            $addToCartButton .= '<input type="number" class="wdt-woo-product-quantity" min="1" value="1" />';

            // Disabled Add to Cart button
            $addToCartButton .= '<button disabled data-product_id="' . $productId . '" 
                            class="single_add_to_cart_button button alt ajax_add_to_cart"
                            aria-label="' . $productName . '">
                        ' . __('Add to cart', 'wpdatatables') . '
                    </button>';
            $addToCartButton .= '</div>';
        } else {
            $addToCartButton = '<button data-product_id="' . $productId . '" 
                            class="single_add_to_cart_button button alt ajax_add_to_cart"
                            aria-label="' . $productName . '">
                        ' . __('Add to cart', 'wpdatatables') . '
                    </button>';
        }
        return apply_filters('wpdatatables_before_render_add_to_cart', $addToCartButton);
    }

    /**
     * @param $cellOutput
     * @param $tableId
     * @param $columnName
     * @return mixed|string
     * @throws Exception
     */
    public static function allowHtmlInColumn($cellOutput, $tableId, $columnName)
    {
        $tableType = WDTConfigController::loadTableFromDB($tableId)->table_type;

        if ($tableType === 'woo_commerce' && $columnName === 'add_to_cart_button') {
            // Extract product ID from the cell output
            $productId = self::extractProductId($cellOutput);

            if ($productId) {
                $product = wc_get_product($productId);

                // Initialize HTML output
                $htmlOutput = '<div class="wdt-woo-variable-product">';

                if ($product->is_type('variable')) {
                    // Variable product: Get available attributes
                    $attributes = $product->get_variation_attributes();
                    // Get default attribute values
                    $defaultAttributes = $product->get_default_attributes();

                    // Loop through attributes and create dropdowns
                    foreach ($attributes as $attributeName => $options) {
                        $sanitizedAttrName = sanitize_title($attributeName);

                        // Get default value for this attribute, if set
                        $default_value = isset($defaultAttributes[$sanitizedAttrName]) ? $defaultAttributes[$sanitizedAttrName] : '';

                        $htmlOutput .= '<label for="' . $sanitizedAttrName . '">' . wc_attribute_label($attributeName) . '</label>';
                        $htmlOutput .= '<select name="attribute_' . $sanitizedAttrName . '" id="' . $sanitizedAttrName . '" class="wdt-woo-variation-selector" data-attribute-name="' . $sanitizedAttrName . '">';
                        $htmlOutput .= '<option value="">' . __('Choose an option', 'wpdatatables') . '</option>';

                        foreach ($options as $option) {
                            // Check if the option is the default value and set it as selected
                            $selected = selected($default_value, $option, false);
                            $htmlOutput .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($option) . '</option>';
                        }

                        $htmlOutput .= '</select>';
                    }

                    // Quantity input for variable products
                    $htmlOutput .= '<div class="wdt-woo-quantity-field">
                                <label for="quantity_' . $productId . '">' . __('Quantity', 'wpdatatables') . '</label>
                                <input type="number" name="quantity" id="quantity_' . $productId . '" value="1" min="1" class="wdt-woo-input-text" />
                             </div>';

                    // Disabled Add to Cart button (will be enabled once all variations are selected)
                    $htmlOutput .= '<button disabled class="single_add_to_cart_button button alt ajax_add_to_cart" data-product_id="' . esc_attr($productId) . '" data-value="' . esc_attr($tableId) . '">' . __('Add to cart', 'wpdatatables') . '</button>';
                } else {
                    // Single product: Add quantity input and enabled Add to Cart button
                    $htmlOutput .= '<div class="wdt-woo-quantity-field">
                                <label for="quantity_' . $productId . '">' . __('Quantity', 'wpdatatables') . '</label>
                                <input type="number" name="quantity" id="quantity_' . $productId . '" value="1" min="1" class="wdt-woo-input-text" />
                             </div>';
                    $htmlOutput .= '<button class="single_add_to_cart_button button alt ajax_add_to_cart" data-product_id="' . esc_attr($productId) . '" data-value="' . esc_attr($tableId) . '">' . __('Add to cart', 'wpdatatables') . '</button>';
                }

                $htmlOutput .= '</div>';

                return $htmlOutput;
            }
        }
        return $cellOutput;
    }


    /**
     * @param $cellOutput
     * @return false|string
     */
    public static function extractProductId($cellOutput)
    {
        if (preg_match('/data-product_id="(\d+)"/', $cellOutput, $matches)) {
            return $matches[1];
        }

        return false;
    }

    /**
     * @param $column
     * @param $filterByUserId
     * @param $tableData
     * @return array
     */
    public static function getPossibleWooCommerceValuesRead($column, $filterByUserId, $tableData = null): array
    {
        $parentTable = $column->getParentTable();
        $columnOrigHeader = $column->getOriginalHeader();

        $wpQueryParams = json_decode($parentTable->getTableContent(), true);
        $distValues = [];

        if ($posts = get_posts($wpQueryParams)) {
            foreach ($posts as $post) {
                $product = wc_get_product($post->ID);

                switch ($columnOrigHeader) {
                    case 'product_id':
                        $postValue = $product->get_id();
                        break;
                    case 'product_name':
                        $postValue = $product->get_name();
                        break;
                    case 'sku':
                        $postValue = $product->get_sku();
                        break;
                    case 'price':
                        $postValue = $product->get_price();
                        break;
                    case 'stock_status':
                        $postValue = $product->get_stock_status();
                        break;
                    case 'total_sales':
                        $postValue = $product->get_total_sales();
                        break;
                    case 'categories':
                        $postValue = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
                        break;
                    case 'tags':
                        $postValue = wp_get_post_terms($product->get_id(), 'product_tag', ['fields' => 'names']);
                        break;
                    case 'dimensions':
                        $postValue = $product->has_dimensions() ? wc_format_dimensions($product->get_dimensions(false)) : null;
                        break;
                    case 'average_rating':
                        $postValue = $product->get_average_rating();
                        break;
                    case 'review_count':
                        $postValue = $product->get_review_count();
                        break;
                    case 'featured_image':
                        $postValue = wp_get_attachment_url($product->get_image_id());
                        break;
                    case 'date_published':
                        $postValue = get_the_date(get_option('wdtDateFormat'), $product->get_id());
                        break;
                    case 'short_description':
                        $postValue = $product->get_short_description();
                        break;
                    default:
                        $postValue = '';
                        break;
                }

                if (is_array($postValue)) {
                    foreach ($postValue as $value) {
                        if (!in_array($value, $distValues)) {
                            $distValues[] = $value;
                        }
                    }
                } else {
                    if ((!empty($postValue) || $postValue === "0") && !in_array($postValue, $distValues)) {
                        $distValues[] = $postValue;
                    }
                }
            }
        }

        return array_values(array_filter($distValues, function ($value) {
            return ($value !== null && $value !== false && $value !== '');
        }));

    }

    public static function wdtCheckIfWooIsInstalled()
    {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wdtNonce'], 'wdtConstructorNonce')) {
            exit();
        }

        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        if (is_plugin_active('woocommerce/woocommerce.php')) {
            $data = array(
                'wooExists' => true
            );

            echo json_encode($data);
            exit();
        }
        $data = array(
            'wooExists' => false,
            'responseText' => __('Please install and activate the WooCommerce plugin to use this table type.', 'wpdatatables')
        );

        echo json_encode($data);
        exit();
    }
}

WooCommerceIntegration::init();