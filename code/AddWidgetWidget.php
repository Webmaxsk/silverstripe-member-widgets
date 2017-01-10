<?php

class AddWidgetWidget extends Widget {

	private static $title = "Widget for adding new widget";
	private static $cmsTitle = "Widget for adding new widget";
	private static $description = "Displays widget to adding new widgets or enabling existing widgets.";

	private static $only_available_in = array(
		'none'
	);

	public function requireDefaultRecords() {
		if (!DataObject::get_one(__CLASS__)) {
			$selfClass = $this->ClassName;

			$addWidget = $selfClass::create();
			$addWidget->Title = "Pridať widget";
			$addWidget->write();

			DB::alteration_message(sprintf('Created default "%s".', $this->Title),'created');
		}
	}

	public function WidgetHolder() {
		return $this->renderWith("PlainWidgetHolder");
	}
}

class AddWidgetWidget_Controller extends WidgetController {

	public function WidgetHolder() {
		return $this->widget->WidgetHolder();
	}
}