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
      </td>
      <td>
        <div class="hrjob-summary-health"></div>
        <div class="hrjob-summary-hour"></div>
        <div class="hrjob-summary-leave"></div>
        <div class="hrjob-summary-pay"></div>
        <div class="hrjob-summary-pension"></div>
      </td>
    </tr>
    </tbody>
  </table>

  <div class="hrjob-summary-role"></div>
</script>
