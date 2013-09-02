<?php
// This file declares a managed database record of type "WordReplacement".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return array (
              array (
                     'name' => 'WordReplacement CiviHR',
                     'entity' => 'WordReplacement',
                     'params' =>
                     array (
                            'version' => 3,
                            'find_word' => 'CiviCRM',
                            'replace_word'=>'CiviHR',
                            ),
                     )
              );
