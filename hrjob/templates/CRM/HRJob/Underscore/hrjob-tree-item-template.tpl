<script id="hrjob-tree-item-template" type="text/template">
  <dl>
    <dt><a href="#<%= cid %>/hrjob/<%= id %>" class="hrjob-nav" data-hrjob-event="hrjob:summary:show">
      <span name="contract_type"></span>:
      <span name="position"></span>
    </a></dt>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/general" class="hrjob-nav" data-hrjob-event="hrjob:general:edit">{ts}General{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/health" class="hrjob-nav" data-hrjob-event="hrjob:health:edit">{ts}Healthcare{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/hour" class="hrjob-nav" data-hrjob-event="hrjob:hour:edit">{ts}Hours{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/leave" class="hrjob-nav" data-hrjob-event="hrjob:leave:edit">{ts}Leave{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/pay" class="hrjob-nav" data-hrjob-event="hrjob:pay:edit">{ts}Pay{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/pension" class="hrjob-nav" data-hrjob-event="hrjob:pension:edit">{ts}Pension{/ts}</a></dd>
    <dd><a href="#<%= cid %>/hrjob/<%= id %>/role" class="hrjob-nav" data-hrjob-event="hrjob:role:edit">{ts}Roles{/ts}</a></dd>
  </dl>
</script>
