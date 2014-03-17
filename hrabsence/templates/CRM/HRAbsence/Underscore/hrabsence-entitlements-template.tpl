{literal}
<script id="hrabsence-entitlements-template" type="text/template">
  <% if (!_.isEmpty(contractEntitlements)) {%>
    <h3>Contract Entitlement</h3>
    <table class="contract-entitlement-table">
      <thead>
        <tr class="hrabsence-contractentitlements-header">
          <th>Position</th>
          <th>Start Date</th>
          <th>End Date</th>
          <% _.each(absencetype, function(absenceId, absencetypeId) { %>
            <th><%- FieldOptions.activity_type_id[absencetypeId] %></th>
          <% }); %> <!-- end foreach absence type -->
        </tr>
      </thead>
      <tbody>
        <% _.each(contractEntitlements, function(leaveInfo, jobId) { %>
          <tr class="hrabsence-list-item">
            <td class="hrabsence-contractentitlements-position"><%- leaveInfo.position %></td>
            <td class="hrabsence-contractentitlements-startDate"><%- leaveInfo.start_date %></td>
            <td class="hrabsence-contractentitlements-endDate"><%- leaveInfo.end_date %></td>
            <% _.each(absencetype, function(absenceId, absencetypeId) { %>
              <td class="hrabsence-contractentitlements-absence-<%- absenceId %>"><%- leaveInfo[absenceId] %></td>
            <% }); %>
          </tr>
        <% }); %>
      </tbody>
    </table>
  <% } %>
  <h3>Annual Entitlements</h3>
  <table class="annual-entitlement-table">
    <thead>
      <tr class="hrabsence-annualentitlements-period-header">
        <th>{/literal}{ts}Period{/ts}{literal}</th>
        <% _.each(selectedAbsences, function(absenceId, absencetypeId) {
          if (CRM.HRAbsenceApp.absenceTypeCollection.findDirection(absencetypeId) == -1) {
        %>
          <th><%- FieldOptions.activity_type_id[absencetypeId] %></th>
        <% }}); %> <!-- end foreach absence type -->
      </tr>
    </thead>
    <tbody>
    <% _.each(selectedPeriod, function(period_id){ %>
      <tr class="hrabsence-list-item" data-period-id="<%- periods[period_id].id %>">
        <td class="hrabsence-annualentitlements-period"><%- periods[period_id].title %></td>
        <% _.each(selectedAbsences, function(absenceId, absencetypeId) {
          if (CRM.HRAbsenceApp.absenceTypeCollection.findDirection(absencetypeId) == -1) {
        %>
          <td>
            <input
              type="text"
              readonly="readonly"
              size="5"
              class="hrabsence-annualentitlement-input crm-editable-enabled"
              data-period-id=<%- periods[period_id].id %>
              data-absence-type-id=<%- absencetype[absencetypeId] %>
              />
          </td>
        <% }}); %> <!-- end foreach absence type -->
      </tr>
    <% }); %> <!-- each period -->
    </tbody>
  </table>
</script>
{/literal}
