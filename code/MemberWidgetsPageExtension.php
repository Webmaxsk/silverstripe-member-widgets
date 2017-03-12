<?php

class MemberWidgetsPageExtension extends DataExtension {

	private static $db = array(
		'EnableAddWidget' => 'Boolean'
	);

	private static $has_many = array(
		'MemberWidgetArea' => 'MemberWidgetArea'
	);

	private static $defaults = array(
		'EnableAddWidget' => 1
	);

	public function updateCMSFields(FieldList $fields) {
		$fields->addFieldToTab("Root.Widgets", new CheckboxField("EnableAddWidget", 'Povoliť pridávanie widgetov'));
	}

	public function currentMemberWidgetArea() {
		return $this->owner->MemberWidgetArea()->filter('MemberID',Member::currentUserID())->limit(1)->first();
	}
}

class MemberWidgetsPageControllerExtension extends Extension {

	public function onAfterInit() {
		if ($this->owner->ID > 0) {
			$filter = array();
			$filter['PageID'] = $this->owner->ID;
			$filter['MemberID'] = Member::currentUserID();

			if (!($memberWidgetArea = MemberWidgetArea::get()->filter($filter)->limit(1)->first()) || !$memberWidgetArea->exists())
				$this->createMemberWidgetArea($filter['PageID'], $filter['MemberID']);

			Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
			Requirements::javascript(THIRDPARTY_DIR."/jquery-ui/jquery-ui.js");

			Requirements::javascript(MEMBER_WIDGETS_DIR."/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js");
			Requirements::javascript(MEMBER_WIDGETS_DIR."/magnific-popup/dist/jquery.magnific-popup.min.js");
			Requirements::javascript(MEMBER_WIDGETS_DIR."/javascript/widgets.js");
			Requirements::javascript(MEMBER_WIDGETS_DIR."/javascript/ajax_addWidget.js");
			Requirements::javascript(MEMBER_WIDGETS_DIR."/javascript/ajax_enableWidget.js");
			Requirements::javascript(MEMBER_WIDGETS_DIR."/javascript/ajax_editWidget.js");
			Requirements::javascript(MEMBER_WIDGETS_DIR."/javascript/ajax_disableWidget.js");

			Requirements::customScript("
				init_widgets('".$this->saveAllMemberWidgetsLink()."',".$this->owner->ID.");
			");
		}
	}

	public function createMemberWidgetArea($pageID, $memberID) {
		$memberWidgetArea = MemberWidgetArea::create();
		$memberWidgetArea->PageID = $pageID;
		$memberWidgetArea->MemberID = $memberID;

		$memberWidgetArea->write();

		return $memberWidgetArea;
	}

	public function addMemberWidgetLink() {
		return singleton('MemberWidgets_Controller')->Link('addWidget');
	}

	public function saveAllMemberWidgetsLink() {
		return singleton('MemberWidgets_Controller')->Link('saveAll');
	}
}