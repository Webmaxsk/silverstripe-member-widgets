<% with controller.curr %>
	<a id="addMemberWidgetLink" href="$addMemberWidgetLink<% if $ID > 0 %>?PageID=$ID<% end_if %>" class="addMemberWidget ajax-popup-link">$Top.Title</a>
<% end_with %>