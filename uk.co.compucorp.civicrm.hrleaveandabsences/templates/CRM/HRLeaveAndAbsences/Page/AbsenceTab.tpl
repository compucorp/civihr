<section id="bootstrap-theme" data-leave-absences-absence-tab>
  <absence-tab></absence-tab>
</section>
{literal}
  <script type="text/javascript">
    document.addEventListener('absenceTabReady', function () {
      angular.bootstrap(document.querySelector('[data-leave-absences-absence-tab]'), ['absence-tab']);
    });
  </script>
{/literal}
