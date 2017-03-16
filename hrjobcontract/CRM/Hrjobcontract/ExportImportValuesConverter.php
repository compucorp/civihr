<?php

class CRM_Hrjobcontract_ExportImportValuesConverter
{
    static private $_singleton = NULL;

    protected $_annualOptions = array();
    protected $_annualOptionsFlipped = array();
    protected $_contractTypeOptions = array();
    protected $_contractTypeOptionsFlipped = array();
    protected $_hoursLocationOptions = array();
    protected $_hoursTypeOptions = array();
    protected $_hoursTypeOptionsFlipped = array();
    protected $_endReasonOptions = array();
    protected $_endReasonOptionsFlipped = array();
    protected $_leaveTypes = array();
    protected $_leaveTypesFlipped = array();
    protected $_locationOptions = array();
    protected $_locationOptionsFlipped = array();
    protected $_payCycleOptions = array();
    protected $_payCycleOptionsFlipped = array();
    protected $_payScaleOptions = array();
    protected $_pensionTypeOptions = array();
    protected $_pensionTypeOptionsFlipped = array();
    protected $_insurancePlanType = [];

    private function __construct()
    {
        $this->_initialize();
    }

    /**
     * singleton function used to manage this object
     *
     * @return CRM_Hrjobcontract_ExportImportValuesConverter
     * @static
     */
    static function &singleton()
    {
        if (self::$_singleton === NULL)
        {
            self::$_singleton = new self;
        }
        return self::$_singleton;
    }

    protected function _initialize()
    {
        // annual benefits options:
        CRM_Core_OptionGroup::getAssoc('hrjc_benefit_name', $this->_annualOptions['benefit']['name']);
        CRM_Core_OptionGroup::getAssoc('hrjc_benefit_type', $this->_annualOptions['benefit']['type']);
        foreach ($this->_annualOptions['benefit']['name']['label'] as $key => $value)
        {
            $this->_annualOptionsFlipped['benefit']['name'][$value] = $key;
        }
        foreach ($this->_annualOptions['benefit']['type']['label'] as $key => $value)
        {
            $this->_annualOptionsFlipped['benefit']['type'][$value] = $key;
        }

        // annual deductions options:
        CRM_Core_OptionGroup::getAssoc('hrjc_deduction_name', $this->_annualOptions['deduction']['name']);
        CRM_Core_OptionGroup::getAssoc('hrjc_deduction_type', $this->_annualOptions['deduction']['type']);
        foreach ($this->_annualOptions['deduction']['name']['label'] as $key => $value)
        {
            $this->_annualOptionsFlipped['deduction']['name'][$value] = $key;
        }
        foreach ($this->_annualOptions['deduction']['type']['label'] as $key => $value)
        {
            $this->_annualOptionsFlipped['deduction']['type'][$value] = $key;
        }

        // contract type options:
        $contractTypeOptions = array();
        CRM_Core_OptionGroup::getAssoc('hrjc_contract_type', $contractTypeOptions, true);
        foreach ($contractTypeOptions as $contractType) {
            $this->_contractTypeOptions[$contractType['value']] = $contractType;
            $this->_contractTypeOptionsFlipped[$contractType['label']] = $contractType['value'];
        }

        // end reason type options:
        CRM_Core_OptionGroup::getAssoc('hrjc_contract_end_reason', $endReasons, true);
        foreach ($endReasons as $endReason) {
          $this->_endReasonOptions[$endReason['value']] = $endReason;
          $this->_endReasonOptionsFlipped[$endReason['label']] = $endReason['value'];
        }

        // hours location options:
        $hoursLocation = new CRM_Hrjobcontract_BAO_HoursLocation();
        $hoursLocation->find();
        while ($hoursLocation->fetch()) {
            $this->_hoursLocationOptions[$hoursLocation->id] = (array)$hoursLocation;
        }

        // hours type options:
        $hoursTypeOptions = array();
        CRM_Core_OptionGroup::getAssoc('hrjc_hours_type', $hoursType, true);
        foreach ($hoursType as $hourType) {
            $this->_hoursTypeOptions[$hourType['value']] = $hourType;
            $this->_hoursTypeOptionsFlipped[$hourType['label']] = $hourType['value'];
        }

        // leave types options:
        $absenceType = new CRM_HRAbsence_BAO_HRAbsenceType();
        $absenceType->find();
        while ($absenceType->fetch()) {
            $absenceTypeArray = (array)$absenceType;
            $this->_leaveTypes[$absenceType->id] = $absenceTypeArray;
            $this->_leaveTypesFlipped[$absenceTypeArray['title']] = $absenceType->id;
        }

        // location options:
        $locationOptions = array();
        CRM_Core_OptionGroup::getAssoc('hrjc_location', $locationOptions, true);
        foreach ($locationOptions as $location) {
            $this->_locationOptions[$location['value']] = $location;
            $this->_locationOptionsFlipped[$location['label']] = $location['value'];
        }

        // pay cycle options:
        $payCycleOptions = array();
        CRM_Core_OptionGroup::getAssoc('hrjc_pay_cycle', $payCycleOptions, true);
        foreach ($payCycleOptions as $payCycle) {
            $this->_payCycleOptions[$payCycle['value']] = $payCycle;
            $this->_payCycleOptionsFlipped[$payCycle['label']] = $payCycle['value'];
        }

        // pay scale options:
        $payScale = new CRM_Hrjobcontract_BAO_PayScale();
        $payScale->find();
        while ($payScale->fetch()) {
            $this->_payScaleOptions[$payScale->id] = (array)$payScale;
        }

        // pension type options:
        $pensionTypeOptions = array();
        CRM_Core_OptionGroup::getAssoc('hrjc_pension_type', $pensionTypeOptions, true);
        foreach ($pensionTypeOptions as $pensionType) {
            $this->_pensionTypeOptions[$pensionType['value']] = $pensionType;
            $this->_pensionTypeOptionsFlipped[$pensionType['label']] = $pensionType['value'];
        }

        // Insurance Plan Type Options:
        $typeOptions = array();
        CRM_Core_OptionGroup::getAssoc('hrjc_insurance_plantype', $typeOptions, true);
        foreach ($typeOptions as $insuranceType) {
            $this->_insurancePlanType[$insuranceType['value']] = $insuranceType;
        }
    }

    public function export($entityName, $fieldName, $value)
    {
        $functionName = $entityName . '_' . $fieldName . '_export';
        if (is_callable(array($this, $functionName)))
        {
            return self::$functionName($value);
        }
        return $value;
    }

    public function import($entityName, $fieldName, $value)
    {
        $functionName = $entityName . '_' . $fieldName . '_import';
        if (is_callable(array($this, $functionName)))
        {
            return self::$functionName($value);
        }
        return $value;
    }

    public function contract_is_primary_export($value)
    {
        return (int)$value ? 'Yes' : 'No';
    }
    public function contract_is_primary_import($value)
    {
        return strtolower($value) === 'yes' ? 1 : 0;
    }

    public function details_contract_type_export($value)
    {
        return isset($this->_contractTypeOptions[$value]['label']) ? $this->_contractTypeOptions[$value]['label'] : $value;
    }
    public function details_contract_type_import($value)
    {
        return $this->_contractTypeOptionsFlipped[$value];
    }

    public function health_plan_type_export($value) {
      return isset($this->_insurancePlanType[$value]['label']) ? $this->_insurancePlanType[$value]['label'] : $value;
    }

    public function health_plan_type_life_insurance_export($value) {
      return isset($this->_insurancePlanType[$value]['label']) ? $this->_insurancePlanType[$value]['label'] : $value;
    }

    public function details_location_export($value)
    {
        return $this->_locationOptions[$value]['label'];
    }

    public function details_location_import($value)
    {
        if(!isset($this->_locationOptionsFlipped[$value])) {
            return null;
        }

        return $this->_locationOptionsFlipped[$value];
    }

    public function details_end_reason_export($value)
    {
      return isset($value) ? $this->_endReasonOptions[$value]['label'] : null;
    }
    public function details_end_reason_import($value)
    {
      return !empty($value) ? $this->_endReasonOptionsFlipped[$value] : null;
    }

    public function hour_hours_type_export($value)
    {
        return isset($value) ? $this->_hoursTypeOptions[$value]['label'] : null;
    }
    public function hour_hours_type_import($value)
    {
        return !empty($value) ? $this->_hoursTypeOptionsFlipped[$value] : null;
    }

    public function hour_location_standard_hours_export($value)
    {
        return isset($value) ? $this->_hoursLocationOptions[$value]['location'] . ' - ' .
            $this->_hoursLocationOptions[$value]['standard_hours'] . ' hours per ' .
            $this->_hoursLocationOptions[$value]['periodicity'] : null;
    }
    public function hour_location_standard_hours_import($value)
    {
        if (!isset($value))
        {
            return null;
        }
        $keys = preg_split("/( - | hours per )/", $value);
        foreach ($this->_hoursLocationOptions as $key => $hoursLocationOption)
        {
            if ($hoursLocationOption['location'] === $keys[0] &&
                $hoursLocationOption['standard_hours'] === $keys[1] &&
                $hoursLocationOption['periodicity'] === $keys[2])
            {
                return $key;
            }
        }
        return $value;
    }

    public function leave_leave_amount_export($value)
    {
        $leaves = explode(',', $value);
        $output = array();
        foreach ($leaves as $leave)
        {
            list($typeId, $leaveAmount) = explode(':', $leave);
            $output[] = $this->_leaveTypes[$typeId]['title'] . ': ' . $leaveAmount;
        }
        return implode(', ', $output);
    }
    public function leave_leave_amount_import($value)
    {
        if (is_array($value) && isset($value[0]['leave_amount']))
        {
            $value = $value[0]['leave_amount'];
        }

        $leaves = explode(',', $value);
        $output = array();
        foreach ($leaves as $leave)
        {
            list($typeTitle, $leaveAmount) = explode(':', $leave);
            $output[$this->_leaveTypesFlipped[trim($typeTitle)]] = trim($leaveAmount);
        }

        return $output;
    }

    public function leave_leave_type_export($value)
    {
        $typeIds = explode(',', $value);
        $typeTitles = array();
        foreach ($typeIds as $typeId)
        {
            $typeTitles[] = $this->_leaveTypes[$typeId]['title'];
        }
        return implode(', ', $typeTitles);
    }
    public function leave_leave_type_import($value)
    {
        //return isset($value) ? $this->_leaveTypesFlipped[$value] : null;
        return null;
    }

    public function pay_annual_benefits_export($value)
    {
        return isset($value) ? $this->_getAnnualReadableValues('benefit', $value) : null;
    }
    public function pay_annual_benefits_import($value)
    {
        return !empty($value) ? $this->_getAnnualValues('benefit', $value) : null;
    }

    public function pay_annual_deductions_export($value)
    {
        return isset($value) ? $this->_getAnnualReadableValues('deduction', $value) : null;
    }
    public function pay_annual_deductions_import($value)
    {
        return !empty($value) ? $this->_getAnnualValues('deduction', $value) : null;
    }

    public function pay_pay_cycle_export($value)
    {
        return isset($value) ? $this->_payCycleOptions[$value]['label'] : null;
    }
    public function pay_pay_cycle_import($value)
    {
        return !empty($value) ? $this->_payCycleOptionsFlipped[$value] : null;
    }

    public function pay_pay_is_auto_est_export($value)
    {
        return (int)$value ? 'Yes' : 'No';
    }
    public function pay_pay_is_auto_est_import($value)
    {
        return strtolower($value) === 'yes' ? 1 : 0;
    }

    public function pay_is_paid_export($value)
    {
        return (int)$value ? 'Yes' : 'No';
    }
    public function pay_is_paid_import($value)
    {
        return strtolower($value) === 'yes' ? 1 : 0;
    }

    public function pay_pay_scale_export($value)
    {
        $result = '';
        if (isset($value)) {
            $result = $this->_payScaleOptions[$value]['pay_scale'];
            if (!empty($this->_payScaleOptions[$value]['currency'])) {
                $result .= ' - ' . 
                $this->_payScaleOptions[$value]['currency'] . ' ' .
                $this->_payScaleOptions[$value]['amount'] . ' per ' .
                $this->_payScaleOptions[$value]['periodicity'];
            }
        }
        return $result;
    }
    
    /**
     * Obtains id for given pay scale. If given payscale does not exist in DB,
     * it will attempt to create it. If given payscale follows the format
     * "%s - %s %d per %s", the information will be used to populate currency, 
     * amount and periodicity fields for the pay scale.  Otherwise it will just 
     * add it with the given label and leave all other values empty.
     * 
     * @param string $value
     *   String for pay scale / grade that needs to be imported
     * 
     * @return int
     *   ID of given pay scale / grade
     */
    public function pay_pay_scale_import($value) {
      if (!isset($value)) {
        return null;
      }

      $payScale = trim(substr($value, 0, (strrpos($value, '-'))));
      $payData = trim(substr($value, (strrpos($value, '-') + 1)));
      $parts = explode(' ', $payData);

      if (count($parts) === 4 && !empty($payScale)) {
        $payScaleParams = [
          'pay_scale' => $payScale,
          'currency' => $parts[0],
          'amount' => $parts[1],
          'periodicity' => $parts[3]
        ];
      } else {
        $payScaleParams = [
          'pay_scale' => $value
        ];
      }

      $result = civicrm_api3('HRPayScale', 'get', array_merge(
        ['sequential' => 1],
        $payScaleParams
      ));

      return ($result['count'] > 0) ? array_shift($result['values'])['id'] : $this->createPayScale($payScaleParams);      
    }

    /**
     * Creates pay scale using given parameters.
     * 
     * @param array $params
     *   Fields that are to be set for new pay scale.
     * 
     * @return int
     *   ID of created pay scale / grade
     */
    private function _createPayScale($params) {
      $defaultParams = ['sequential' => 1, 'is_active' => 1];

      $result = civicrm_api3('HRPayScale', 'create', array_merge($defaultParams, $params));
      $this->_payScaleOptions[$result['id']] = $params;

      return $result['id'];
    }

    public function pension_is_enrolled_export($value)
    {
        $result = '';
        switch ((int)$value)
        {
            case 0:
                $result = 'No';
            break;
            case 1:
                $result = 'Yes';
            break;
            case 2:
                $result = 'Opted out';
            break;
        }
        return $result;
    }
    public function pension_is_enrolled_import($value)
    {
        $result = null;
        switch (strtolower($value))
        {
            case 'no':
                $result = 0;
            break;
            case 'yes':
                $result = 1;
            break;
            case 'opted out':
                $result = 2;
            break;
        }
        return $result;
    }

    public function pension_pension_type_export($value)
    {
        return $this->_pensionTypeOptions[$value]['label'];
    }
    public function pension_pension_type_import($value)
    {
        return $this->_pensionTypeOptionsFlipped[$value];
    }

    public function role_location_export($value)
    {
        return $this->details_location_export($value);
    }
    public function role_location_import($value)
    {
        return $this->details_location_import($value);
    }

    protected function _getAnnualReadableValues($field, $json)
    {
        $list = json_decode($json, true);
        $output = '';
        if (!empty($list))
        {
            foreach ($list as $row)
            {
                if (empty($row))
                {
                    continue;
                }
                $output .= 'name: ' . $this->_annualOptions[$field]['name']['name'][$row['name']] . ', ';
                $output .= 'type: ' . $this->_annualOptions[$field]['type']['name'][$row['type']] . ', ';
                $output .= 'amount pct: ' . $row['amount_pct'] . ', ';
                $output .= 'amount abs: ' . $row['amount_abs'] . '; ';
            }
        }

        return $output;
    }

    protected function _getAnnualValues($field, $value)
    {
        if (empty($value))
        {
            return null;
        }
        $outputArray = array();
        $rows = explode(';', $value);
        foreach ($rows as $row)
        {
            $row = trim($row);
            if (empty($row))
            {
                continue;
            }
            $outputRow = array();
            $columns = explode(', ', $row);
            foreach ($columns as $column)
            {
                if (empty($column))
                {
                    continue;
                }
                $pair = explode(': ', $column);
                $k = $pair[0];
                $v = isset($pair[1]) ? $pair[1] : '';
                switch ($k)
                {
                    case 'amount pct':
                        $outputRow['amount_pct'] = $v;
                    break;
                    case 'amount abs':
                        $outputRow['amount_abs'] = $v;
                    break;
                    default:
                        $outputRow[$k] = $this->_annualOptionsFlipped[$field][$k][$v];
                    break;
                }
            }
            $outputArray[] = $outputRow;
        }

        return $outputArray;
    }

    public function getContactByLookup($data)
    {
        $contactId = null;

        // external_identifier:
        if (!empty($data['external_identifier']) && !$contactId) {
          $checkCid = new CRM_Contact_DAO_Contact();
          $checkCid->external_identifier = $data['external_identifier'];
          $checkCid->find(TRUE);
          if (!empty($checkCid->id)) {
              $contactId = $checkCid->id;
          }
        }

        // email:
        if (!empty($data['email']) && !$contactId)
        {
            $checkEmail = new CRM_Core_BAO_Email();
            $checkEmail->email = $data['email'];
            $checkEmail->find(TRUE);
            if (!empty($checkEmail->contact_id))
            {
                $contactId = $checkEmail->contact_id;
            }
        }

        // id:
        if (!empty($data['id']) && !$contactId) {
          $checkId = new CRM_Contact_DAO_Contact();
          $checkId->id = $data['id'];
          $checkId->find(TRUE);
          if (!empty($checkId->id)) {
              $contactId = $checkId->id;
          }
        }

        return $contactId;
    }
}
