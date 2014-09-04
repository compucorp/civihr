<script id="hrjob-role-summary-row-template" type="text/template">
  <td>
    {literal}<%= RenderUtil.toggle({className: 'hrjob-role-toggle'}) %>{/literal}
  </td>
  <td>
    <strong class="hrjob-role-toggle" data-hrjobrole-row="title"></strong>
    <div class="toggle-role-form">
    </div>
  </td>
  <td>
      {literal}<%
      _.each(funderMulti, function(funderId){  %>{/literal}
      <div><a href="#" class="hrjob-funder" id="hrjob-role-funder-{literal}<%- funderId %>{/literal}"/></div><hr/>
    {literal}<% }); %>{/literal}
  </td>
  <td>
    <strong data-hrjobrole-row="percent_pay_role"></strong>
  </td>
  <td>
    <strong data-hrjobrole-row="hours"></strong>
  </td>

</script>
