<script id="hrjob-hour-summary-template" type="text/template">
  <h3>{ts}Hours{/ts}</h3>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Hours Type{/ts}</div>
    <div class="crm-content"><%- FieldOptions.hours_type[hours_type] %></div>
  </div>

  <div class="crm-summary-row hrjob-needs-type">
    <div class="crm-label">{ts}Hours{/ts}</div>
    <div class="crm-content">
      <span name="hours_amount"/> {ts}per{/ts} <%- FieldOptions.hours_unit[hours_unit] %>
    </div>
  </div>

  <div class="crm-summary-row hrjob-needs-type">
    <div class="crm-label">{ts}FTE{/ts}</div>
    <div class="crm-content"><span name="hours_fte"/></div>
  </div>

</script>