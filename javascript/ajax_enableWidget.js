$(document).on('click', "#Form_EnableWidgetForm_action_doEnableWidget", function(event) {
	event.preventDefault();

	var form = $("#Form_EnableWidgetForm");
	var submitButton = $(this);

	var widgets = $('#memberwidgets-sortable');

	$.ajax(form.attr('action'), {
		type: "POST",
		data: form.serialize(),
		beforeSend: function() {
			submitButton.attr('value','Prebieha odosielanie...');
			submitButton.attr("disabled", true);
		},
		success: function(data) {
			try {
				var json = jQuery.parseJSON(data);

				if(typeof json == 'object') {
					widgets.append(json.Widget);

					if ($('#widgetSettings').hasClass('active'))
						$('#widget-'+json.WidgetID+' .disableMemberWidget').toggle();

					$.magnificPopup.close();
				}
			}
			catch(err) {
				form.replaceWith(data);
				$('#Form_EnableWidgetForm fieldset .field :input:visible').focus();
				$('#Form_EnableWidgetForm :input[required]:visible').first().focus();
			}
		}
	});
});