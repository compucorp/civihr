{assign var="module" value="hrjobroles" }
{assign var="prefix" value="hrjobroles-" }

<div id="{$module}" >
  <div class="container" ng-view></div>
</div>
{literal}
<script type="text/javascript">
  document.addEventListener('hrjobrolesReady', function (){
    angular.bootstrap(document.getElementById('hrjobroles'), ['hrjobroles']);
  });
</script>
{/literal}
