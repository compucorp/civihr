<section data-leave-absences-absence-tab>
  <div class="chr_absence-tab-page">
    <div class="button-container">
      <div class="btn-group">
        <button type="button" class="btn btn-primary">Create New</button>
        <button
          type="button"
          class="btn btn-primary dropdown-toggle dropdown-toggle-split"
          data-toggle="dropdown">
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <li>
            <a href="#">Record staff sickness</a>
          </li>
          <li>
            <a href="#">Record staff overtime</a>
          </li>
        </ul>
      </div>
    </div>

    <div class="tabs-container">
      <ul class="nav nav-tabs nav-tabs-header">
        <li>
          <a class="chr_absence-tab-page--active_bottom">Report</a>
        </li>
        <li>
          <a>Calendar</a>
        </li>
        <li>
          <a>
            Entitlements
          </a>
        </li>
        <li>
          <a>Work Patterns</a>
        </li>
      </ul>
      <div class="tab-content">
        <ui-view></ui-view>
      </div>
    </div>
  </div>

</section>
{literal}
<script type="text/javascript">
  document.addEventListener('absenceTabReady', function () {
    angular.bootstrap(document.querySelector('[data-leave-absences-absence-tab]'), ['absence-tab']);
  });
</script>
{/literal}
