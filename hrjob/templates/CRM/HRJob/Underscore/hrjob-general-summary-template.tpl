<script id="hrjob-general-summary-template" type="text/template">

  <h3>{ts}General{/ts}</h3>

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
    <div class="crm-label">{ts}Level{/ts}</div>
    <div class="crm-content"><%- FieldOptions.level_type[level_type] %></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Manager{/ts}</div>
    <div class="crm-content"><span name="manager_contact_id"/></div>
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


  <h3>{ts}Funding{/ts}</h3>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Tied to Funding{/ts}</div>
    <div class="crm-content"><span name="is_tied_to_funding"/></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Funding Notes{/ts}</div>
    <div class="crm-content"><span name="funding_notes"/></div>
  </div>

</script>