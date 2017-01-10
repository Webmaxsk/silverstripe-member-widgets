<div id="widget-$ID" class="WidgetHolder $ClassName">
	<% if Title %><h3>$Title<% if $Parent.ClassName=MemberWidgetArea %><a href="$editMemberWidgetLink" class="editMemberWidget ajax-popup-link" style="display:none;">Upraviť</a><% else_if currentPageID %><a href="$disableMemberWidgetLink?PageID=$currentPageID" class="disableMemberWidget" style="display:none;">Zakázať<% end_if %></a></h3><% end_if %>
	$Content
</div>