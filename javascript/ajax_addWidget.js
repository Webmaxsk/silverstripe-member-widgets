$(document).on('click', "#Form_AddWidgetForm_action_doAddMemberWidget", function(event) {
	event.preventDefault();

	var form = $("#Form_AddWidgetForm");
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
					var widget = $(json.Widget);

					widgets.append(widget);

					if (widgets.hasClass('memberwidgets-isotope'))
						widgets.imagesLoaded(function() {
							widgets.isotope('appended', widget);
						});

					if ($('#widgetSettings').hasClass('active'))
						$('#widget-'+json.WidgetID+' .editMemberWidget').toggle();

					$.magnificPopup.open({
						items: {
							src: json.EditWidgetLink
						}
					});
				}
			}
			catch(err) {
				form.replaceWith(data);
				$('#Form_AddWidgetForm fieldset .field :input:visible').focus();
				$('#Form_AddWidgetForm :input[required]:visible').first().focus();
			}
		}
	});
});