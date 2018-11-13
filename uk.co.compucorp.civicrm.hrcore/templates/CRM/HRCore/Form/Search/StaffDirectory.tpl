{* This input is needed to flush the selections after submit *}
<input type="hidden" name="_qf_Custom_refresh" value="true"/>
<div id="bootstrap-theme">
  <div class="staff-directory panel panel-default">
    {* Top bar section *}
    {include file="CRM/HRCore/Form/Search/StaffDirectoryTopBarSection.tpl"}
    {* Filters section *}
    {include file="CRM/HRCore/Form/Search/StaffDirectoryFiltersSection.tpl"}
    {* Results section *}
    {include file="CRM/HRCore/Form/Search/StaffDirectoryResultsSection.tpl"}
  </div>
</div>
