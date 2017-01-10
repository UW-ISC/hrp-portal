<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta name="distribution" content="global" />
<meta name="robots" content="follow, all" />
<meta name="language" content="en, sv" />

<title><?php wp_title(''); ?><?php if(wp_title('', false)) { echo ' :'; } ?><?php bloginfo('name'); ?> - washington.edu</title>
<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" />
<!-- leave this for stats please -->
        
        
<!-- Framework CSS -->
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/css/screen.css" type="text/css" media="screen, projection" />
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/css/print.css" type="text/css" media="print" />
    
<!--Header-->
<link href="<?php bloginfo('template_url'); ?>/css/reset.css" rel="stylesheet" type="text/css" />
<link href="<?php bloginfo('template_url'); ?>/css/footer.css" rel="stylesheet" type="text/css" />
<link href="<?php bloginfo('template_url'); ?>/css/typography.css" rel="stylesheet" type="text/css" />    
<link href="<?php bloginfo('template_url'); ?>/css/header.css" rel="stylesheet" type="text/css" />
<link href="<?php bloginfo('template_url'); ?>/css/secondary.css" rel="stylesheet" type="text/css" />
	
<!-- Tile Popup -->
    
<!-- The line below starts the conditional comment -->
<!--[if IE]>
<style type="text/css">
  body{ behavior:url("<?php bloginfo('template_directory'); ?>csshover.htc"); }
</style>
<![endif]--> <!-- This ends the conditional comment -->
    
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php wp_get_archives('type=monthly&format=link'); ?>
<?php wp_head(); ?>   
    
<style type="text/css" media="screen">
<!-- @import url( <?php bloginfo('stylesheet_url'); ?> ); -->
</style>

  </head>
  <body>
  
  <?php include(TEMPLATEPATH."/topnav.php");?>

<div class="container documentContent" id="pageBackground">        
 <div id="bgSliceMiddle">
<?php include(TEMPLATEPATH."/banner.php");?>
