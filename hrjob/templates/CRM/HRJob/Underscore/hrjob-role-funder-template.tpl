{literal}
<script id="hrjob-role-funder-template" type="text/template">
  <td>
    <%= RenderUtil.funder({
      fid: fid,
      cid: cid
    }) %>
  </td>
  <td>
    <input id="hrjob-percent_pay_funder-<%= fid %>_<%= cid %>" name="percent_pay_funder-<%= fid %>_<%= cid %>" size="15" type="int" class="funderPerc"/>
  </td>
  <td><input class="pay_amount_to_funder" type="int" disabled="true" /></td>
  <td>
  </td>
</script>
{/literal}
