<?php

class MemberWidgets_Page extends DataObject {

	private static $db = array(
		'WidgetsSort' => 'Varchar(100)',
		'DisabledWidgets' => 'Varchar(100)'
	);

	private static $has_one = array(
		'Page' => 'Page',
		'Member' => 'Member'
	);

}