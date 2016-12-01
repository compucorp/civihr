<?php

echo "Script started.\n";

$timeStart = time();
$jobContracts = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_hrjobcontract WHERE deleted = 0 ORDER BY id ASC');
$i = 0;
while ($jobContracts->fetch()) {
    echo "Recalculating Absence Entitlement by Job Contract #{$jobContracts->id}...";
    CRM_HRAbsence_BAO_HRAbsenceEntitlement::recalculateAbsenceEntitlement($jobContracts->id);
    echo "OK.\n";
    $i++;
}
$timeEnd = time();

echo "{$i} Job Contracts processed in " . ($timeEnd - $timeStart) . " seconds.\n";
echo "Finished.\n";
