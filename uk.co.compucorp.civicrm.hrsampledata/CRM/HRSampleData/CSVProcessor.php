<?php

use CRM_HRSampleData_CSVProcessingVisitor as CSVProcessingVisitor;

class CRM_HRSampleData_CSVProcessor
{

  /**
   * File Handler
   *
   * @var SplFileObject
   */
  private $fileHandler;

  public function __construct(SplFileObject $file) {
    $this->fileHandler = $file;
  }

  /**
   * Iterate through CSV file rows one by one
   * and allows you to operate on them, So you can insert
   * the row data to the database or remove the matching
   * record from the database or anything else you want to
   * do with the row data.
   *
   * @param CSVProcessingVisitor $visitor
   */
  public function process(CSVProcessingVisitor $visitor) {
    $header = null;

    while (!$this->fileHandler->eof()) {
      $row = $this->fileHandler->fgetcsv();

      if ($header === null) {
        $header = $row;
        continue;
      }

      $row = array_combine($header, $row);
      $visitor->visit($row);
    }
  }

}
