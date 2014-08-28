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
      {literal}<% if (notice_amount || notice_unit) { %>{/literal}
      <div><strong>{ts}Notice Period from Employer{/ts}</strong>:
        <span name="notice_amount" />
        <%- FieldOptions.notice_unit[notice_unit] %>
      </div>
      {literal}<% } %>{/literal}
      {literal}<% if (notice_amount_employee || notice_unit_employee) { %>{/literal}
      <div><strong>{ts}Notice Period from Employee{/ts}</strong>:
        <span name="notice_amount_employee" />
        <%- FieldOptions.notice_unit_employee[notice_unit_employee] %>
      </div>
      {literal}<% } %>{/literal}
    </div>
  </div>

 <div class="crm-summary-row">
    <div class="crm-label">{ts}Contract File{/ts}</div>
    <div class="crm-content"><div id="contract_file"> </div>
    </div>
 </div>
</script>
