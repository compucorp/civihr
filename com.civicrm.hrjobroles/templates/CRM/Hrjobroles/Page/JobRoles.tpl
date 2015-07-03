{assign var="module" value="hrjobroles" }
{assign var="prefix" value="hrjobroles-" }

<div id="{$module}" >
    <div class="container" ng-view></div>
</div>

{literal}
    <script type="text/javascript">
        document.dispatchEvent(new CustomEvent('hrjobrolesLoad'));
    </script>
{/literal}