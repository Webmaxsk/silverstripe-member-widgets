<?php

class MemberWidgetsWidgetExtension extends DataExtension {

	public function editMemberWidgetLink() {
		return singleton('MemberWidgets_Controller')->Link("editWidget/{$this->owner->ID}");
	}

	public function disableMemberWidgetLink() {
		return singleton('MemberWidgets_Controller')->Link("disableWidget/{$this->owner->ID}");
	}

	protected function isOwner($member = null) {
		if ($this->owner->Parent()->is_a('MemberWidgetArea'))
			return ($member || ($member = Member::currentUser())) && $member->ID == MemberWidgetArea::get()->filter('ID',$this->owner->ParentID)->limit(1)->first()->MemberID;
		else
			return false;
	}

	protected function isAdmin() {
		return Permission::check('ADMIN');
	}

	public function canEditCurrent() {
		return $this->canEdit(Member::currentUser());
	}

	public function canEdit($member = null) {
		return $this->IsOwner($member) || $this->isAdmin();
	}

	public function canDisableCurrent($PageID) {
		$filter = array();

		$filter['PageID'] = $PageID;
		$filter['MemberID'] = Member::currentUserID();

		$disabledWidgetsIDs = array();
		if (($memberWidgets_Page = MemberWidgets_Page::get()->filter($filter)->limit(1)->first()) && $memberWidgets_Page->exists())
			$disabledWidgetsIDs = array_flip(explode(',', $memberWidgets_Page->DisabledWidgets));

		return !isset($disabledWidgetsIDs[$this->owner->ID]) && ((!$this->owner->Parent()->is_a('MemberWidgetArea') && $member = Member::currentUser()) || $this->isAdmin());
	}

	public function currentPageID() {
    	return ($ID = Controller::curr()->ID) > 0 ? $ID : null;
    }
}