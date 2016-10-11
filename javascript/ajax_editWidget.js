$(document).on('click', "#Form_EditWidgetForm_action_doEditMemberWidget, #Form_EditWidgetForm_action_doDeleteMemberWidget", function(event) {
	event.preventDefault();

	var form = $('#Form_EditWidgetForm');
	var submitButton = $(this);

	var actionName = $(this).attr("name");
	var action = actionName+"="+$(this).attr("value");

	$.ajax(form.attr('action'), {
		type: "POST",
		data: form.serialize()+"&"+action,
		beforeSend: function() {
			submitButton.attr('value','Prebieha odosielanie...');
			submitButton.attr("disabled", true);
		},
		success: function(data) {
			try {
				var json = jQuery.parseJSON(data);

				if(typeof json == 'object') {
					var widget = $('#memberwidgets-sortable #widget-'+json.WidgetID);

					if (actionName=='action_doDeleteMemberWidget')
						widget.remove();
					else {
						widget.replaceWith(json.Widget);

						if ($('#widgetSettings').hasClass('active')) 
							$('#widget-'+json.WidgetID+' .editMemberWidget').toggle();
					}

					$.magnificPopup.close();
				}
			}
			catch(err) {
				form.replaceWith(data);
				$('#Form_EditWidgetForm fieldset .field :input:visible').focus();
				$('#Form_EditWidgetForm :input[required]:visible').first().focus();
			}
		}
	});
});