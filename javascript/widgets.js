function init_widgets(saveUrl, pageID) {
	var memberWidgets = $('#memberwidgets-sortable');
	var useIsotope = memberWidgets.hasClass('memberwidgets-isotope');

	$(window).load(function() {
		if (useIsotope)
			memberWidgets.isotope({  
				transformsEnabled: false,
				itemSelector: '.memberwidgets-isotope-item'
			});
	});

	memberWidgets.sortable({
		cursor: 'move',
		forcePlaceholderSize: true,
		disabled: true,
		start: function( event, ui ) {
			if (useIsotope) {
				ui.item.addClass('grabbing moving').removeClass('memberwidgets-isotope-item');

				ui.placeholder.addClass('starting').removeClass('moving').css({
					top: ui.originalPosition.top,
					left: ui.originalPosition.left
				});

				memberWidgets.isotope('reloadItems');
			}
		},
		change: function( event, ui ) {
			if (useIsotope) {
				ui.placeholder.removeClass('starting');

				memberWidgets.isotope('reloadItems').isotope({
					sortBy: 'original-order'
				});
			}
		},
		beforeStop: function( event, ui ) {
			if (useIsotope) {
				ui.placeholder.after(ui.item);
			}
		},
		stop: function( event, ui ) {
			if (useIsotope) {
				ui.item.removeClass('grabbing').addClass('memberwidgets-isotope-item');

				memberWidgets.isotope('reloadItems').isotope({
					sortBy: 'original-order'
				}, function() {
					if (!ui.item.is('.grabbing'))
						ui.item.removeClass('moving');
				});
			}

			var data = $(this).sortable('serialize');

			$.ajax({
				data: data+"&pageID="+pageID,
				type: 'POST',
				url: saveUrl
			});
		}
	});

	$(document).on('click', '#member_memberwidgets .ajax_memberwidgets_popup', function() {
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

		return false;
	});
}