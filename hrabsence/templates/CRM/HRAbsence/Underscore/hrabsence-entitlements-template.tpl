{literal}
<script id="hrabsence-entitlements-template" type="text/template">
  <h3>Annual Entitlements</h3>
  <table>
    <thead>
      <tr class="hrabsence-annualentitlements-period-header">
        <th>{/literal}{ts}Period{/ts}{literal}</th>
        <% _.each(selectedAbsences, function(absenceId, absencetypeId) { %>
        <th><%- FieldOptions.activity_type_id[absencetypeId] %></th>
        <% }); %> <!-- end foreach absence type -->
      </tr>
    </thead>
    <tbody>
    <% _.each(selectedPeriod, function(period_id){ %>
      <tr class="hrabsence-list-item" data-period-id="<%- periods[period_id].id %>">
        <td class="hrabsence-annualentitlements-period"><%- periods[period_id].title %></td>
        <% _.each(selectedAbsences, function(absenceId, absencetypeId) { %>
          <td>
            <input
              type="text"
              size="5"
              class="hrabsence-annualentitlement-input"
              data-period-id=<%- periods[period_id].id %>
              data-absence-type-id=<%- absencetype[absencetypeId] %>
              />
          </td>
        <% }); %> <!-- end foreach absence type -->
      </tr>
    <% }); %> <!-- each period -->
    </tbody>
  </table>
</table>
</script>
{/literal}