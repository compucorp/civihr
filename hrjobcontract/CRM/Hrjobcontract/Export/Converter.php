<?php

use CRM_Hrjobcontract_ExportImportValuesConverter as ExportImportValuesConverter;

class CRM_Hrjobcontract_Export_Converter {

  /**
   * This method is based on CRM_Export_BAO_Export::writeCSVFromTable()
   * but adds check for hrjobcontract entities and runs values converting
   * on them so we get proper Job Contract entity values exported.
   *
   * @param $exportTempTable
   * @param $headerRows
   * @param $sqlColumns
   * @param $exportMode
   * @param null $saveFile
   * @param string $batchItems
   */
  public static function writeCSVFromTable($exportTempTable, $headerRows, $sqlColumns, $exportMode, $saveFile = NULL, $batchItems = '') {
    $converter = CRM_Hrjobcontract_ExportImportValuesConverter::singleton();
    $writeHeader = TRUE;
    $offset = 0;
    $limit = CRM_Export_BAO_Export::EXPORT_ROW_COUNT;
    $query = "SELECT * FROM {$exportTempTable}";
    $total = (int)CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) AS total FROM {$exportTempTable}");

    $getExportFileName = CRM_Export_BAO_Export::getExportFileName('csv', $exportMode);
    if ($exportMode == 'financial') {
      $getExportFileName = 'CiviCRM Contribution Search';
    }

    $leaveTypes = ExportImportValuesConverter::singleton()->getLeaveTypesFlipped();

    if (array_key_exists('hrjobcontract_leave_leave_amount', $sqlColumns)) {
      $newHeaderRows = [];
      foreach($headerRows as $headerRow) {
        $newHeaderRows[] = $headerRow;
        if($headerRow == 'Contract Leave Amount') {
          foreach ($leaveTypes as $type => $typeId) {
            $newHeaderRows[] = $type;
          }
        }
      }
      $headerRows = $newHeaderRows;
    }

    while ($offset < $total) {
      $limitQuery = $query . " LIMIT {$offset}, {$limit} ";
      $dao = CRM_Core_DAO::executeQuery($limitQuery);

      $componentDetails = array();
      while ($dao->fetch()) {
        $row = array();

        foreach ($sqlColumns as $column => $dontCare) {
          $row[$column] = $dao->$column;
          // Convert Job Contract entity values to their proper export
          // format defined in CRM_Hrjobcontract_ExportImportValuesConverter class.
          if (substr($column, 0, 13) === 'hrjobcontract') {
            list(, $entity, $field) = explode('_', $column, 3);
            $row[$column] = $converter->export($entity, $field, $row[$column]);
          }

          if ($column === 'hrjobcontract_leave_leave_amount') {
            $contractEntitlementsArray = [];
            $contractEntitlements = explode(',', $row[$column]);
            foreach ($contractEntitlements as $contractEntitlement) {
              list($leaveType, $entitlement) = explode(':', $contractEntitlement);
              $contractEntitlementsArray[trim($leaveType)] = trim($entitlement);
            }
            foreach ($leaveTypes as $type => $typeId) {
              $row[$type] = !empty($contractEntitlementsArray[$type]) ? $contractEntitlementsArray[$type] : '';
            }
          }
        }

        $componentDetails[] = $row;
      }

      $csvRows = CRM_Core_Report_Excel::writeCSVFile($getExportFileName,
        $headerRows,
        $componentDetails,
        NULL,
        $writeHeader,
        $saveFile);

      if ($saveFile && !empty($csvRows)) {
        $batchItems .= $csvRows;
      }

      $writeHeader = FALSE;
      $offset += $limit;
    }
  }
}
