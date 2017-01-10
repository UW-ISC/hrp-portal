<?php get_header(); ?>

<?php include(TEMPLATEPATH."/l_sidebar.php");?>

<div id="region-content" class="span-18 last">
  <div id="informationStory" class="span-11 append-1">
  
	<div class="title"><h2><a href="#" title="Your homepage"><?php bloginfo('name'); ?></a></h2></div>
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<h3><?php the_time('M'); ?></h3>
		<h4><?php the_time('j'); ?></h4>
	</div>
		
		<h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
		<p>Posted under <?php the_category(', ') ?> by <a href="mailto:<?php the_author_email(); ?>"><?php the_author_firstname(); ?> <?php the_author_lastname(); ?></a>&nbsp;<?php edit_post_link('(Edit Post)', '', ''); ?></p>
		</div>
		<?php the_content(__('Read more'));?>
		<!--
		<?php trackback_rdf(); ?>
		-->
	
		<?php endwhile; else: ?>
		
		<p><?php _e('Sorry, no posts matched your criteria.'); ?></p><?php endif; ?>
	
		<h1>Comments</h1>
		<?php comments_template(); // Get wp-comments.php template ?>

</div>
	
<?php include(TEMPLATEPATH."/r_sidebar.php");?>

<!-- The main column ends  -->

<?php get_footer(); ?>