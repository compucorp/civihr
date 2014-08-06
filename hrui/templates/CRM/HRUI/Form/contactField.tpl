{* template block that contains the new field for contact*}
<span id='govID'><br/>
  <span>{$form.GovernmentId.label|crmAddClass:optGovlabel}</span>
  <span>{$form.govTypeOptions.html|crmAddClass:optGovType}</span>
  <span>{$form.GovernmentId.html|crmAddClass:optGovText}</span>
</span>