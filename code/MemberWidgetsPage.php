<?php

class MemberWidgetsPage extends Page {

}

class MemberWidgetsPage_Controller extends Page_Controller {

	public function init() {
		parent::init();

		Requirements::javascript(MEMBER_WIDGETS_DIR."/javascript/jquery.isotope.js");
	}
}