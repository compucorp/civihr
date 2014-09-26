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
          <label for="hrjob-work_months_per_year">{ts}Standard work-months per year{/ts}</label>
        </td>
        <td>
          <input id="hrjob-work_months_per_year" name="work_months_per_year" type="text" />
        </td>
      </tr>
      <tr>
        <td>
          <label for="hrjob-work_weeks_per_year">{ts}Standard work-weeks per year{/ts}</label>
        </td>
        <td>
          <input id="hrjob-work_weeks_per_year" name="work_weeks_per_year" type="text" />
        </td>
      </tr>
      <tr>
        <td>
          <label for="hrjob-work_days_per_week">{ts}Standard work-days per week{/ts}</label>
        </td>
        <td>
          <input id="hrjob-work_days_per_week" name="work_days_per_week" type="text" />
        </td>
      </tr>
      <tr>
        <td>
          <label for="hrjob-work_days_per_month">{ts}Standard work-days per month{/ts}</label>
        </td>
        <td>
          <input id="hrjob-work_days_per_month" name="work_days_per_month" type="text" />
        </td>
      </tr>
      <tr>
        <td>
          <label for="hrjob-work_hour_per_day">{ts}Standard work-hours per day{/ts}</label>
        </td>
        <td>
          <input id="hrjob-work_hour_per_day" name="work_hour_per_day" type="text" />
        </td>
      </tr>
    </tbody>
  </table>

  <%= RenderUtil.standardButtons() %>
</form>
</script>
