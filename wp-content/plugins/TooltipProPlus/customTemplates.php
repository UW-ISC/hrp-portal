<?php

class CMTT_Custom_Templates
{

    public static function init()
    {
        add_filter('template_include', array(__CLASS__, 'overrideTemplate'), PHP_INT_MAX);
    }

    /**
     * Return the list of template options
     * @return type
     */
    public static function getPageTemplatesOptions()
    {
        $theme = wp_get_theme();
        $templates = (array) $theme->get_page_templates();

        $result = array(
            0 => CMTT_NAME . ' - default',
        );

        $pageTemplate = locate_template('page.php', false, false);
        if( $pageTemplate )
        {
            $result['page.php'] = 'Theme\'s page.php';
        }

//        $pageTemplate = locate_template('single.php', false, false);
//        if( $pageTemplate )
//        {
//            $result['single.php'] = 'Theme\'s single.php';
//        }

        return array_merge($result, $templates);
    }

    /**
     * Return the custom template
     * @param type $template
     * @return type
     */
    public static function getCustomTemplate($template)
    {
        $available = self::getPageTemplatesOptions();
        if( isset($available[$template]) )
        {
            return $template;
        }
    }

    /**
     *
     * @global type $wp_query
     * @global type $post
     * @param type $template
     * @return type
     */
    public static function overrideTemplate($template)
    {
        if( get_query_var('post_type') == 'glossary' && is_single() )
        {
            $option = get_option('cmtt_glossaryPageTermTemplate', 0);
            $name = self::getCustomTemplate($option);

            if( $name )
            {
                $template = locate_template(array($name, 'page.php', 'single.php'), false, false);
                add_filter('body_class', array(__CLASS__, 'pageBodyClass'), 20);
            }
        }

        return $template;
    }

    static function pageBodyClass($classes)
    {
        if( get_query_var('post_type') == 'glossary' && is_single() )
        {
            $option = get_option('cmtt_glossaryPageTermTemplate', 0);
            $template = self::getCustomTemplate($option);

            $classes[] = 'page';
            $classes[] = 'page-template';
            $classes[] = 'page-template-' . sanitize_html_class(str_replace('.', '-', $template));
            if( stripos($template, 'full-width') !== false )
            {
                $classes[] = 'full-width';
            }
        }
        return $classes;
    }

}