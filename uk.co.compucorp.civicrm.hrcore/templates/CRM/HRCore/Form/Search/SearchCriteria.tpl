{if $context EQ 'smog'}
  {capture assign=editTitle}{ts}Find Contacts within this Group{/ts}{/capture}
{elseif $context EQ 'amtg' AND !$rows}
  {capture assign=editTitle}{ts}Find Contacts to Add to this Group{/ts}{/capture}
{else}
  {capture assign=editTitle}{ts}Edit Search Criteria{/ts}{/capture}
{/if}
