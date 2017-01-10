$(document).on('click', '.disableMemberWidget', function(event) {
	event.preventDefault();

	var memberWidgetsIsotope = $('#memberwidgets-sortable.memberwidgets-isotope');

	$.ajax($(this).attr('href'), {
		type: "POST",
		success: function(data) {
			try {
				var json = jQuery.parseJSON(data);

				if(typeof json == 'object') {
					var widget = $('#memberwidgets-sortable #widget-'+json.WidgetID);

					widget.remove();

					if (memberWidgetsIsotope.length)
						memberWidgetsIsotope.isotope('reloadItems').isotope({
							sortBy: 'original-order'
						});
				}
			}
			catch(err) {
			}
		}
	});
});