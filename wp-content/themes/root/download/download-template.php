<!DOCTYPE html>
<html lang="en-US" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="University of Washington Human Resources">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/assets/ie/js/html5shiv.min.js" type="text/javascript"></script>
        <link rel="stylesheet"" href="<?php echo get_template_directory_uri(); ?>/assets/ie/css/ie.min.css" type="text/css" media="all" data-norem />
    <![endif]-->

    <title>UW Human Resources</title>

    <link rel="stylesheet" id="open-sans-css" href="https://fonts.googleapis.com/css?family=Open+Sans%3A300italic%2C400italic%2C600italic%2C300%2C400%2C600&#038;subset=latin%2Clatin-ext&#038;ver=4.4.2" type="text/css" media="all" />
    <link rel="stylesheet" id="google-font-open-css" href="https://fonts.googleapis.com/css?family=Open+Sans%3A400italic%2C700italic%2C400%2C700&#038;ver=3.6" type="text/css" media="all" />
    <link rel="stylesheet" id="main-css" href="<?php echo get_template_directory_uri(); ?>/assets/main.min.css" type="text/css" media="all" />
</head>

<body>

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

<main id="mainContent" tabindex="-1">
    <section class="uwhr-hero hero-xs hero-branded"></section>
    <section class="uwhr-body container">
        <div class="row">
            <article class="uwhr-content col-lg-12">

            </article>
        </div>
    </section>
<?php get_footer(); ?>
