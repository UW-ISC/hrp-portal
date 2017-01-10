<?php get_header(); ?>

<?php include(TEMPLATEPATH."/l_sidebar.php");?>

<div id="region-content" class="span-18 last">
  <div id="informationStory" class="span-11 append-1">
  
<!--	<div class="title"><h2><a href="#" title="Your homepage"><?php bloginfo('name'); ?></a></h2></div>
-->
        <div id="categorytitle">
	<a href="<?php the_permalink() ?>" rel="bookmark"><?php the_category(', ') ?></a>
	</div>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<div class="contentdate">
		<h3><?php the_time('M'); ?></h3>
		<h4><?php the_time('j'); ?></h4>
	</div>
	
	<div class="contenttitle">
		<h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
		<p>Posted under <?php the_category(', ') ?> by <a href="mailto:<?php the_author_email(); ?>"><?php the_author_firstname(); ?> <?php the_author_lastname(); ?></a>&nbsp;<?php edit_post_link('(Edit Post)', '', ''); ?></p>
		</div>
		<?php the_content(__('Read more'));?>
		
		<div class="comment_link"><img src="<?php bloginfo('template_url'); ?>/images/comment_bubble.gif" width="13" height="13" alt="Comment bubble icon" /><?php comments_popup_link('Leave a comment', '1 Comment', '% Comments'); ?></div>
		
		<div class="postspace">
		</div>
		
		<!--
		<?php trackback_rdf(); ?>
		-->
	
		<?php endwhile; else: ?>
		
		<p><?php _e('Sorry, no posts matched your criteria.'); ?></p><?php endif; ?>
		<p><?php posts_nav_link(' &#8212; ', __('&larr; Previous Page'), __('Next Page &rarr;')); ?></p>
	
     </div>

	
<?php include(TEMPLATEPATH."/r_sidebar.php");?>



<!-- The main column ends  -->

<?php get_footer(); ?>






	



