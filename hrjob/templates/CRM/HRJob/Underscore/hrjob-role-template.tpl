<script id="hrjob-role-template" type="text/template">
  <td>
    <span class="hrjob-role-toggle">
      <span class="on-closed">[+]</span>
      <span class="on-open">[-]</span>
    </span>
  </td>
  <td>
    <strong class="hrjob-role-toggle" data-hrjobrole="title"></strong>
    <div class="toggle-role-form">
    {* FIXME: Extract to a new view/template *}
      <form>
        <table>
          <tbody>
          <tr>
            <td>{ts}Title{/ts}</td>
            <td colspan="3"><input data-hrjobrole="title" name="title" /></td>
          </tr>
          <tr>
            <td>{ts}Description{/ts}</td>
            <td colspan="3">
              <textarea data-hrjobrole="description" name="description"></textarea>
            </td>
          </tr>
          <tr>
            <td>{ts}Department{/ts}</td>
            <td>
              <input data-hrjobrole="department" name="department" />
            </td>
            <td>{ts}Hours/Week{/ts}</td>
            <td>
              <input data-hrjobrole="hours" name="hours" />
            </td>
          </tr>
          </tbody>
        </table>
      </form>
    </div>
  </td>
  <td>
    <strong data-hrjobrole="hours"></strong>
  </td>
  <td></td>
</script>
