<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Plugin\Widgets;

use WP_Widget;
use WPDataTables\Plugin\Plugin;
use WPDataTables\Rendering\FilterRenderer;

/**
 * wpDataTables filtering widget.
 *
 * WordPress requires widgets to subclass WP_Widget, so this stays an
 * inheritance edge; the render is delegated to
 * {@see FilterRenderer::renderWidget()} (pulled from the container at the WP
 * edge — service-locator only here, as widgets cannot be constructor-injected).
 *
 * The legacy `wdtFilterWidget` class is now a thin subclass of this one. It is
 * still the class registered with WordPress (`register_widget('wdtFilterWidget')`
 * in {@see \WPDataTables\Plugin\FrontendHooks}), which keeps the widget's
 * `id_base` exactly `wdtfilterwidget` — `WP_Widget::__construct(false, ...)`
 * derives the base from `get_class($this)`, i.e. the concrete instance class —
 * so existing placed widgets are unaffected.
 *
 * @package WPDataTables\Plugin\Widgets
 */
class FilterWidget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(false, 'wpDataTables filtering widget');
    }

    function widget($args, $instance)
    {
        // Widget output
        if (!isset($instance['title'])) {
            $title = esc_html__('Filter', 'wpdatatables');
        } else {
            $title = $instance['title'];
        }
        $title = apply_filters('widget_title', $title);

        echo $args['before_widget'];

        /** @noinspection PhpUnusedLocalVariableInspection */
        $title = $args['before_title'] . $title . $args['after_title'];

        echo Plugin::container()->get(FilterRenderer::class)->renderWidget($title);
        echo $args['after_widget'];
    }

    function form($instance)
    {
        // Output admin widget options form
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = esc_html__('New title', 'text_domain');
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     * @see WP_Widget::update()
     *
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        return $instance;
    }

}
