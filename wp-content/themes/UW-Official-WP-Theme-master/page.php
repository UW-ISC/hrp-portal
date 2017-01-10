<?php get_header(); ?>

<?php include(TEMPLATEPATH."/l_sidebar.php");?>

<div id="region-content" class="span-18 last">
  <div id="informationStory" class="span-11 append-1">
  
	<div class="title"><h2><a href="#" title="Your homepage"><?php bloginfo('name'); ?></a></h2></div>
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<h1><?php the_title(); ?></h1>
	<?php the_content(__('Read more'));?>
	<!--
	<?php trackback_rdf(); ?>
	-->

	<?php endwhile; else: ?>
	<p><?php _e('Sorry, no posts matched your criteria.'); ?></p><?php endif; ?>
	

</div>
	
<?php include(TEMPLATEPATH."/r_sidebar.php");?>



<!-- The main column ends  -->

<?php get_footer(); ?>



