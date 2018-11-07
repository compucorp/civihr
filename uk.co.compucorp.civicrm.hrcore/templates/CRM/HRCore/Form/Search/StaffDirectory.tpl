{* Main template for basic search (Find Contacts) *}
{include file="CRM/Contact/Form/Search/Intro.tpl"}
<div id="bootstrap-theme">
  <div class="panel panel-default">
    {* Top bar *}
    {include file="CRM/HRCore/Form/Search/StaffDirectoryTopBarSection.tpl"}
    {* Filters section *}
    {include file="CRM/HRCore/Form/Search/StaffDirectoryFiltersSection.tpl"}
  </div>
</div>
<div class="crm-content-block">
{if $rowsEmpty}
  <div class="crm-results-block crm-results-block-empty">
    {include file="CRM/Contact/Form/Search/EmptyResults.tpl"}
  </div>
{elseif $rows}
  <div class="crm-results-block">
    {* Search request has returned 1 or more matching rows. *}
    {* This section handles form elements for action task select and submit *}
    <div class="crm-search-tasks">
      {if $taskFile}
        {if $taskContext}
          {include file=$taskFile context=$taskContext}
        {else}
          {include file=$taskFile}
        {/if}
      {else}
        {include file="CRM/Contact/Form/Search/ResultTasks.tpl"}
      {/if}
    </div>

    {* This section displays the rows along and includes the paging controls *}
    <div class="crm-search-results">
      {include file="CRM/HRCore/Form/Search/Selector.tpl"}
    </div>

  {* END Actions/Results section *}
  </div>
{else}
  <div class="spacer">&nbsp;</div>
{/if}
</div>
{*include file="CRM/common/searchJs.tpl"*}
