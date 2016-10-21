<?php
  class CRM_Styleguide_HtmlBuilder {

    public function __construct() {
      $this->root = CRM_Core_Resources::singleton()->getPath('org.civicrm.styleguide') . '/partials/';
    }

    public function listElementsAsOptions ($type) {
      $files = array();
      $handle=opendir($this->root . $type . '/');

      while (false !== ($file = readdir($handle))) {
        if(stristr($file,'.html')) {
          $files[] = $file;
        }
      }

      echo '<li role="presentation" class="divider"></li><li role="presentation" class="dropdown-header">'.$type.'</li>';
      sort($files);

      foreach ($files as $file) {
        $filename = preg_replace("/\.html$/i", "", $file);
        $title = preg_replace("/\-/i", " ", $filename);
        $title = ucwords($title);

        echo '<li><a href="#sg-'.$filename.'">'.$title.'</a></li>';
      }
    }

    // Display title of each markup samples as a select option
    public function listMarkupAsOptions ($type) {
      $files = array();
      $handle=opendir($this->root . 'markup/' . $type);

      while (false !== ($file = readdir($handle))) {
        if(stristr($file,'.html')) {
          $files[] = $file;
        }
      }

      sort($files);

      foreach ($files as $file) {
        $filename = preg_replace("/\.html$/i", "", $file);
        $title = preg_replace("/\-/i", " ", $filename);
        $title = ucwords($title);

        echo '<li><a href="#sg-'.$filename.'">'.$title.'</a></li>';
      }
    }

    // Display foundation elements
    public function showDocs() {
      $files = array();
      $handle=opendir($this->root . 'docs/');

      while (false !== ($file = readdir($handle))) {
        if(stristr($file,'.html')) {
          $files[] = $file;
        }
      }

      sort($files);

      foreach ($files as $file) {
        $filename = preg_replace("/\.html$/i", "", $file);
        $title = preg_replace("/\-/i", " ", $filename);

        echo '<div class="col-lg-12 sg-section" id="sg-'.$filename.'">';
        echo '<div class="sg-display">';
        echo '<h2 class="sg-h2">'.$title.'</h2>';
        include($this->root . 'docs/' . $file);
        echo '</div><!--/.sg-display-->';
        echo '</div><!--/.sg-section-->';
      }
    }

    // Display foundation elements
    public function showFoundation() {
      $files = array();
      $handle=opendir($this->root . 'foundation/');

      while (false !== ($file = readdir($handle))) {
        if(stristr($file,'.html')) {
          $files[] = $file;
        }
      }

      sort($files);

      foreach ($files as $file) {
        $filename = preg_replace("/\.html$/i", "", $file);
        $title = preg_replace("/\-/i", " ", $filename);

        echo '<div class="col-lg-12 sg-section" id="sg-'.$filename.'">';
        echo '<div class="sg-display">';
        echo '<h2 class="sg-h2">'.$title.'</h2>';
        include($this->root . 'foundation/' . $file);
        echo '</div><!--/.sg-display-->';
        echo '</div><!--/.sg-section-->';
      }
    }

    // Display markup view & source
    public function showMarkup($type) {
      $files = array();
      $handle=opendir($this->root . 'markup/'.$type);

      while (false !== ($file = readdir($handle))) {
        if(stristr($file,'.html')) {
          $files[] = $file;
        }
      }

      sort($files);

      foreach ($files as $file) {
        $filename = preg_replace("/\.html$/i", "", $file);
        $title = preg_replace("/\-/i", " ", $filename);
        $documentation = $this->root . 'usage/' . $type . '/' . $file;

        echo '<div class="sg-markup sg-section">';
        echo '<div class="sg-display">';
        echo '<h2 class="sg-h2"><a id="sg-'.$filename.'" class="sg-anchor">'.$title.'</a></h2>';
        echo '<div class="row"><div class="' . (file_exists($documentation) ? 'col-md-8' : 'col-sm-12') . '">';
        include($this->root . 'markup/'.$type.'/'.$file);
        echo '</div>';

        if (file_exists($documentation)) {
          echo '<div class="col-md-4"><div class="well sg-doc">';
          echo '<h3 class="sg-h3">Usage Notes</h3>';
          include($documentation);
          echo '</div></div></div>';
        }

        echo '</div><!--/.sg-display-->';
        echo '<div class="sg-markup-controls"><a class="btn btn-primary sg-btn sg-btn--source" href="#">View Source</a> <a class="sg-btn--top" href="#top">Back to Top</a> </div>';
        echo '<div class="sg-source sg-animated">';
        echo '<a class="btn btn-default sg-btn sg-btn--select" href="#">Copy Source</a>';
        echo '<pre class="prettyprint linenums"><code>';
        echo htmlspecialchars(file_get_contents($this->root . 'markup/' . $type . '/' . $file));
        echo '</code></pre>';
        echo '</div><!--/.sg-source-->';
        echo '</div><!--/.sg-section-->';
      }
    }
  }
?>
