<?php

class CRM_Hrjobcontract_JobContractDates
{
    public static function setDates($contactId, $jobcontractId, $startDate, $endDate)
    {
        self::removeDates($jobcontractId);
        
        $insertSql = 'INSERT INTO civicrm_value_jobcontract_dates_13 SET '
            . 'entity_id = %1, '
            . 'contract_id = %2 ';
        $insertParams = array(
            1 => array($contactId, 'Integer'),
            2 => array($jobcontractId, 'Integer'),
        );
        if ($startDate && $startDate !== 'null')
        {
            $insertSql .= ', contract_start_date = %3 ';
            $insertParams[3] = array($startDate, 'String');
        }
        if ($endDate && $endDate !== 'null')
        {
            $insertSql .= ', contract_end_date = %4';
            $insertParams[4] = array($endDate, 'String');
        }
        CRM_Core_DAO::executeQuery(
            $insertSql,
            $insertParams
        );
        
        return true;
    }
    
    public static function removeDates($jobcontractId)
    {
        CRM_Core_DAO::executeQuery(
            'DELETE FROM civicrm_value_jobcontract_dates_13 WHERE contract_id = %1',
            array(
                1 => array($jobcontractId, 'Integer'),
            )
        );
        
        return true;
    }
}
