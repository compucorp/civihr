{assign var="module" value="hrjob-contract" }
{assign var="prefix" value="hrjc-" }

<div id="{$module}" hrjc-loader hrjc-loader-show="true">
  <div class="container" ng-view>
  </div>
</div>
{literal}
<script type="text/javascript">
  document.addEventListener('hrjcReady', function () {
    angular.bootstrap(document.getElementById('hrjob-contract'), ['job-contract']);
  });
</script>
{/literal}
