<div id="bootstrap-theme" class="crm-leave-and-absences-list-block">
  <div id="calculation-details">
    <p>For each contract the default calculation is as follows:</p>

    <p>1) Calculate period Pro Rata: ( (<span class="base-contractual-entitlement">Base contractual entitlement</span>) * (<span class="working-days-to-work">No of working days to work</span> /
      <span class="working-days-in-period">No of working days in period</span>) ) = (Period pro rata)
    <br>
    2) Add Public Holidays in period of contract = Total Entitlement for this contract</p>

    <p>Then we:</p>

    <p>3) Sum all Total entitlements for all contracts
    <br>
    4) Add brought forward days to the Total entitlements for all contracts = "Period Entitlement".</p>

    {assign var=contractsCalculations value=$calculation->getContractEntitlementCalculations()}
    {foreach from=$contractsCalculations item=contractCalculation name=contractsCalculation}

      {assign var=number value=$smarty.foreach.contractsCalculation.iteration}
      {assign var=startDate value=$contractCalculation->getContractStartDate()|crmDate}
      {assign var=endDate value=$contractCalculation->getContractEndDate()|crmDate}
      {assign var=contractualEntitlement value=$contractCalculation->getContractualEntitlement()}
      {assign var=workingDaysToWork value=$contractCalculation->getNumberOfWorkingDaysToWork()}
      {assign var=workingDays value=$contractCalculation->getNumberOfWorkingDays()}
      {assign var=proRata value=$contractCalculation->getProRata()|string_format:"%.2f"}
      {assign var=publicHolidays value=$contractCalculation->getNumberOfPublicHolidaysInEntitlement()}

      <div>
        <p>
          Contract {$number}: {$startDate} - {$endDate}
          <br>
          1) ( (<span class="base-contractual-entitlement">{$contractualEntitlement}</span>) * (<span class="working-days-to-work">{$workingDaysToWork}</span> /
              <span class="working-days-in-period">{$workingDays}</span>) ) = {$proRata-$publicHolidays}
          <br>
          2) ({$proRata-$publicHolidays}) + ({$publicHolidays}) = <span class="contract-{$number}-pro-rata">{$proRata}</span>
        </p>
      </div>
    {/foreach}

    Total:
    {assign var=periodProRata value=$calculation->getProRata()}
    {assign var=periodPublicHolidays value=$calculation->getNumberOfPublicHolidaysInEntitlement()}
    {assign var=broughtForward value=$calculation->getBroughtForward()}
    {assign var=periodEntitlement value=$calculation->getProposedEntitlement()}
    <p>
      3) {$proRataCalculationDescription} (Rounded up to the nearest half day)
      <br>
      4) <span class="calculation-pro-rata">{$periodProRata}</span> + {$broughtForward} = Period entitlement: {$periodEntitlement}
    </p>
  </div>
</div>
