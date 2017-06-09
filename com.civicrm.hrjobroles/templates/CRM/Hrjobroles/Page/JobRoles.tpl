<section id="bootstrap-theme" data-job-roles>
  <div class="container" ng-view></div>
</section>
{literal}
<script type="text/javascript">
  document.addEventListener('hrjobrolesReady', function () {
    angular.bootstrap(document.querySelector('[data-job-roles]'), ['hrjobroles']);
  });
</script>
{/literal}
