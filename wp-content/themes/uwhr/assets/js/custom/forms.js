$(document).ready(function() {
	$.getScript(UWHR.getBaseUrl() + '/wp-content/themes/uwhr/assets/js/vendor/isotope.pkgd.min.js', function() {
		setup();
	});
});

function setup() {
	var $grid = $('.grid');

	$grid.isotope({
		masonry: {
    		columnWidth: '.grid-sizer',
    		gutter: '.gutter-sizer'
  		},
		itemSelector: '.grid-item',
		percentPosition: true
	});

	var input = document.getElementById('searchForms');

	input.addEventListener('keyup', function(e) {
		var that = this;
		var string = that.value.toLowerCase();

		$grid.isotope({ filter: function() {
	  		var name = $(this).find('.form-title').text().toLowerCase();
	  		return (name.indexOf(string) > -1);
		}});
	});
}
