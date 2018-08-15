/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  return {
    all: {
      'is_error': 0,
      'version': 3,
      'count': 8,
      'values': [
        {
          'id': '5',
          'name_a_b': 'Employee of',
          'label_a_b': 'Employee of',
          'name_b_a': 'Employer of',
          'label_b_a': 'Employer of',
          'description': 'Employment relationship.',
          'contact_type_a': 'Individual',
          'contact_type_b': 'Organization',
          'is_reserved': '1',
          'is_active': '0'
        },
        {
          'id': '7',
          'name_a_b': 'Head of Household for',
          'label_a_b': 'Head of Household for',
          'name_b_a': 'Head of Household is',
          'label_b_a': 'Head of Household is',
          'description': 'Head of household.',
          'contact_type_a': 'Individual',
          'contact_type_b': 'Household',
          'is_reserved': '1',
          'is_active': '0'
        },
        {
          'id': '8',
          'name_a_b': 'Household Member of',
          'label_a_b': 'Household Member of',
          'name_b_a': 'Household Member is',
          'label_b_a': 'Household Member is',
          'description': 'Household membership.',
          'contact_type_a': 'Individual',
          'contact_type_b': 'Household',
          'is_reserved': '1',
          'is_active': '0'
        },
        {
          'id': '9',
          'name_a_b': 'Case Coordinator is',
          'label_a_b': 'Case Coordinator is',
          'name_b_a': 'Case Coordinator',
          'label_b_a': 'Case Coordinator',
          'description': 'Case Coordinator',
          'contact_type_a': 'Individual',
          'contact_type_b': 'Individual',
          'is_reserved': '0',
          'is_active': '0'
        },
        {
          'id': '11',
          'name_a_b': 'HR Manager is',
          'label_a_b': 'HR Manager is',
          'name_b_a': 'HR Manager',
          'label_b_a': 'HR Manager',
          'contact_type_a': 'Individual',
          'contact_type_b': 'Individual',
          'is_reserved': '0',
          'is_active': '1'
        },
        {
          'id': '12',
          'name_a_b': 'Line Manager is',
          'label_a_b': 'Line Manager is',
          'name_b_a': 'Line Manager',
          'label_b_a': 'Line Manager',
          'contact_type_a': 'Individual',
          'contact_type_b': 'Individual',
          'is_reserved': '1',
          'is_active': '1'
        },
        {
          'id': '13',
          'name_a_b': 'Recruiting Manager is',
          'label_a_b': 'Recruiting Manager is',
          'name_b_a': 'Recruiting Manager',
          'label_b_a': 'Recruiting Manager',
          'contact_type_a': 'Individual',
          'contact_type_b': 'Individual',
          'is_reserved': '0',
          'is_active': '1'
        },
        {
          'id': '14',
          'name_a_b': 'has Leave Approved by',
          'label_a_b': 'has Leave Approved by',
          'name_b_a': 'is Leave Approver of',
          'label_b_a': 'is Leave Approver of',
          'description': 'Has Leave Approved By',
          'contact_type_a': 'Individual',
          'contact_type_b': 'Individual',
          'is_reserved': '1',
          'is_active': '1'
        }
      ]
    }
  };
});
