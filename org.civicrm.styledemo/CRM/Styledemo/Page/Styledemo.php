<?php

class CRM_Styledemo_Page_Styledemo extends CRM_Core_Page {
  function run() {
    CRM_Utils_System::setTitle(ts('Styledemo'));

    parent::run();
  }
}
