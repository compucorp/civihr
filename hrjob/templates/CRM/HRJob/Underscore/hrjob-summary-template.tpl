<script id="hrjob-summary-template" type="text/template">
  <h3>
    {literal}
    <% if (contract_type) { %>
    <span name="contract_type"></span>:
    <% } %>
    <span name="position"></span>

    <% if (is_primary == 1) { %>
      (<em>{/literal}{ts}Primary Job{/ts}{literal}</em>)
    <% } %>
    {/literal}
  </h3>
  <table>
    <tbody>
    <tr>
      <td>
        <div class="hrjob-summary-general"></div>
        <div class="hrjob-summary-funding"></div>
      </td>
      <td>
        <div class="crm-summary-row">
          <div class="crm-label">{ts}Health Insurance{/ts}</div>
          <div class="crm-content hrjob-summary-health"></div>
        </div>

        <div class="crm-summary-row">
          <div class="crm-label">{ts}Life Insurance{/ts}</div>
          <div class="crm-content hrjob-summary-life"></div>
        </div>

        <div class="crm-summary-row">
          <div class="crm-label">{ts}Hours{/ts}</div>
          <div class="crm-content hrjob-summary-hour"></div>
        </div>

        <div class="crm-summary-row">
          <div class="crm-label">{ts}Leave{/ts}</div>
          <div class="crm-content hrjob-summary-leave"></div>
        </div>

        <div class="crm-summary-row">
          <div class="crm-label">{ts}Pay{/ts}</div>
          <div class="crm-content hrjob-summary-pay"></div>
        </div>

        <div class="crm-summary-row">
          <div class="crm-label">{ts}Pension{/ts}</div>
          <div class="crm-content hrjob-summary-pension"></div>
        </div>

      </td>
    </tr>
    </tbody>
  </table>

  <div class="hrjob-summary-role"></div>
</script>
