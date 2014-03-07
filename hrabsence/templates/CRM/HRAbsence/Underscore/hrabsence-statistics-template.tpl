{literal}
<script id="hrabsence-statistics-template" type="text/template">
  <table>
    <thead>
      <tr class="hrabsence-statistics-header" >
        <th>{/literal}{ts}Period{/ts}{literal}</th>
        <th>{/literal}{ts}Leave Type{/ts}{literal}</th>
        <th>{/literal}{ts}Entitlement{/ts}{literal}</th>
        <th>{/literal}{ts}Requested{/ts}{literal}</th>
        <th>{/literal}{ts}Approved{/ts}{literal}</th>
        <th>{/literal}{ts}Balance{/ts}{literal}</th>
      </tr>
    </thead>
    <tbody>
    <% _.each(stats, function(absence){ %>
        <tr class="hrabsence-list-item" data-statistics-id="<%- absence.period_id %>-<%- absence.activity_type_id %>">
          <td class="hrabsence-statistics-period-desc"><%- FieldOptions.period_id[absence.period_id] %></td>
          <td class="hrabsence-statistics-leave-type"><%- FieldOptions.activity_type_id[absence.activity_type_id] %></td>
          <td class="hrabsence-statistics-entitlement"><%- CRM.HRAbsenceApp.formatFloat(absence.entitlement) %></td>
          <td class="hrabsence-statistics-request"><%- CRM.HRAbsenceApp.formatFloat(absence.requested) %></td>
          <td class="hrabsence-statistics-approve"><%- CRM.HRAbsenceApp.formatFloat(absence.approved) %></td>
          <td class="hrabsence-statistics-bal"><%- CRM.HRAbsenceApp.formatFloat(absence.balance) %></td>
        </tr>
    <% }); %>
    </tbody>
  </table>
</script>
{/literal}