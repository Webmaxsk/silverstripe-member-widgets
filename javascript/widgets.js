function init_widgets(saveUrl, pageID) {
	var memberWidgets = $('#memberwidgets-sortable');

	memberWidgets.sortable({
		placeholder: "widget-placeholder",
		forcePlaceholderSize: true,
		axis: 'y',
		disabled: true,
		stop: function( event, ui ) {
			var data = $(this).sortable('serialize');

			$.ajax({
				data: data+"&pageID="+pageID,
				type: 'POST',
				url: saveUrl
			});
		}
	});

	$(document).on('click', '#member_memberwidgets .ajax-popup-link', function() {
		$.magnificPopup.open({
			items: {
				src: $(this).attr('href')
			},
			type: 'ajax',
			closeBtnInside: true,
			fixedContentPos: true,
			callbacks: {
				open: function() {
					return $('html').addClass('popup-opened');
				},
				close: function() {
					return $('html').removeClass('popup-opened');
				}
			},
			tClose: 'Zatvoriť (Esc)',
			tLoading: 'Prebieha načítanie...'
		});

		return false;
	});

	$('#widgetSettings').click(function(event) {
		$(this).toggleClass('active');

		$('.editMemberWidget, .disableMemberWidget').toggle();

		if ($(this).hasClass('active'))
			memberWidgets.sortable("enable");
		else
			memberWidgets.sortable("disable");
	});
}