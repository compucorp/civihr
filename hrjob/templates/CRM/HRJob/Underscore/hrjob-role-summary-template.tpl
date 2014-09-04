<script id="hrjob-role-summary-template" type="text/template">
  <div class="crm-summary-row">
    <div class="crm-label">{ts}Title{/ts}</div>
    <div class="crm-content"><span name="title"/></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Description{/ts}</div>
    <div class="crm-content"><span name="description" /></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Hours{/ts}</div>
    <div class="crm-content"><span name="hours"/></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Cost Center{/ts}</div>
    <div class="crm-content"><span name="cost_center"/></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Funder{/ts}</div>
    <div class="crm-content">
      {literal}<%
      _.each(funderMulti, function(funderId){  %>{/literal}
        <div><a href="#" class="hrjob-funder" id="hrjob-role-funder-{literal}<%- funderId %>{/literal}"/></div>
    {literal}<% }); %>{/literal}
    </div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Department{/ts}</div>
    <div class="crm-content"><%- FieldOptions.department[department] %></div>
  </div>

  {*
  <div class="crm-summary-row">
    <div class="crm-label">{ts}Functional Area{/ts}
    </div>
    <div class="crm-content"><span name="functional_area"/></div>
  </div>
  *}

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Location{/ts}</div>
    <div class="crm-content"><%- FieldOptions.location[location] %></div>
  </div>

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Manager{/ts}</div>
    <div class="crm-content"><a href="#" class="hrjob-manager_contact" /></div>
  </div>

  {*
  <div class="crm-summary-row">
    <div class="crm-label">{ts}Organization{/ts}</div>
    <div class="crm-content"><span name="organization"/></div>
  </div>
  *}

  <div class="crm-summary-row">
    <div class="crm-label">{ts}Region{/ts}</div>
    <div class="crm-content"><span name="region"/></div>
  </div>
</script>