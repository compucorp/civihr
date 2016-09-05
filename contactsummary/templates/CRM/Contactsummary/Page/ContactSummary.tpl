{assign var="module" value="contactsummary" }

<div id="bootstrap-theme">
    <div id="{$module}" ng-view>
    </div>
</div>
{literal}
    <script type="text/javascript">
      jQuery(function() {
        document.dispatchEvent(new CustomEvent('contactsummaryLoad'));
        window.contactsummaryLoad = true;
      })
    </script>
{/literal}
