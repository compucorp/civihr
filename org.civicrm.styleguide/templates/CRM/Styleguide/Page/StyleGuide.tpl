<section id="bootstrap-theme">
  <div class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
      <div class="navbar-header">
        <a class="navbar-brand" href="#">Style Guide</a>
      </div>
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown active">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Select an element: <b class="caret"></b></a>
          <ul class="dropdown-menu  scroll-menu sg-sect">
            {php}listElementsAsOptions('docs');{/php}
            {php}listElementsAsOptions('foundation');{/php}
            <li role="presentation" class="divider"></li>
            <li role="presentation" class="dropdown-header">Base Styles</li>
            {php}listMarkupAsOptions('base');{/php}
            <li role="presentation" class="divider"></li>
            <li role="presentation" class="dropdown-header">Patterns</li>
            {php}listMarkupAsOptions('patterns');{/php}
          </ul>
        </li>
      </ul>
    </div>
  </div>
  <div class="sg-body sg-container container">
    <div class="row">
      <h1 class="page-header">Style Guide Documentation</h1>
      {php}showDocs();{/php}
    </div>
    <div class="row">
      <h1 class="page-header">Foundation</h1>
      {php}showFoundation();{/php}
    </div>
    <div class="row sg-base-styles">
      <h1 class="page-header">Base Styles</h1>
      {php}showMarkup('base');{/php}
    </div><!--/.sg-base-styles-->
    <div class="sg-pattern-styles">
      <h1 class="page-header">Patterns<small> - Design and mark-up patterns unique to your site.</small></h1>
      {php}showMarkup('patterns');{/php}
    </div>
  </div>
</section>
