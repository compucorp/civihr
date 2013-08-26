<?php
// This file declares a managed database record of type "WordReplacement".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
return array (
              array (
                     'name'   => 'WordReplacement Relationships',
                     'entity' => 'WordReplacement',
                     'params' =>
                     array (
                            'version' => 3,
                            'find_word' => 'Relationships',
                            'replace_word'=>'Emergency Contacts'  
                            ),
                     ),
              array (
                     'name'   => 'WordReplacement Relationship',
                     'entity' => 'WordReplacement',
                     'params' =>
                     array (
                            'version' => 3,
                            'find_word' => 'Relationship',
                            'replace_word'=>'Emergency Contact'  
                            ),
                     )
              );
