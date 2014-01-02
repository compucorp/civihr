<?php
// This file declares a managed database record of type "WordReplacement".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
 return array (
   array (
     'name' => 'Sick Absence Type',
     'entity' => 'HRAbsence',
     'params' =>
       array (
       	 'version' => 3,
         'title' => "Sick",
  	     'allow_debits' => 1,     
       ),
    ),
    array (
      'name' => 'Vacation Absence Type',
      'entity' => 'HRAbsence',
      'params' =>
        array (
          'version' => 3,
          'title' => "Vacation",
  	      'allow_debits' => 1,     
        ),
     ),  
     array (
 	   'name' => 'Maternity Absence Type',
       'entity' => 'HRAbsence',
       'params' =>
         array (
           'version' => 3,
           'title' => "Maternity",
           'allow_debits' => 1,
 		 ),
     ),
     array (
 	   'name' => 'Paternity Absence Type',
 	   'entity' => 'HRAbsence',
 	   'params' =>
 	     array (
 	       'version' => 3,
           'title' => "Paternity",
           'allow_debits' => 1,
 		 ),
     ),
     array (
       'name' => 'TOIL Absence Type',
       'entity' => 'HRAbsence',
       'params' =>
         array (
           'version' => 3,
           'title' => "TOIL",
           'allow_debits' => 1,
         ),
      ),
      array (
        'name' => 'Other Absence Type',
        'entity' => 'HRAbsence',
        'params' =>
          array (
            'version' => 3,
 		    'title' => "Other",
 			'allow_debits' => 1,
 		  ),
 	  ),
 	  array (
        'name' => 'Undecided: Public Holiday Absence Type',
        'entity' => 'HRAbsence',
        'params' =>
          array (
            'version' => 3,
            'title' => "Undecided: Public Holiday",
            'allow_debits' => 1,
          ),
      )
 );
 