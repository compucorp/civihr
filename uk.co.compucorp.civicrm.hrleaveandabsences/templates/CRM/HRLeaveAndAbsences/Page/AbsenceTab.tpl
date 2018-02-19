<section id="bootstrap-theme" data-leave-absences-absence-tab>
  <ui-view></ui-view>
</section>
{literal}
  <script type="text/javascript">
    document.addEventListener('absenceTabReady', function () {
      angular.bootstrap(document.querySelector('[data-leave-absences-absence-tab]'), ['absence-tab']);
    });
  </script>
{/literal}
