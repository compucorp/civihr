{assign var="module" value="contactsummary" }
{assign var="prefix" value="contactsummary-" }

<div id="{$module}">
    <div class="container" ng-view>
    </div>
</div>
{literal}
    <script type="text/javascript">
        document.dispatchEvent(new CustomEvent('contactsummaryLoad'));
    </script>
{/literal}