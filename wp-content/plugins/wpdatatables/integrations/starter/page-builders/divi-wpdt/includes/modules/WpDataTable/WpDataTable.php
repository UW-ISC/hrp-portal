<?php


class DIVI_wpDataTable extends ET_Builder_Module
{

    public $slug = 'DIVI_wpDataTable';
    public $vb_support = 'on';

    private $_allTables;

    /**
     * @return mixed
     */
    public function getAllTables()
    {
        return $this->_allTables;
    }

    /**
     * @param mixed $allTables
     */
    public function setAllTables($allTables)
    {
        $this->_allTables = $allTables;
    }

    protected $module_credits = array(
        'module_uri' => '',
        'author' => '',
        'author_uri' => '',
    );

    public function init()
    {
        $this->name = esc_html__('wpDataTable', 'wpdatatables');
        $this->setAllTables(WDTConfigController::getAllTablesAndChartsForPageBuilders('divi', 'tables'));
        if (defined('WDT_WOO_COMMERCE_INTEGRATION')) {
            add_action('wp_enqueue_scripts', array('Divi_Wpdt_Shortcode_Helper', 'enqueue_woo_commerce_divi_script'));
        }
    }

    public static function enqueueCustomDiviJs()
    {
        Divi_Wpdt_Shortcode_Helper::enqueue_woo_commerce_divi_script();
    }

    /**
     * Advanced Fields Config
     *
     * @return array
     */
    public function get_advanced_fields_config()
    {
        return array(
            'button' => false,
            'link_options' => false
        );
    }

    public function get_fields()
    {
        return array(
            'id' => array(
                'label' => __('Choose a wpDataTable', 'wpdatatables'),
                'type' => 'select',
                'default_on_front' => $this->getAllTables()[0],
                'options' => $this->getAllTables(),
                'toggle_slug' => 'main_content',
                'computed_affects' => array(
                    '__view' => array(),
                ),
            ),
            'view' => array(
                'label' => __('Choose table view', 'wpdatatables'),
                'type' => 'select',
                'default_on_front' => __('regular', 'wpdatatables'),
                'options' => array(
                    'regular' => __('Regular', 'wpdatatables'),
                    'excel-like' => __('Excel-like', 'wpdatatables'),
                ),
                'toggle_slug' => 'main_content',
                'custom_class' => 'wpdt-view-dropdown-field'
            ),
            'var1' => array(
                'label' => __('Insert the %VAR1% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var2' => array(
                'label' => __('Insert the %VAR2% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var3' => array(
                'label' => __('Insert the %VAR3% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var4' => array(
                'label' => __('Insert the %VAR4% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var5' => array(
                'label' => __('Insert the %VAR5% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var6' => array(
                'label' => __('Insert the %VAR6% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var7' => array(
                'label' => __('Insert the %VAR7% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var8' => array(
                'label' => __('Insert the %VAR8% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'var9' => array(
                'label' => __('Insert the %VAR9% placeholder', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'export_file_name' => array(
                'label' => __('Set the name for the export file', 'wpdatatables'),
                'type' => 'text',
                'default_on_front' => ''
            ),
            'table_array_length' => array(
                'type' => 'text',
                'default_on_front' => count($this->getAllTables()),
                'show_if' => array(
                    'id' => -1
                )
            )
        );
    }

    protected function is_not_woocommerce_table()
    {
        $wooCommerceTableIds = array('330', '331'); // WooCommerce table IDs
        $selectedTableId = $this->props['id'] ?? null;

        return !in_array($selectedTableId, $wooCommerceTableIds);
    }

    public function render($attrs, $content = null, $render_slug = null)
    {
        return Divi_Wpdt_Shortcode_Helper::render_wpdatatable_from_props( $this->props );
    }
}

new DIVI_wpDataTable;