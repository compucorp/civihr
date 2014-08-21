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

class CRM_HRJob_Page_Files extends CRM_Core_Page {
  function fileDisplay() {// Display evidence file
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

  function fileDelete() {// delete file
    $postParams = $_POST;
    $fileId = $postParams['fileID'];
    CRM_Core_BAO_File::deleteEntityFile( $postParams['entityTable'], $postParams['entityID'], $fileTypeID = NULL, $fileId );
    CRM_Utils_System::civiExit( );
  }

  function fileUpload() {
    $config = CRM_Core_Config::singleton();
    $postParams = $_POST;
    if($postParams['entityID'] == 'undefined') {
      $result = civicrm_api3('HRJob','get',array('options' => array('sort' => "id DESC")));
      foreach($result['values'] as $key=>$val) {
        $id= $val['id'];
        break;
      }
      $postParams['entityID'] = ++$id;
    }

    $files = $_FILES;
    if(is_array($files) && !empty($files)) {
      foreach($files as $k => $v) {
        $fileName = CRM_Utils_File::makeFileName(basename($v['name']));
        $maxSize = @filesize($v['tmp_name']);

        $dest = $config->customFileUploadDir;
        if ($dest != ''  && substr($dest, -1) != '/') {
          $dest .= '/';
        }
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
          }
        }
      }
    }
    CRM_Utils_System::civiExit( );
  }
}
