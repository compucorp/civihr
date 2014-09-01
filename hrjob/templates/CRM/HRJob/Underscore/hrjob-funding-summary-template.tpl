<script id="hrjob-funding-summary-template" type="text/template">
  <div class="crm-summary-row">
    <div class="crm-label">{ts}Funding{/ts}</div>
    {* // HR-395
    <div class="crm-content">
    {literal}<% if (is_tied_to_funding == 1) { %>{/literal}
        <div><strong>{ts}Tied to funding{/ts}</strong></div>
    {literal}<% } %>{/literal}
    {literal}<% if (funding_org_id) { %>{/literal}
      <div><strong>{ts}Funding organization{/ts}</strong>: <a href="#" class="hrjob-funding_org_id" /></div>
    {literal}<% } %>{/literal}
    *}
    {literal}<% if (funding_notes) { %>{/literal}
      <div><strong>{ts}Notes{/ts}</strong>: <%- funding_notes %></div>
    {literal}<% } %>{/literal}
    </div>
  </div>
</script>