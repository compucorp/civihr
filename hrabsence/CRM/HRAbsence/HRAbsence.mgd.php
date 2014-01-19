<?php
// This file declares a managed database record of type "HRAbsenceType".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC/Hook+Reference
 return array (
   array (
     'name' => 'Sick Absence Type',
     'entity' => 'HRAbsenceType',
     'params' =>
       array (
       	 'version' => 3,
         'title' => "Sick",
       	 'is_active' => 1,	
  	     'allow_debits' => 1,     
       ),
    ),
    array (
      'name' => 'Vacation Absence Type',
      'entity' => 'HRAbsenceType',
      'params' =>
        array (
          'version' => 3,
          'title' => "Vacation",
          'is_active' => 1,
  	      'allow_debits' => 1,     
        ),
     ),  
     array (
 	   'name' => 'Maternity Absence Type',
       'entity' => 'HRAbsenceType',
       'params' =>
         array (
           'version' => 3,
           'title' => "Maternity",
           'is_active' => 1,
           'allow_debits' => 1,
 		 ),
     ),
     array (
 	   'name' => 'Paternity Absence Type',
 	   'entity' => 'HRAbsenceType',
 	   'params' =>
 	     array (
 	       'version' => 3,
         'title' => "Paternity",
         'is_active' => 1,
         'allow_debits' => 1,
 		 ),
     ),
     array (
       'name' => 'TOIL Absence Type',
       'entity' => 'HRAbsenceType',
       'params' =>
         array (
           'version' => 3,
           'title' => "TOIL",
           'is_active' => 1,
           'allow_debits' => 1,
           'allow_credits' => 1,
         ),
      ),
      array (
        'name' => 'Other Absence Type',
        'entity' => 'HRAbsenceType',
        'params' =>
          array (
            'version' => 3,
 		        'title' => "Other",
 		        'is_active' => 1,
 			      'allow_debits' => 1,
 		      ),
 	  ),
 	  array (
        'name' => 'Undecided: Public Holiday Absence Type',
        'entity' => 'HRAbsenceType',
        'params' =>
          array (
            'version' => 3,
            'title' => "Undecided: Public Holiday",
            'is_active' => 1,
            'allow_debits' => 1,
          ),
      )
 ); 
 