{if $action eq 1 or $action eq 2 or $action eq 8}
  {include file="CRM/HRLeaveAndAbsences/Form/WorkPattern.tpl"}
{else}
  <div id="bootstrap-theme" class="crm-leave-and-absences-list-block">
  {if $rows}
    <div class="panel panel-default">
      {strip}
        {* handle enable/disable actions*}
        {include file="CRM/common/enableDisableApi.tpl"}
        <table cellpadding="0" cellspacing="0" border="0" class="table table-responsive hrleaveandabsences-entity-list">
          <thead class="sticky">
            <th>{ts}Label{/ts}</th>
            <th>{ts}Description{/ts}</th>
            <th>{ts}No. of Weeks{/ts}</th>
            <th>{ts}No. of Hours{/ts}</th>
            <th>{ts}Is Default{/ts}</th>
            <th>{ts}Order{/ts}</th>
            <th>{ts}Enabled/Disabled{/ts}</th>
            <th>&nbsp;</th>
          </thead>
          {foreach from=$rows item=row}
            {assign var='workPattern' value=$row.entity}
            <tr id="WorkPattern-{$workPattern->id}" class="crm-entity {if NOT $workPattern->is_active} disabled{/if}">
              <td data-field="title">{$workPattern->label|escape}</td>
              <td>{$workPattern->description|escape}</td>
              <td>{$workPattern->getNumberOfWeeks()}</td>
              <td>{if $workPattern->getNumberOfWeeks() gt 1} {ts}Various{/ts} {else} {$workPattern->getNumberOfHours()} {/if}</td>
              <td>
                {if $workPattern->is_default eq 1}
                  <i class="fa fa-check"></i>
                {/if}
              </td>
              <td>{$workPattern->weight}</td>
              <td>{if $workPattern->is_active eq 1} {ts}Enabled{/ts} {else} {ts}Disabled{/ts} {/if}</td>
              <td>{$row.action|replace:'xx':$workPattern->id}</td>
            </tr>
          {/foreach}
        </table>
      {/strip}
      {if $action ne 1 and $action ne 2}
        <div class="panel-footer clearfix">
          <div class="pull-right">
            <a href="{crmURL q="action=add&reset=1"}" class="btn btn-primary">
              <i class="fa fa-plus btn-icon"></i>
              <span>{ts}Add Work Pattern{/ts}</span>
            </a>
          </div>
        </div>
      {/if}
      </div>
      <script type="text/javascript">
        {literal}
          CRM.$(document).on('hrappready.list', function (event, app) {
            (new app.ListPage(CRM.$('.hrleaveandabsences-entity-list')));
          });
        {/literal}
      </script>
    {else}
      <div class="messages status no-popup">
        <div class="icon inform-icon"></div>
        {capture assign=crmURL}{crmURL q="action=add&reset=1"}{/capture}
        {ts 1=$crmURL}There are no Work Patterns entered. You can <a href='%1'>add one</a>.{/ts}
      </div>
    {/if}
  </div>
{/if}
