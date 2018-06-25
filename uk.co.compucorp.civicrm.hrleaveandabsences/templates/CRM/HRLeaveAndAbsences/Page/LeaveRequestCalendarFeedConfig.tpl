{if $action eq 1 or $action eq 2 or $action eq 8}
  {include file="CRM/HRLeaveAndAbsences/Form/LeaveRequestCalendarFeedConfig.tpl"}
{else}
  {if $rows}
    <div id="bootstrap-theme" class="crm-leave-and-absences-list-block">
      <div class="alert alert-info">
        {ts}All Calendar feeds make some or all of your staff leave data public to the internet.
          Anyone with the appropriate feed link will be able to view the data available on the feed.
          This may include former employees or any other persons who your employee may share the calendar feed link with.
          Please consider carefully whether this will be suitable for your organisation.{/ts}
      </div>
      <div class="panel panel-default">
        {strip}
          {include file="CRM/common/enableDisableApi.tpl"}
          <table cellpadding="0" cellspacing="0" border="0" class="table table-responsive hrleaveandabsences-entity-list">
            <thead class="sticky">
            <th>{ts}Title{/ts}</th>
            <th>{ts}Timezone{/ts}</th>
            <th>{ts}Status{/ts}</th>
            <th>{ts}Actions{/ts}</th>
            <th>&nbsp;</th>
            </thead>
            {foreach from=$rows item=row}
              <tr id="LeaveRequestCalendarFeedConfig-{$row.id}" class="crm-entity {$row.class}{if NOT $row.is_active} disabled{/if}">
                <td data-field="title">{$row.title|escape}</td>
                <td>{$row.timezone}</td>
                <td>{if $row.is_active eq 1} {ts}Enabled{/ts} {else} {ts}Disabled{/ts} {/if}</td>
                <td>{$row.action|replace:'xx':$row.id}</td>
              </tr>
            {/foreach}
          </table>
        {/strip}

        {if $action ne 1 and $action ne 2}
          <div class="panel-footer clearfix">
            <div class="pull-right">
              <a href="{crmURL q="action=add&reset=1"}" class="button btn btn-primary pull-right">
                <i class="fa fa-plus"></i>
                <span>{ts}Add Calendar Feed{/ts}</span>
              </a>
            </div>
          </div>
        {/if}
      </div>
    </div>
  {literal}
    <script type="text/javascript">
      CRM.$(function () {
        var listPage = new CRM.HRLeaveAndAbsencesApp.ListPage(CRM.$('.hrleaveandabsences-entity-list'));
      });
    </script>
  {/literal}
  {else}
    <div class="alert alert-info">
      <div class="icon inform-icon"></div>
      {capture assign=crmURL}{crmURL q="action=add&reset=1"}{/capture}
      {ts 1=$crmURL}There are no Calendar Feeds entered. You can <a href='%1'>add one</a>.{/ts}
    </div>
  {/if}
{/if}
