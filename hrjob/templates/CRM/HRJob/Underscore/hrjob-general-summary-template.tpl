<script id="hrjob-general-summary-template" type="text/template">

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Position{/ts}</div>
    <div class="crm-content"><span name="position"></span></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Title{/ts}</div>
    <div class="crm-content"><span name="title"/></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Contract Type{/ts}</div>
    <div class="crm-content"><%- FieldOptions.contract_type[contract_type] %></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Department{/ts}</div>
    <div class="crm-content"><span name="department" /></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Level{/ts}</div>
    <div class="crm-content"><%- FieldOptions.level_type[level_type] %></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Manager{/ts}</div>
    <div class="crm-content"><a href="#" class="hrjob-manager_contact" /></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Normal Place of Work{/ts}</div>
    <div class="crm-content"><%- FieldOptions.location[location] %></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Time Period{/ts}</div>
    <div class="crm-content">
      <%- FieldOptions.period_type[period_type] %>
      {literal}<% if (period_start_date || period_end_date) { %>{/literal}
        (<%- period_start_date ? period_start_date : '{ts escape="js"}Unspecified{/ts}' %>
        to
        <%- period_end_date ? period_end_date : '{ts escape="js"}Unspecified{/ts}' %>)
    {literal}<% } %>{/literal}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Funding{/ts}</div>
    <div class="crm-content">
    {literal}<% if (is_tied_to_funding == 1) { %>{/literal}
        <div><strong>{ts}Tied to funding{/ts}</strong></div>
    {literal}<% } %>{/literal}
    {literal}<% if (funding_notes) { %>{/literal}
      <div><strong>{ts}Notes{/ts}</strong>: <%- funding_notes %></div>
    {literal}<% } %>{/literal}
    </div>
  </div>

</script>