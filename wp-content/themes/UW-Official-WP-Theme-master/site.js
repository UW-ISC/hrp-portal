jQuery(document).ready(function($) {

/* --------- Left Navigation --------- */

    $("#menu").uwaccessibleleftnav();

/* --------- Search box clear --------- */

	$(".wTextInput").focus(function () {
		if ($(this).val() === $(this).attr("title")) {
			$(this).val("");
		}
	}).blur(function () {
		if ($(this).val() === "") {
			$(this).val($(this).attr("title"));
		}
	});


/* --------- Init Dropdown Accesibility --------- */

    $("#navg").uwaccessiblenav();
});
 
