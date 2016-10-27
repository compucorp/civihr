{php}
  $htmlBuilder = new CRM_StyleGuide_HtmlBuilder($this->get_template_vars('styleguide'));
{/php}
<section id="bootstrap-theme">
  <!-- <div class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
      <div class="navbar-header">
        <a class="navbar-brand" href="#">Style Guide</a>
      </div>
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown active">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Select an element: <b class="caret"></b></a>
          <ul class="dropdown-menu  scroll-menu sg-sect">
            {php}$htmlBuilder->listElementsAsOptions('docs');{/php}
            {php}$htmlBuilder->listElementsAsOptions('foundation');{/php}
            <li role="presentation" class="divider"></li>
            <li role="presentation" class="dropdown-header">Base Styles</li>
            {php}$htmlBuilder->listMarkupAsOptions('base');{/php}
            <li role="presentation" class="divider"></li>
            <li role="presentation" class="dropdown-header">Patterns</li>
            {php}$htmlBuilder->listMarkupAsOptions('patterns');{/php}
          </ul>
        </li>
      </ul>
    </div>
  </div> -->
  <div class="sg-body sg-container container">
    <div class="row">
      {php}$htmlBuilder->showDocs();{/php}
    </div>
    <div class="row">
      <div class="page-header">
        <h1>Foundation</h1>
      </div>
      {php}$htmlBuilder->showFoundation();{/php}
    </div>
    <div class="row sg-base-styles">
      <div class="page-header">
        <h1>Base Styles</h1>
      </div>
      {php}$htmlBuilder->showMarkup('base');{/php}
    </div><!--/.sg-base-styles-->
    <div class="sg-pattern-styles">
      <div class="page-header">
        <h1>Patterns<small> - Design and mark-up patterns unique to your site.</small></h1>
      </div>
      {php}$htmlBuilder->showMarkup('patterns');{/php}
    </div>
  </div>
</section>
