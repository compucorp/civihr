<script id="hrjob-pay-settings-template" type="text/template">
<form>
  <p>{ts}The constants defined in this box are used to estimate the amount of work done in each year.{/ts}</p>
  <p>{ts}For example, if the standard workload is 2,000 hour/year and a full-time employee earns $10/hour, then her estimated annual pay will be $20,000.{/ts}</p>

  <table>
    <thead>
      <tr>
        <th>Variable</th>
        <th>Amount</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <label for="hrjob-hours_per_year">{ts}Standard work-months per year{/ts}</label>
        </td>
        <td>
          <input id="hrjob-months_per_year" name="months_per_year" type="text" />
        </td>
      </tr>
      <tr>
        <td>
          <label for="hrjob-hours_per_year">{ts}Standard work-weeks per year{/ts}</label>
        </td>
        <td>
          <input id="hrjob-weeks_per_year" name="weeks_per_year" type="text" />
        </td>
      </tr>
      <tr>
        <td>
          <label for="hrjob-days_per_year">{ts}Standard work-days per year{/ts}</label>
        </td>
        <td>
          <input id="hrjob-days_per_year" name="days_per_year" type="text" />
        </td>
      </tr>
      <tr>
        <td>
          <label for="hrjob-hours_per_year">{ts}Standard work-hours per year{/ts}</label>
        </td>
        <td>
          <input id="hrjob-hours_per_year" name="hours_per_year" type="text" />
        </td>
      </tr>
    </tbody>
  </table>

  <%= RenderUtil.standardButtons() %>
</form>
</script>
