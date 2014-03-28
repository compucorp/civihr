{literal}
var listTpl = [
  '<div class="description">',
    'Click on the job position for more information.',
  '</div>',
  '<table class="selector">',
    '<tr class="columnheader">',
      '<th>{/literal}{ts}Job Position{/ts}{literal}</th>',
      '<th>{/literal}{ts}Location{/ts}{literal}</th>',
      '<th>{/literal}{ts}Salary{/ts}{literal}</th>',
      '<th>{/literal}{ts}Application Dates{/ts}{literal}</th>',
      '<th></th>',
    '</tr>',
    '<% _.each(rc, function(leaveInfo, actId) { %>',
      '<tr id="rowid<%- leaveInfo.id %>" class="crm-hrvacancy-id_<%- leaveInfo.id %>">',
        '<td class="crm-job_position"><a class="hr-job-position-link" href="<%- leaveInfo.positionLink %>"><%- leaveInfo.position %></a></td>',
        '<td class="crm-location"><%- leaveInfo.location %></td>',
        '<td class="crm-salary"><%- leaveInfo.salary %></td>',
        '<td class="crm-application_dates"><%- leaveInfo.startDate %> - <%- leaveInfo.endDate %></td>',
        '<td><a class="hr-job-apply-link" href="<%- leaveInfo.apply %>">Apply Now</a></td>',
      '</tr>',
    '<% })%>',
  '</table>'
].join("\n");
var listTemplate = c_.template(listTpl);

var infoTpl = [
  "<table>",
    "<tr><td>{/literal}{ts}Salary{/ts}{literal}</td><td><%- rc.salary %></td></tr>",
    "<tr><td>{/literal}{ts}Location{/ts}{literal}</td><td><%- rc.location %></td></tr>",
    "<tr><td>{/literal}{ts}Description{/ts}{literal}</td><td><%- rc.description %></td></tr>",
    "<tr><td>{/literal}{ts}Benefits{/ts}{literal}</td><td><%- rc.benefits %></td></tr>",
    "<tr><td>{/literal}{ts}Requirements{/ts}{literal}</td><td><%- rc.requirements %></td></tr>",
  "</table>"
].join("\n");
var infoTemplate = c_.template(infoTpl);
{/literal}