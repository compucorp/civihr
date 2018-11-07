{* Main template for basic search (Find Contacts) *}
{include file="CRM/Contact/Form/Search/Intro.tpl"}
<div id="bootstrap-theme">
  <div class="panel panel-default">
    {* Top bar section *}
    {include file="CRM/HRCore/Form/Search/StaffDirectoryTopBarSection.tpl"}
    {* Filters section *}
    {include file="CRM/HRCore/Form/Search/StaffDirectoryFiltersSection.tpl"}
    {* Results section *}
    {include file="CRM/HRCore/Form/Search/StaffDirectoryResultsSection.tpl"}
  </div>
</div>
{*include file="CRM/common/searchJs.tpl"*}
