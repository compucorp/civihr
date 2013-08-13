<script id="hrjob-leave-table-template" type="text/template">
<form>
  <h3>
    {ts}Leave Entitlement{/ts}
    {literal}<% if (!isNew) { %> {/literal}
    <a class="css_right hrjob-revision-link" data-table-name="civicrm_hrjob_leave" href="#" title="{ts}View Revisions{/ts}">(View Revisions)</a>
    {literal}<% } %>{/literal}
  </h3>

  <table class="hrjob-leave-table">
    <thead>
    <tr>
      <th>{ts}Leave Type{/ts}</th>
      <th>{ts}Days per Year{/ts}</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
  </table>

  <%= RenderUtil.standardButtons() %>
</form>
</script>
