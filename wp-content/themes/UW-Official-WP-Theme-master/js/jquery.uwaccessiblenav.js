/*
 * UW Accessible Dropdown Navigation
 * @author Dane Odekirk
 * @version 1.0
 *
 * @dependencies: jquery.uwaccessiblenav.css 
 *      - the css file provides classnames which can be overwritten/changed or modified.
 */

; (function($) {
    $.uwaccessible = $.uwaccessible || { };
	$.uwaccessible.nav = {
		options: {	
            classname : 'accessibleOpen', //the class that gets added or removed for tabbing 
            dropdown  : '.text', //the div that contains the dropdown
			exits      : '' //the id's of the links before (and after if necessary) that will close all open dropdowns
		}
	};	
	
	$.fn.uwaccessiblenav = function(options) { 
		var options = $.extend({}, $.uwaccessible.nav.options, options); 		

        if(options.exits.length == 0) {
            var a = this.find('a').first();
            var i = this.closest('div').find('a').index(a);
            options.exits = this.closest('div').find('a').get(i-1);
        };

        $(options.exits).focus(function() {
            options._first.removeClass('accessibleOpen');         
        });

        return this.each(function(){ 
            options._first = 
                $(this).children().each(function() {
                        var self = $(this);
                        self.find(options.dropdown).mouseout(function() {
                            self.removeClass(options.classname).find(':focus').blur();
                        }).end()
                        .find('a').last().blur(function(e) {
                            self.removeClass(options.classname);
                        }).end()
                        .first().focus(function(e) {
                            self.siblings().removeClass(options.classname);
                            self.addClass(options.classname);
                        }).end()
                 }).first();
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

************************************* @UW Marketing *****/
