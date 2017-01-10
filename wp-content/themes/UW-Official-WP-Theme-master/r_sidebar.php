<!-- begin r_sidebar -->
<div id="rightInfo" class="span-6 last">
 <div class="leftNavBlocks">
  <form method="get" action="<?php bloginfo('url'); ?>/">
   <div><h2><label for="s" class="screen-reader-text">Search for:</label></h2>
    <input type="text" id="s" name="s" value="" />
    <input type="submit" value="Search" id="searchsubmit" />
   </div>
  </form>

<ul>
<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
<?php endif; ?>
</ul>

 </div>
</div>                    
<!-- end r_sidebar -->