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

{*
By default, the column widths in jQuery.dataTable have the uncanny tendency to change while typing in a search.

We cannot resolve this by setting a fixed width in CSS because the qty and size of columns change at runtime.

WORK-AROUND: Call jQuery.dataTable twice. The first time uses automatic width. We extract the automatically-computed
widths. The second time uses the same widths -- but sets them statically.
*}
{literal}
<script type="text/javascript">
CRM.$(function($){
  var result = {/literal}{$aaData}{literal};
  var columns = {/literal}{$aaColumn}{literal};
  $('table').dataTable( {
    "aoColumns": columns,
    "aaData": result,
    "sPaginationType": "full_numbers"
  });

  // get the width parameters
  var widthData = [];
  $( "table thead th", this ).each(function( index ) {
    var widthParam = this.style.width;
    var width = '{"sWidth":"'+ widthParam + '","aTargets":[' + index + ']}';
    widthData.push(width);
  });
  var width = '[' + widthData + ']';
  var responseResult = $.parseJSON(width);

  // Reconstruct the datatable
  $('table').dataTable( {
    "bDestroy": true,
    "aoColumns": columns,
    "aaData": result,
    "sPaginationType": "full_numbers",
    "aoColumnDefs": responseResult
  });
  $( '.crm-profile-name-hrprofile table').css( "table-layout",'fixed');
});
</script>
{/literal}

<div class="clear"></div>
