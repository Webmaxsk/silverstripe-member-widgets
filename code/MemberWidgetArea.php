<?php

class MemberWidgetArea extends WidgetArea {

	private static $has_one = array(
		'Page' => 'Page',
		'Member' => 'Member'
	);

	public function WidgetControllers() {
		$widgetcontrollers = new ArrayList();

		$widgetItems = new ArrayList();

		$sortIDs = false;
		$disabledWidgetsIDs = false;

		$page = $this->Page();

		if ($page->ID) {
			if ($sideBar = $page->SideBarView())
				$widgetItems->merge($sideBar->ItemsToRender());

			$filter = array();
			$filter['PageID'] = $page->ID;
			$filter['MemberID'] = Member::currentUserID();

			if (($memberWidgets_Page = MemberWidgets_Page::get()->filter($filter)->limit(1)->first()) && $memberWidgets_Page->exists()) {
				$sortIDs = array_flip(explode(',', $memberWidgets_Page->WidgetsSort));

				if ($memberWidgets_Page->DisabledWidgets)
					$disabledWidgetsIDs = array_flip(explode(',', $memberWidgets_Page->DisabledWidgets));
			}
		}

		$widgetItems->merge($this->ItemsToRender());

		if ($widgetItems->exists()) {
			foreach ($widgetItems as $widget) {
				if ($disabledWidgetsIDs && isset($disabledWidgetsIDs[$widget->ID]))
					continue;

				$controller = $widget->getController();

				if ($sortIDs)
					$controller->Sort = isset($sortIDs[$widget->ID]) ? $sortIDs[$widget->ID] : -1;

				$controller->init();
				$widgetcontrollers->push($controller);
			}
		}

		if ($sortIDs)
			$widgetcontrollers = $widgetcontrollers->sort('Sort');

		return $widgetcontrollers;
	}

	public function ItemsToRender() {
		if ($this->Page()->ID)
			return parent::ItemsToRender();
		else {
			if ($currentUserID = Member::currentUserID()) {
				$parentIDs = array_merge(
					WidgetArea::get()->filter(array('ClassName:not'=>'MemberWidgetArea'))->column(),
					MemberWidgetArea::get()->filter('MemberID',$currentUserID)->column()
				);

				$widgets = Widget::get()->filter(array('ParentID'=>$parentIDs,"Enabled"=>1));

				return $widgets;
			}

			return null;
		}
	}
}