<?php

echo "Script started.\n";

$timeStart = time();
$contacts = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_contact WHERE contact_type = "Individual" ORDER BY id ASC');
$i = 0;
while ($contacts->fetch()) {
    echo "Recalculating Absence Entitlement for Contact #{$contacts->id}...";
    CRM_HRAbsence_BAO_HRAbsenceEntitlement::recalculateAbsenceEntitlementForContact($contacts->id);
    echo "OK.\n";
    $i++;
}
$timeEnd = time();

echo "{$i} Contacts processed in " . ($timeEnd - $timeStart) . " seconds.\n";
echo "Finished.\n";
