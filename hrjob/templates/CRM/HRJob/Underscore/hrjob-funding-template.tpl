{literal}
<script id="hrjob-funding-template" type="text/template">
<% if (!_.isEmpty(rolesInfo)) {%>
  <table class="funding-role-info-table">
    <thead>
      <tr class="hrjob-funding-role-header">
        <th>{/literal}Role Title(s){literal}</th>
        <th>{/literal}Name of the Funder(s){literal}</th>
        <th>{/literal}Percent of pay assigned to Funder{literal}</th>
      </tr>
    </thead>
    <tbody>
      <% _.each(rolesInfo, function(roleInfo, roleId) { %>
        <tr class="hrjob-funding-list-item">
          <td rowspan="<%= roleInfo.rowspan %>" class="hrjob-funding-role-position-<%= roleId %>"><%- roleInfo.position %></td>
          <% _.each(roleInfo.funderInfo, function(funderVal, funderId){  %>
            <td class="hrjob-funding-role-funders">
              <div><a href="#" class="hrjob-funding-role-funder" id="hrjob-role-funder-<%- funderId %>"/></div><hr/>
            </td>
            <td class="hrjob-funding-role-percent-assigned-toRole"><%- funderVal %></td>
            </tr><tr>
          <% }) %>
        </tr>
      <% }); %>
    </tbody>
  </table>
<% } %>
<form>
  <h3>{/literal}{ts}Funding{/ts}{literal}</h3>
  <div class="crm-summary-row">
    <div class="crm-label">
      <label for="hrjob-funding_notes">{/literal}{ts}Funding Notes{/ts}{literal}</label>
    </div>
    <div class="crm-content">
      <textarea id="hrjob-funding_notes" name="funding_notes"></textarea>
    </div>
  </div>

  <% if (!isNewDuplicate) { %>
  <button class="crm-button standard-save">{/literal}{ts}Save{/ts}{literal}</button>
  <% } else { %>
  <button class="crm-button standard-save">{/literal}{ts}Save New Copy{/ts}{literal}</button>
  <% } %>
  <button class="crm-button standard-reset">{/literal}{ts}Reset{/ts}{literal}</button>
</form>
</script>
{/literal}
