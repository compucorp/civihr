<div data-leave-absences-admin-dashboard>
  <admin-dashboard-container></admin-dashboard-container>
</div>
{literal}
  <script type="text/javascript">
    document.addEventListener('adminDashboardReady', function () {
      angular.bootstrap(document.querySelector('[data-leave-absences-admin-dashboard]'), ['admin-dashboard']);
    });
  </script>
{/literal}
