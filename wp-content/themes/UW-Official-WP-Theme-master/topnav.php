<?php

include_once(ABSPATH.WPINC.'/feed.php'); // path to include script
$rss = fetch_feed('http://www.atmos.washington.edu/rss/home.rss'); // specify feed url
$weather = false;

if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly 
    
    // Figure out how many total items there are, but limit it to 3 
    $maxitems = $rss->get_item_quantity(3); 

    // Build an array of all the items, starting with element 0 (first element).
    $rss_items = $rss->get_items(0, $maxitems);
    
    // pull the titles out of the $rss_items object and build an array of titles
    foreach ( $rss_items as $item ) :
    
		$weatherItems[] = $item->get_title();
	
	endforeach;
    
    $tempInfo = explode('|',$weatherItems[0]);
    $temp = substr($tempInfo[1],1);

    $condInfo = explode('|',$weatherItems[1]);
    $cond = substr($condInfo[1],1);

    $iconInfo = explode('|',$weatherItems[2]);
    $icon = substr($iconInfo[1],1);
    
    if (!empty($cond) && $icon !== '00') {
    	// Set the weather to true
    	$weather = true;
    }
    
endif;

?>

<div class="wheader patchYes colorGold">	
  <div id="autoMargin">
  
    <div class="wlogoSmall">
      <div class="logoAbsolute"><a id="wlogoLink" href="http://www.washington.edu/">University of Washington</a></div>
	</div>
	<?php if ($weather == true) : ?>
	<div id="weather">
	  <a href="http://www.atmos.washington.edu/weather/forecast.shtml"><img src="http://www.washington.edu/static/image/weather/<?php echo $icon; ?>.png" width="32" height="32" alt="<?php echo $cond; ?>: <?php echo $temp; ?>" title="<?php echo $cond; ?>: <?php echo $temp; ?>" class="weather-icon" /></a>
	  <div>
	    <span class="weather-city"><a href="http://www.atmos.washington.edu/weather/forecast.shtml" title="Click for a detailed forecast">Seattle</a> <?php echo $temp; ?></span>
	  </div>
	</div>
	<?php endif; ?>
	<div id="wtext">
    	<ul>
      		<li><a href="http://www.washington.edu/">UW Home</a></li>
        	<li><span class="border"><a href="http://www.washington.edu/home/directories.html">Directories</a></span></li>
       	  	<li><span class="border"><a href="http://www.washington.edu/discover/visit/uw-events">Calendar</a></span></li>
            <li><span class="border"><a href="http://www.lib.washington.edu/">Libraries</a></span></li>
       	  	<li><span class="border"><a href="http://www.washington.edu/maps/">Maps</a></span></li>
       	  	<li><span class="border margRight"><a href="http://myuw.washington.edu/">My UW</a></span></li>
            <li><a href="http://www.uwb.edu/">UW Bothell</a></li>
       	  	<li><span class="border"><a href="http://www.tacoma.washington.edu/">UW Tacoma</a></span></li>
       </ul>
   </div>
    
  </div>
</div>

<div id="visual-portal-wrapper">
      <div id="bg">
        <div id="header">  
        
        <span id="uwLogo"><a href="http://www.washington.edu/">University of Washington</a></span>	
        
        <div id="wsearch">
                <form action="http://www.google.com/cse" id="searchbox_001967960132951597331:04hcho0_drk" name="uwglobalsearch">
                  <div class="wfield">
                    <input type="hidden" value="001967960132951597331:04hcho0_drk" name="cx" />
                    <input type="hidden" value="FORID:0" name="cof" />
                    <input type="text" class="wTextInput" value="Search the UW" title="Search the UW" name="q" />
                  </div>
                  <input type="submit" value="Go" name="sa" class="formbutton" />
                </form>
            </div>
        
        	
        	<p class="tagline"><a href="http://www.washington.edu/discovery/washingtonway/"><span class="taglineGold">Discover what's next.</span> It's the Washington Way.</a></p>
        	
        	<ul id="navg">

<?php uw_dropdowns(); ?>    	
    
        	</ul>        	       
  </div>
