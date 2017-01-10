<div id="member_memberwidgets">
	<% include EnableWidgetsSettings %>
	<div id="memberwidgets-sortable">
		$currentMemberWidgetArea
	</div>
	<a id="addMemberWidgetLink" href="$addMemberWidgetLink<% if $ID > 0 %>?PageID=$ID<% end_if %>" class="addMemberWidget ajax-popup-link">Pridať widget</a>
</div>