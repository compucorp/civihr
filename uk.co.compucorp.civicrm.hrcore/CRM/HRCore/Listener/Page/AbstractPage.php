<?php
  abstract class CRM_HRCore_Listener_Page_AbstractPage {

    protected $pageClass;
    protected $page;

    public function __construct($page) {
      $this->page = $page;
    }

    protected function canHandle() {
      return $this->page instanceof $this->pageClass;
    }
  }
