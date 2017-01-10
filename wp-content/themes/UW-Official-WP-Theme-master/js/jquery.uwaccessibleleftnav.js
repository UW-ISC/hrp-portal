/*
 * UW Accessible Left Navigation
 * @author Dane Odekirk
 * @version 1.0
 *
 *
 * @desc: This allows keyboard functionality to the left accordion navigation.
 *
 * @options: classname -> The name of the class to be added when a user hits enter
 *           duration  -> The duration of the sliding up and down animation
 */

; (function($) {
    $.uwaccessible = $.uwaccessible || { };
	$.uwaccessible.leftnav = {
		options: {	
            classname : 'current_page_item', //the class that gets added or removed for tabbing 
            duration  : 200 //the slide up and down duration 
		},
        cache : []
	};	
	
	$.fn.uwaccessibleleftnav = function(options) { 
        var cache   = $.uwaccessible.leftnav.cache,
		    options = $.extend({}, $.uwaccessible.leftnav.options, options); 		

        return this.each(function(){ 
            
            $(this).delegate( 'li' , 'click keypress' , function( event ) {

                var $this  = $(this),
                    $ul    = $this.children( 'ul' ),
                    open   = $this.hasClass( options.classname ),
                    has_ul = ( $ul.length > 0 );

                switch ( event.type ) {

                    case 'click' : 

                        if ( !has_ul || open ) {
                            return true;   
                        }

                        if ( cache.length ) {
                            cache.slideUp( options.duration ).parent().removeClass( options.classname );
                        }

                        $this.addClass( options.classname );
                        $ul.slideDown( options.duration );

                        cache = $ul;

                        return false;

                    break;

                    case 'keypress':
                        // keycode 13 is Enter
                        if ( event.keyCode == 13 ) { 
                            $(this).trigger( 'click' );
                        }

                    break;

                }
                    

            });
            
        });
	};

}(jQuery));

/********************************************************
 
 xWMMMMMMMMMMMMMMMMWd     dWMMMMMX,     OMMMMMMMMMMMMWd 
 xWMMMMMMMMMMMMMMMMWd    .XMMMMMMWk.    OMMMMMMMMMMMMWd 
 l0KKNMMMMMMMMMWKKK0c    xWMMMMMMMN;    d0KKXWMMMMNKK0c 
     ,NMMMMMMMMX,       ,NMMMMMMMMMO.      .kWMMMN,     
     .xWMMMMMMMWk      .OMMMMMMMMMMN:      ,NMMMWx      
      .XMMMMMMMMN;     :NMMMMMMMMMMWO.    .OMMMMX.      
       dWMMMMMMMWO    .0MMMMMMMMMMMMN:    :NMMMWo       
       .XMMMMMMMMN;   oWMMMMMMMMMMMMM0.  .0MMMM0.       
        oWMMMMMMMMO  .XMMMWKNMMMMMMMMWc  oWMMMNc        
        .KMMMMMMMMN:.dWMMMN:lNMMMMMMMM0..KMMMM0.        
         lWMMMMMMMM0:XMMMWk .0MMMMMMMMNokWMMMN;         
         .0MMMMMMMMMNMMMMX'  cNMMMMMMMMNWMMMWk.         
          cWMMMMMMMMMMMMWx   .0MMMMMMMMMMMMMN,          
          .0MMMMMMMMMMMMK.    :NMMMMMMMMMMMWx           
           cNMMMMMMMMMMWo     .OMMMMMMMMMMMX.           
           .OMMMMMMMMMMK.      ;NMMMMMMMMMWo            
            ,dxkxxxxxxx;        lxxxxxxxxxd. 

************************************* @UW Marketing ******/
