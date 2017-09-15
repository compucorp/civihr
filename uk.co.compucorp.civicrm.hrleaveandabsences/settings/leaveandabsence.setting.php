<?php

  return [
    'relationship_types_allowed_to_approve_leave' => [
      'group_name' => 'Leave and Absences General Settings',
      'group' => 'leave_and_absences_general_settings',
      'name' => 'relationship_types_allowed_to_approve_leave',
      'type' => 'Array',
      'description' => 'Relationship types that are allowed to approve a Leave Request',
      'label' =>  'Relationship types that are allowed to approve leave: <br/>
                   <span class="crm-marker-description">(Please note that CiviHR administrators are always able to approve all leave)</span>',
      'html_type' => 'addSelect',
      'is_domain' => 0,
      'html_attributes' => [
        'style' => 'width:350px',
      ],
      'quick_form_type' => 'Element',
    ],
  ];
