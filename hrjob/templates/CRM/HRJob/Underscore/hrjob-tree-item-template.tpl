<script id="hrjob-tree-item-template" type="text/template">
  <dl class="<%= is_active ? '' : 'hrjob-inactive' %>">
    {*
    <dt><a href="#<%= cid %>/hrjob/<%= id %>" class="hrjob-nav" data-hrjob-event="hrjob:summary:show">
    *}
    <dt>
      <a href="#<%= cid %>/hrjob/<%= id %>/copy" class="hrjob-nav ui-icon ui-icon-copy" data-hrjob-event="hrjob:general:copy" title="{ts}Copy{/ts}"></a>
      <a href="#<%= cid %>/hrjob/<%= id %>" class="hrjob-nav <%= (is_primary == '1') ? 'primary' : '' %>" data-hrjob-event="hrjob:summary:show">
        <span name="position"></span>
        {literal}
          [<span>Summary</span>]
        {/literal}
    </a></dt>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/general" class="hrjob-nav" data-hrjob-event="hrjob:general:edit">{ts}General{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/hour" class="hrjob-nav" data-hrjob-event="hrjob:hour:edit">{ts}Hours{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/pay" class="hrjob-nav" data-hrjob-event="hrjob:pay:edit">{ts}Pay{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/role" class="hrjob-nav" data-hrjob-event="hrjob:role:edit">{ts}Roles{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/funding" class="hrjob-nav" data-hrjob-event="hrjob:funding:edit">{ts}Funding{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/leave" class="hrjob-nav" data-hrjob-event="hrjob:leave:edit">{ts}Leave{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/health" class="hrjob-nav" data-hrjob-event="hrjob:health:edit">{ts}Insurance{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/pension" class="hrjob-nav" data-hrjob-event="hrjob:pension:edit">{ts}Pension{/ts}</a></dd>
  </dl>
</script>
