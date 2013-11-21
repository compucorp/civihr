<div class="crm-profile-name-hrprofile">
  {crmRegion name=profile-search-`$ufGroupName`}
  {* make sure there are some fields in the selector *}
    {if ! empty( $aaColumn ) || $isReset }
      <div class="crm-block crm-content-block">
        {* show profile listings criteria ($qill) *}
        {if $aaData}
          <div class="crm-search-results">
          {* Search criteria are passed to tpl in the $qill array *}
            {strip}
              <table>
              </table>
            {/strip}
          </div>
        {elseif ! $isReset}
          {include file="CRM/Contact/Form/Search/EmptyResults.tpl" context="Profile"}
        {/if}
      </div>
    {else}
      <div class="messages status no-popup">
        <div class="icon inform-icon"></div>
        {ts}No fields in this Profile have been configured to display as a result column in the search results table. Ask the site administrator to check the Profile setup.{/ts}
      </div>
    {/if}
  {/crmRegion}
</div>
{* crm-profile-name-NAME *}

{literal}
  <script type="text/javascript">
    var result = {/literal}{$aaData}{literal};
    var columns = {/literal}{$aaColumn}{literal};
    cj( function() {
      cj('table').dataTable( {
        "aoColumns": columns,
        "aaData": result,
        "sPaginationType": "full_numbers"
      });
    });
 </script>
{/literal} 

<div class="clear"></div>