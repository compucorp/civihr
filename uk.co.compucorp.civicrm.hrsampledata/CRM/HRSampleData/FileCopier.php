<?php

/**
 * Class CRM_HRSampleData_FileCopier
 *
 */
class CRM_HRSampleData_FileCopier
{

  /**
   * Copies files from one Directory to another
   *
   * @param string $src
   *   Source Directory
   * @param string $dst
   *   Destination Directory
   */
  public function recurseCopy($src, $dst) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
      if (( $file != '.' ) && ( $file != '..' )) {
        if ( is_dir($src . '/' . $file) ) {
          $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
        }
        else {
          copy($src . '/' . $file, $dst . '/' . $file);
        }
      }
    }
    closedir($dir);
  }

}
