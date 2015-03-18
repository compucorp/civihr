{literal}
<script id="hrabsence-list-template" type="text/template">
  <table>
    <thead>
      <tr>
        <th>{/literal}{ts}Description{/ts}{literal}</th>
        <th>{/literal}{ts}Status{/ts}{literal}</th>
         <% _.each(active_activity_types, function(actId) { %>
        <th><%- FieldOptions.activity_type_id[actId] %></th>
        <% }); %>
      </tr>
    </thead>
    <tbody>
    <% _.each(absences_by_period, function(absences, period_id){ %>
      <tr class="hrabsence-list-period-header" data-period-id="<%- period_id %>">
        <th data-period-id="<%- period_id %>" colspan="<%= 2 + active_activity_types.length %>"><%- periods[period_id].title %></th>
      </tr>

      <tr class="hrabsence-list-entitlement" data-period-id="<%- period_id %>">
        <td>{/literal}{ts}Entitlement{/ts}{literal}</td>
        <td></td>
        <% _.each(active_activity_types, function(actId) { %>
        <td>
          <%
            var entitlement = entitlementCollection.findByTypeAndPeriod(
              absenceTypeCollection.findByDebitTypeId(actId),
              period_id);
            if (entitlement) { %><%- entitlement.getFormattedAmount() %><% }
          %>
        </td>
        <% }); %>
      </tr>

      <% _.each(absences, function(model) { %>
      <tr class="hrabsence-list-item" data-period-id="<%- period_id %>">
        <td class="hrabsence-list-desc">
          <a href="#" class="hrabsence-open" data-activity="<%= model.get('id') %>">
            <%- FieldOptions.activity_type_id[model.get('activity_type_id')] %>
            <% if (model.get('absence_range').low && model.get('absence_range').low == model.get('absence_range').high) { %>
              (<%- CRM.HRAbsenceApp.moment(model.get('absence_range').low).format('MMM D, YYYY') %>)
            <% } else if (model.get('absence_range').low) { %>
              (<%- CRM.HRAbsenceApp.moment(model.get('absence_range').low).format('MMM D, YYYY') %>
              -
              <%- CRM.HRAbsenceApp.moment(model.get('absence_range').high).format('MMM D, YYYY') %>)
            <% } %>
          </a>
        </td>
        <td><%- FieldOptions.status_id[model.get('status_id')] %></td>
        <% _.each(active_activity_types, function(actId) { %>
        <td data-duration-actid="<%- actId %>"><%- (actId == model.get('activity_type_id')) ? model.getFormattedDuration() : '' %></td>
        <% }); %>
      </tr>
      <% }); %><!-- each model -->

      <tr class="hrabsence-list-balance" data-period-id="<%- period_id %>">
      <td>{/literal}{ts}Balance{/ts}{literal}</td>
        <td></td>
        <% _.each(active_activity_types, function(actId) { %>
        <td>
          <%
          var balance = collection.calculateSubtotal(function(absence){
            return absence.get('activity_type_id') == actId && absence.getPeriodId() == period_id;
          });
          var entitlement = entitlementCollection.findByTypeAndPeriod(
            absenceTypeCollection.findByDebitTypeId(actId),
            period_id);
          if (entitlement) balance = balance + parseFloat(entitlement.getFormattedAmount());
          %>
          <%- CRM.HRAbsenceApp.formatFloat(balance) %>
        </td>
        <% }); %>
      </tr>

    <% }); %><!-- each period -->
    </tbody>
  </table>
</script>
{/literal}