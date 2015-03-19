<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.4                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2014                                |
+--------------------------------------------------------------------+
| This file is a part of CiviCRM.                                    |
|                                                                    |
| CiviCRM is free software; you can copy, modify, and distribute it  |
| under the terms of the GNU Affero General Public License           |
| Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
|                                                                    |
| CiviCRM is distributed in the hope that it will be useful, but     |
| WITHOUT ANY WARRANTY; without even the implied warranty of         |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
| See the GNU Affero General Public License for more details.        |
|                                                                    |
| You should have received a copy of the GNU Affero General Public   |
| License and the CiviCRM Licensing Exception along                  |
| with this program; if not, contact CiviCRM LLC                     |
| at info[AT]civicrm[DOT]org. If you have questions about the        |
| GNU Affero General Public License or the licensing of CiviCRM,     |
| see the CiviCRM license FAQ at http://civicrm.org/licensing        |
+--------------------------------------------------------------------+
*/

require_once 'CRM/Core/Page.php';

class CRM_Hrjobcontract_Page_Files extends CRM_Core_Page {
  public static function fileList() {
    $config = CRM_Core_Config::singleton();
    $postParams = $_GET;
    $result = array();
    $fileID = CRM_Core_BAO_File::getEntityFile( $postParams['entityTable'], $postParams['entityID'] );
    
    if($fileID) {
      foreach($fileID as $k => $v) {
        $fileType = $v['mime_type'];
        $fid = $v['fileID'];
        $eid = $postParams['entityID'];
        $url = null;
        $uri = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_File', $fid, 'uri');
        
        if ($fileType == 'image/jpeg' ||
          $fileType == 'image/pjpeg' ||
          $fileType == 'image/gif' ||
          $fileType == 'image/x-png' ||
          $fileType == 'image/png'
        ) {
          $url = CRM_Utils_System::url('civicrm/file',
                 "reset=1&id=$fid&eid=$eid",
                 FALSE, NULL, TRUE, TRUE
          );
        } else {
          $url = CRM_Utils_System::url('civicrm/file', "reset=1&id=$fid&eid=$eid");
        }
        
        list($sql, $params) = CRM_Core_BAO_File::sql($postParams['entityTable'], $postParams['entityID'], NULL, $v['fileID']);
        $dao = CRM_Core_DAO::executeQuery($sql, $params);
        
        $fileSize = 0;
        if ($dao->fetch()) {
          $fileSize = filesize($config->customFileUploadDir . DIRECTORY_SEPARATOR . $dao->uri);
        }
        
        $result[] = array(
          'entityTable' => $postParams['entityTable'],
          'entityID' => $eid,
          'fileID' => $fid,
          'fileType' => $fileType,
          'fileSize' => $fileSize,
          'name' => $uri,
          'url' => $url,
        );
      }
    }
    
    echo html_entity_decode(stripcslashes(json_encode(array('values' => $result))));
    CRM_Utils_System::civiExit( );
  }
  public static function fileDisplay() {// Display evidence file
    $postParams = $_POST;
    $fileID = CRM_Core_BAO_File::getEntityFile( $postParams['entityTable'], $postParams['entityID'] );
    
    if($fileID) {
      foreach($fileID as $k => $v) {
        $fileType = $v['mime_type'];
        $fid = $v['fileID'];
        $eid = $postParams['entityID'];
        if ($fileType == 'image/jpeg' ||
          $fileType == 'image/pjpeg' ||
          $fileType == 'image/gif' ||
          $fileType == 'image/x-png' ||
          $fileType == 'image/png'
        ) {
          list($path) = CRM_Core_BAO_File::path($fid, $eid, NULL, NULL);
          list($imageWidth, $imageHeight) = getimagesize($path);
          list($imageThumbWidth, $imageThumbHeight) = CRM_Contact_BAO_Contact::getThumbSize($imageWidth, $imageHeight);
          $url = CRM_Utils_System::url('civicrm/file',
                 "reset=1&id=$fid&eid=$eid",
                 FALSE, NULL, TRUE, TRUE
          );
          $file_url = "
              <a href=\"$url\" class='crm-image-popup'>
              <img src=\"$url\" width=$imageThumbWidth height=$imageThumbHeight/>
              </a>";
          // for non image files
        }
        else {
          $uri = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_File', $fid, 'uri');
          $url = CRM_Utils_System::url('civicrm/file', "reset=1&id=$fid&eid=$eid");
          $file_url = "<a href=\"$url\">{$uri}</a>";
        }
        if(isset($fid)) {
          $deleteurl = "<div class=file-delete><a class='action-item crm-hover-button' href='javascript:void(0)' id=file_{$fid}>Delete Attached File</a></div>";
          echo "<div id='del_{$fid}'>{$file_url}{$deleteurl}</div>";
        }
      }
    }
    CRM_Utils_System::civiExit( );
  }

  public static function fileDelete() {
    $postParams = $_GET;
    $fileId = $postParams['fileID'];
    $result = 0;
    
    CRM_Core_BAO_File::deleteEntityFile( $postParams['entityTable'], $postParams['entityID'], $fileTypeID = NULL, $fileId );
    
    list($path) = CRM_Core_BAO_File::path($fileId, $postParams['entityID'], NULL, NULL);
    if ($path === null)
    {
        $result = 1;
    }

    echo html_entity_decode(stripcslashes(json_encode(array('values' => array(array('result' => $result))), true)));
    CRM_Utils_System::civiExit( );
  }

  public static function fileUpload() {
    $config = CRM_Core_Config::singleton();
    $postParams = $_POST;
    $result = 0;
    $dest = $config->customFileUploadDir;
    if ($dest != ''  && substr($dest, -1) != '/') {
      $dest .= '/';
    }
    
    $files = $_FILES;
    if(is_array($files) && !empty($files)) {
      foreach($files as $k => $v) {
        $fileName = self::makeFileName(basename($v['name']), $dest);
        $maxSize = @filesize($v['tmp_name']);

        $fileName = ($fileName != '') ? $fileName : basename($v['name']);
        if( is_uploaded_file($v['tmp_name']) ) {
          if( move_uploaded_file($v['tmp_name'], $dest . $fileName) ) {
            $fileparam['name'] = $fileName;
            $fileparam['uri'] = $fileName;
            $file_params = array(
              'version'       => 3,
              'file_type_id'  => null,
              'mime_type'     => $v['type'],
              'name'          => $fileName,
              'uri'           => $fileName,
              'upload_date'   => date('Y-m-d h:i:s', time()),
            );
            $file_save = civicrm_api('File','Create', $file_params);
            $newEntityFile = new CRM_Core_DAO_EntityFile();
            $newEntityFile->entity_id = $postParams['entityID'];
            $newEntityFile->entity_table = $postParams['entityTable'];
            $newEntityFile->file_id = $file_save['id'];
            $newEntityFile->save();
            $fileName = basename($v['name']);
            $result++;
          }
        }
      }
    }

    echo html_entity_decode(stripcslashes(json_encode(array('values' => array(array('result' => $result))), true)));
    CRM_Utils_System::civiExit( );
  }
  
  /**
   * @param $name
   *
   * @return string
   */
  static function makeFileName($name, $dest) {
    $info     = pathinfo($name);
    $basename = substr($info['basename'],
      0, -(strlen(CRM_Utils_Array::value('extension', $info)) + (CRM_Utils_Array::value('extension', $info) == '' ? 0 : 1))
    );
    $filename = null;
    
    if (!CRM_Utils_File::isExtensionSafe(CRM_Utils_Array::value('extension', $info))) {
      // munge extension so it cannot have an embbeded dot in it
      // The maximum length of a filename for most filesystems is 255 chars.
      // We'll truncate at 240 to give some room for the extension.
      $filename = CRM_Utils_String::munge("{$basename}_" . CRM_Utils_Array::value('extension', $info), '_', 240) . ".unknown";
    }
    else {
      $filename = CRM_Utils_String::munge("{$basename}", '_', 240) . "." . CRM_Utils_Array::value('extension', $info);
    }
    
    $newFilename = $filename;
    $i = 1;
    while (file_exists($dest . $newFilename)) {
        $fileinfo = pathinfo($dest . $filename);
        $newFilename = $fileinfo['filename'] . '(' . $i++ . ')' . '.' . $fileinfo['extension'];
    }
    
    return $newFilename;
  }
}
