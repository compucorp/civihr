<section data-leave-absences-absence-tab>
  <!-- START for code to move to views folder in PCHR-2003 -->
  <div id="bootstrap-theme">
    <div class="absence-tab-page">
      <div class="btn-group">
        <button type="button" class="btn btn-info">CREATE NEW</button>
        <button
          type="button"
          class="btn btn-info dropdown-toggle"
          data-toggle="dropdown">
          <span class="caret"></span>
          <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <li>
            <a href="#">Record staff sickness</a>
          </li>
          <li>
            <a href="#">Record staff overtime</a>
          </li>
        </ul>
      </div>

      <ul class="nav nav-pills nav-pills-horizontal nav-pills-horizontal-default">
        <li class="active">
          <a href="#">Report</a>
        </li>
        <li>
          <a href="#">Calendar</a>
        </li>
        <li>
          <a href="#">Entitlements</a>
        </li>
        <li>
          <a href="#">Work Patterns</a>
        </li>
      </ul>

    </div>
  </div>
  <!-- END for code to move to views folder in PCHR-2003 -->
  <ui-view></ui-view>
</section>
