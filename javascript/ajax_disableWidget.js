$(document).on('click', '.disableMemberWidget', function(event) {
	event.preventDefault();

	$.ajax($(this).attr('href'), {
		type: "POST",
		success: function(data) {
			try {
				var json = jQuery.parseJSON(data);

				if(typeof json == 'object') {
					var widget = $('#memberwidgets-sortable #widget-'+json.WidgetID);

					widget.remove();
				}
			}
			catch(err) {
			}
		}
	});
});