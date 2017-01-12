<?php

class MemberWidgets_Controller extends Page_Controller {

	private static $allowed_actions = array(
		'addWidget',
		'editWidget' => '->canEditWidget',
		'disableWidget' => '->canDisableWidget',
		'saveAll',

		'AddWidgetForm',
		'EnableWidgetForm',
		'EditWidgetForm'
	);

	private static $url_handlers = array(
		'editWidget/$ID' => 'editWidget'
	);

	public function init() {
		if (!Member::currentUserID())
			Security::permissionFailure($this);

		parent::init();
	}

	public function index() {
		if (($form = $this->NullForm()) && $form->Message() && $form->MessageType()=="good") {
			$title = 'Widgets';
			$content = '';
		}
		else {
			$title = 'Widgets';
			$content = '';
		}

		$outputData = array (
			'Title' => $title,
			'Content' => $content,
			'Form' => $form
		);

		return $this->customise($outputData)->renderWith(array("MemberWidgets","Page"));
	}

	public function NullForm() {
		$fields = new FieldList();
		$actions = new FieldList();
		$validator = null;

		return Form::create($this, 'NullForm', $fields, $actions, $validator);
	}

	public function addWidget() {
		$title = 'Pridať widget';

		$addForm = $this->AddWidgetForm();
		$addTitle = 'Pridať widget';
		$addContent = '';

		$enableForm = null;
		$enableTitle = 'Obnoviť zakázaný widget';
		$enableContent = '';

		$disabledWidgets = array();
		if ($PageID = $this->request->getVar('PageID')) {
			$filter = array();

			$filter['MemberID'] = Member::currentUserID();
			$filter['PageID'] = $PageID;

			if (($memberWidgets_Page = MemberWidgets_Page::get()->filter($filter)->limit(1)->first()) && $memberWidgets_Page->exists() && $memberWidgets_Page->DisabledWidgets)
				$disabledWidgets = Widget::get()->filter('ID',explode(',', $memberWidgets_Page->DisabledWidgets))->map();

			if (count($disabledWidgets)) {
				$title = 'Pridať/Obnoviť widget';

				$enableForm = $this->EnableWidgetForm();

				$enableForm->Fields()->push(new DropdownField('WidgetID','Widget',$disabledWidgets));
				$enableForm->Fields()->push(new HiddenField('PageID',null,$PageID));
			}
		}

		$outputData = array (
			'Title' => $title,

			'AddTitle' => $addTitle,
			'AddContent' => $addContent,
			'AddForm' => $addForm,

			'EnableTitle' => $enableTitle,
			'EnableContent' => $enableContent,
			'EnableForm' => $enableForm
		);

		if ($this->request->isAjax())
			return $this->customise($outputData)->renderWith(array("MemberWidgets_add"));
		else
			return $this->customise($outputData)->renderWith(array("MemberWidgets_add","Page"));
	}

	public function AddWidgetForm() {
		$availableWidgets = WidgetAreaEditor::create("MemberSideBar")->AvailableWidgets()->map('ClassName','Title');

		$fields = new FieldList();

		if ($PageID = $this->request->getVar('PageID'))
			$fields->push(new HiddenField('PageID',null,$PageID));
		else
			$fields->push(new DropdownField('PageID','Stránka',Page::get()->map()));

		$fields->push(new DropdownField('WidgetClass','Widget',$availableWidgets));


		$actions = new FieldList(
			FormAction::create('doAddMemberWidget', 'Pridať')
		);

		$validator = new MemberWidgets_Validator('PageID','WidgetClass');

		$form = Form::create($this, 'AddWidgetForm', $fields, $actions, $validator);

		return $form;
	}

	public function doAddMemberWidget($data, $form) {
		$filter = array();

		$filter['PageID'] = $data['PageID'];
		$filter['MemberID'] = Member::currentUserID();

		if (!($memberWidgetArea = MemberWidgetArea::get()->filter($filter)->limit(1)->first()) || !$memberWidgetArea->exists())
			$memberWidgetArea = $this->createMemberWidgetArea($filter['PageID'],$filter['MemberID']);

		$widgetsIDs = $memberWidgetArea->WidgetControllers()->column('ID');

		$widget = $data['WidgetClass']::create();
		$widget->ParentID = $memberWidgetArea->ID;

		$widgetsIDs[] = $widget->write();

		if (!($memberWidgets_Page = MemberWidgets_Page::get()->filter($filter)->limit(1)->first()) || !$memberWidgets_Page->exists()) {
			$memberWidgets_Page = new MemberWidgets_Page();

			$memberWidgets_Page->PageID = $filter['PageID'];
			$memberWidgets_Page->MemberID = Member::currentUserID();
		}

		$memberWidgets_Page->WidgetsSort = implode(',', $widgetsIDs);

		$memberWidgets_Page->write();

		if ($this->request->isAjax()) {
			return json_encode(array(
				'Message' => 'Widget pridaný',
				'Type' => 'good',
				'EditWidgetLink' => $widget->editMemberWidgetLink(),
				'WidgetID' => $widget->ID,
				'Widget' => $widget->getController()->WidgetHolder()->getValue()
			));
		}
		else {
			$this->NullForm()->sessionMessage('Widget pridaný', 'good');

			return $this->redirect($this->Link());
		}
	}

	public function EnableWidgetForm() {
		$fields = new FieldList();

		$actions = new FieldList(
			FormAction::create('doEnableWidget', 'Obnoviť')
		);

		$validator = new MemberWidgets_Validator('WidgetID');

		$form = Form::create($this, 'EnableWidgetForm', $fields, $actions, $validator);

		return $form;
	}

	public function doEnableWidget($data, $form) {
		$widget = DataObject::get_by_id('Widget',$data['WidgetID']);

		$filter = array();

		$filter['PageID'] = $data['PageID'];
		$filter['MemberID'] = Member::currentUserID();

		if (($memberWidgets_Page = MemberWidgets_Page::get()->filter($filter)->limit(1)->first()) && $memberWidgets_Page->exists()) {
			if ($memberWidgets_Page->WidgetsSort)
				$widgetsSortIDs = explode(',', $memberWidgets_Page->WidgetsSort);
			else
				$widgetsSortIDs = array();

			$widgetsSortIDs[] = $widget->ID;

			$memberWidgets_Page->WidgetsSort = implode(',', $widgetsSortIDs);

			$disabledWidgetsIDs = array_flip(explode(',', $memberWidgets_Page->DisabledWidgets));

			unset($disabledWidgetsIDs[$widget->ID]);

			$memberWidgets_Page->DisabledWidgets = implode(',', array_flip($disabledWidgetsIDs));

			$memberWidgets_Page->write();
		}

		if ($this->request->isAjax()) {
			return json_encode(array(
				'Message' => 'Widget obnovený',
				'Type' => 'good',
				'WidgetID' => $widget->ID,
				'Widget' => $widget->getController()->customise(array('currentPageID'=>$filter['PageID']))->renderWith("WidgetHolder")->getValue()
			));
		}
		else {
			$this->NullForm()->sessionMessage('Widget obnovený', 'good');

			return $this->redirect($this->Link());
		}
	}

	public function editWidget() {
		$form = $this->EditWidgetForm();
		$title = 'Upraviť widget';
		$content = '';

		$outputData = array (
			'Title' => $title,
			'Content' => $content,
			'Form' => $form
		);

		if ($this->request->isAjax())
			return $this->customise($outputData)->renderWith(array("MemberWidgets_edit"));
		else
			return $this->customise($outputData)->renderWith(array("MemberWidgets_edit","Page"));
	}

	public function EditWidgetForm() {
		$widget = DataObject::get_by_id('Widget',($ID = $this->request->param('ID')) ? $ID : $this->request->postVar('ID'));
		$currentWidget = singleton($widget->ClassName);

		$actions = new FieldList(
			FormAction::create('doEditMemberWidget', 'Uložiť'),
			FormAction::create('doDeleteMemberWidget', 'Vymazať')
		);

		$validator = null;

		$form = Form::create($this, 'EditWidgetForm', new FieldList(), $actions, $validator);

		$form->setFields($currentWidget->getCMSFields());
		$form->Fields()->push(new HiddenField('ID'));
		$form->Fields()->removeByName('Sort');
		$form->Fields()->removeByName('ParentID');
		$form->Fields()->removeByName('Enabled');

		$form->loadDataFrom($widget);

		return $form;
	}

	public function doEditMemberWidget($data, $form) {
		if (isset($data['ID']) && ($ID = $data['ID']) && is_numeric($ID) && ($widget = DataObject::get_by_id('Widget',$ID)) && $widget->canEditCurrent()) {
			foreach ($data as $key => $value)
				if ($widget->hasField($key))
					$widget->$key = $value;

			$widget->write();

			if ($this->request->isAjax()) {
				return json_encode(array(
					'Message' => 'Widget uložený',
					'Type' => 'good',
					'WidgetID' => $widget->ID,
					'Widget' => $widget->getController()->WidgetHolder()->getValue()
				));
			}
			else {
				$this->NullForm()->sessionMessage('Widget uložený', 'good');

				return $this->redirect($this->Link());
			}
		}

		return $this->redirectBack();
	}

	public function doDeleteMemberWidget($data, $form) {
		if (isset($data['ID']) && ($ID = $data['ID']) && is_numeric($ID) && ($widget = DataObject::get_by_id('Widget',$ID)) && $widget->canEditCurrent()) {
			$widget->delete();

			if ($this->request->isAjax()) {
				return json_encode(array(
					'Message' => 'Widget vymazaný',
					'Type' => 'good',
					'WidgetID' => $widget->OldID
				));
			}
			else {
				$this->NullForm()->sessionMessage('Widget vymazaný', 'good');

				return $this->redirect($this->Link());
			}
		}

		return $this->redirectBack();
	}

	public function disableWidget() {
		if (($PageID = $this->request->getVar('PageID')) && ($ID = $this->request->param('ID')) && is_numeric($ID) && ($widget = DataObject::get_by_id('Widget',$ID))) {
			$filter = array();

			$filter['PageID'] = $PageID;
			$filter['MemberID'] = Member::currentUserID();

			if (!($memberWidgets_Page = MemberWidgets_Page::get()->filter($filter)->limit(1)->first()) || !$memberWidgets_Page->exists()) {
				$memberWidgets_Page = new MemberWidgets_Page();

				$memberWidgets_Page->PageID = $filter['PageID'];
				$memberWidgets_Page->MemberID = Member::currentUserID();
			}

			if ($memberWidgets_Page->DisabledWidgets)
				$disabledWidgetsIDs = explode(',', $memberWidgets_Page->DisabledWidgets);
			else
				$disabledWidgetsIDs = array();

			$disabledWidgetsIDs[] = $widget->ID;

			$memberWidgets_Page->DisabledWidgets = implode(',', $disabledWidgetsIDs);

			if ($memberWidgets_Page->WidgetsSort) {
				$widgetsSortIDs = array_flip(explode(',', $memberWidgets_Page->WidgetsSort));

				unset($widgetsSortIDs[$widget->ID]);

				$memberWidgets_Page->WidgetsSort = implode(',', array_flip($widgetsSortIDs));
			}

			$memberWidgets_Page->write();

			if ($this->request->isAjax()) {
				return json_encode(array(
					'Message' => 'Widget zakázaný',
					'Type' => 'good',
					'WidgetID' => $widget->ID
				));
			}
			else {
				$this->NullForm()->sessionMessage('Widget zakázaný', 'good');

				return $this->redirect($this->Link());
			}
		}

		return $this->redirectBack();
	}

	public function saveAll() {
		if ($widgets = $this->request->postVar('widget')) {
			if ($pageID = $this->request->postVar('pageID')) {
				$filter = array();
				$filter['PageID'] = $pageID;
				$filter['MemberID'] = Member::currentUserID();

				if (!($memberWidgets_Page = MemberWidgets_Page::get()->filter($filter)->limit(1)->first()) || !$memberWidgets_Page->exists()) {
					$memberWidgets_Page = new MemberWidgets_Page();

					$memberWidgets_Page->PageID = $pageID;
					$memberWidgets_Page->MemberID = Member::currentUserID();
				}

				$memberWidgets_Page->WidgetsSort = implode(',', $widgets);

				$memberWidgets_Page->write();
			}
		}
	}

	public function Link($action = null) {
		return Controller::join_links(Director::baseURL().'memberwidgets', $action);
	}

	public function canEditWidget() {
		return ($ID = $this->request->param('ID')) && is_numeric($ID) && ($widget = DataObject::get_by_id('Widget',$ID)) && $widget->canEditCurrent();
	}

	public function canDisableWidget() {
		return ($PageID = $this->request->getVar('PageID')) && ($ID = $this->request->param('ID')) && is_numeric($ID) && ($widget = DataObject::get_by_id('Widget',$ID)) && $widget->canDisableCurrent($PageID);
	}
}