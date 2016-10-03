<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?php wp_title(' | ',TRUE,'right'); bloginfo('name'); ?></title>
    <meta name="description" content="<?php bloginfo('description', 'display'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<!--[if lt IE 9]>
        <script src="<?php bloginfo("template_directory"); ?>/assets/ie/js/html5shiv.min.js" type="text/javascript"></script>
        <link rel="stylesheet"" href="<?php bloginfo("template_directory"); ?>/assets/ie/css/ie.min.css" type="text/css" media="all" data-norem />
    <![endif]-->

    <?php wp_head(); ?>

</head>

<body <?php body_class(); ?> >

<div class="uwhr-wrap">

<a href="#mainContent" class="sr-only sr-only-focusable">Skip to main content</a>

<?php $menus = uwhr_get_header_menus(); ?>

<header class="uwhr-headers" id="top">
    <div class="uwhr-global-header">
        <div class="row">
            <div class="col-xs-12 uwhr-global-header-wrapper">
                <div class="pull-right">
                    <?php echo $menus['global']; ?>
                    <?php global $UWHR; $UWHR->Search->UI->render_search_form( 'slim' ); ?>
               </div>
           </div>
       </div>
   </div>

    <div class="uwhr-header">
        <div class="row">
            <div class="col-xs-12">
                <h1 class="text-hide">University of Washington Human Resources</h1>
                <a class="uwhr-logo text-hide" href="<?php echo network_site_url(); ?>" title="University of Washington Human Resources Home">University of Washington Human Resources Home</a>

                <?php
                    echo $menus['dropdown'];
                    echo $menus['mobile-btn'];
                ?>
            </div>
        </div>
    </div>

    <?php echo $menus['mobile']; ?>

</header>

<main id="mainContent" tabindex="-1" role="main">
