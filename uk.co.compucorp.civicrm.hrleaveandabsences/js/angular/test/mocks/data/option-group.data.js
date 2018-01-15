/* eslint-env amd */

define([
  'common/lodash',
  'mocks/module'
], function (_, mocks) {
  'use strict';

  /**
   * Mocked option values, grouped by option group
   */
  var mockedOptionValues = {
    hrleaveandabsences_absence_type_calculation_unit: [
      {
        'id': '1168',
        'option_group_id': '149',
        'name': 'days',
        'label': 'Days',
        'value': '1',
        'weight': '1',
        'is_active': '1',
        'is_reserved': '1',
        'option_group_name': 'hrleaveandabsences_absence_type_calculation_unit'
      },
      {
        'id': '1169',
        'option_group_id': '149',
        'name': 'hours',
        'label': 'Hours',
        'value': '2',
        'weight': '2',
        'is_active': '1',
        'is_reserved': '1',
        'option_group_name': 'hrleaveandabsences_absence_type_calculation_unit'
      }
    ],
    hrleaveandabsences_work_day_type: [
      {
        'id': '1065',
        'option_group_id': '143',
        'label': 'No',
        'value': '1',
        'name': 'non_working_day',
        'is_default': '0',
        'weight': '0',
        'is_optgroup': '0',
        'is_reserved': '1',
        'is_active': '1'
      },
      {
        'id': '1066',
        'option_group_id': '143',
        'label': 'Yes',
        'value': '2',
        'name': 'working_day',
        'is_default': '0',
        'weight': '1',
        'is_optgroup': '0',
        'is_reserved': '1',
        'is_active': '1'
      },
      {
        'id': '1067',
        'option_group_id': '143',
        'label': 'Weekend',
        'value': '3',
        'name': 'weekend',
        'is_default': '0',
        'weight': '2',
        'is_optgroup': '0',
        'is_reserved': '1',
        'is_active': '1'
      }
    ],
    hrleaveandabsences_leave_request_day_type: [{
      'id': '1113',
      'option_group_id': '142',
      'label': 'All Day',
      'value': '1',
      'name': 'all_day',
      'is_default': '0',
      'weight': '0',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1114',
      'option_group_id': '142',
      'label': 'Half-day AM',
      'value': '2',
      'name': 'half_day_am',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1115',
      'option_group_id': '142',
      'label': 'Half-day PM',
      'value': '3',
      'name': 'half_day_pm',
      'is_default': '0',
      'weight': '2',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1116',
      'option_group_id': '142',
      'label': 'Weekend',
      'value': '4',
      'name': 'weekend',
      'is_default': '0',
      'weight': '3',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1117',
      'option_group_id': '142',
      'label': 'Non Working Day',
      'value': '5',
      'name': 'non_working_day',
      'is_default': '0',
      'weight': '4',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1118',
      'option_group_id': '142',
      'label': 'Public Holiday',
      'value': '6',
      'name': 'public_holiday',
      'is_default': '0',
      'weight': '5',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }],
    hrleaveandabsences_leave_request_status: [{
      'id': '1119',
      'option_group_id': '143',
      'label': 'Approved',
      'value': '1',
      'name': 'approved',
      'is_default': '0',
      'weight': '0',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1120',
      'option_group_id': '143',
      'label': 'Admin Approved',
      'value': '2',
      'name': 'admin_approved',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1121',
      'option_group_id': '143',
      'label': 'Waiting Approval',
      'value': '3',
      'name': 'awaiting_approval',
      'is_default': '0',
      'weight': '2',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1122',
      'option_group_id': '143',
      'label': 'More Information Required',
      'value': '4',
      'name': 'more_information_required',
      'is_default': '0',
      'weight': '3',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1123',
      'option_group_id': '143',
      'label': 'Rejected',
      'value': '5',
      'name': 'rejected',
      'is_default': '0',
      'weight': '4',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1124',
      'option_group_id': '143',
      'label': 'Cancelled',
      'value': '6',
      'name': 'cancelled',
      'is_default': '0',
      'weight': '5',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }],
    hrleaveandabsences_leave_days_amounts: [{
      'id': '1105',
      'option_group_id': '141',
      'label': '0 Days',
      'value': '0',
      'name': 'zero_days',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '1106',
      'option_group_id': '141',
      'label': '1/4 Days',
      'value': '0.25',
      'name': 'quarter_day',
      'is_default': '0',
      'weight': '2',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '1107',
      'option_group_id': '141',
      'label': '1/2 Days',
      'value': '0.5',
      'name': 'half_day',
      'is_default': '0',
      'weight': '3',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '1108',
      'option_group_id': '141',
      'label': '3/4 Days',
      'value': '0.75',
      'name': 'three_quarters_day',
      'is_default': '0',
      'weight': '4',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '1109',
      'option_group_id': '141',
      'label': '1 Day',
      'value': '1',
      'name': 'one_day',
      'is_default': '0',
      'weight': '5',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '1110',
      'option_group_id': '141',
      'label': '1 1/4 Days',
      'value': '1.25',
      'name': 'one_and_a_quarter_days',
      'is_default': '0',
      'weight': '6',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '1111',
      'option_group_id': '141',
      'label': '1 1/2 Days',
      'value': '1.5',
      'name': 'one_and_a_half_days',
      'is_default': '0',
      'weight': '7',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '1112',
      'option_group_id': '141',
      'label': '1 3/4 Days',
      'value': '1.75',
      'name': 'one_and_three_quarters_days',
      'is_default': '0',
      'weight': '8',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }],
    hrleaveandabsences_leave_balance_change_type: [{
      'id': '1099',
      'option_group_id': '140',
      'label': 'Leave',
      'value': '1',
      'name': 'leave',
      'is_default': '0',
      'weight': '0',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1100',
      'option_group_id': '140',
      'label': 'Brought Forward',
      'value': '2',
      'name': 'brought_forward',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1101',
      'option_group_id': '140',
      'label': 'Public Holiday',
      'value': '3',
      'name': 'public_holiday',
      'is_default': '0',
      'weight': '2',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1102',
      'option_group_id': '140',
      'label': 'Credit',
      'value': '4',
      'name': 'credit',
      'is_default': '0',
      'weight': '3',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1103',
      'option_group_id': '140',
      'label': 'Debit',
      'value': '5',
      'name': 'debit',
      'is_default': '0',
      'weight': '4',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }, {
      'id': '1104',
      'option_group_id': '140',
      'label': 'Overridden',
      'value': '6',
      'name': 'overridden',
      'is_default': '0',
      'weight': '5',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }],
    hrjc_region: [{
      'id': '1155',
      'option_group_id': '108',
      'label': 'test Region',
      'value': '1',
      'name': 'test Region',
      'filter': '0',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }],
    hrjc_department: [{
      'id': '904',
      'option_group_id': '103',
      'label': 'Finance',
      'value': 'Finance',
      'name': 'Finance',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '905',
      'option_group_id': '103',
      'label': 'HR',
      'value': 'HR',
      'name': 'HR',
      'is_default': '0',
      'weight': '2',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '906',
      'option_group_id': '103',
      'label': 'IT',
      'value': 'IT',
      'name': 'IT',
      'is_default': '0',
      'weight': '3',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '907',
      'option_group_id': '103',
      'label': 'Fundraising',
      'value': 'Fundraising',
      'name': 'Fundraising',
      'is_default': '0',
      'weight': '4',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '908',
      'option_group_id': '103',
      'label': 'Marketing',
      'value': 'Marketing',
      'name': 'Marketing',
      'is_default': '0',
      'weight': '5',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }],
    hrjc_location: [{
      'id': '902',
      'option_group_id': '106',
      'label': 'Headquarters',
      'value': 'Headquarters',
      'name': 'Headquarters',
      'is_default': '1',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '903',
      'option_group_id': '106',
      'label': 'Home or Home-Office',
      'value': 'Home',
      'name': 'Home',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }],
    hrjc_level_type: [{
      'id': '893',
      'option_group_id': '102',
      'label': 'Senior Manager',
      'value': 'Senior Manager',
      'name': 'Senior_Manager',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '894',
      'option_group_id': '102',
      'label': 'Junior Manager',
      'value': 'Junior Manager',
      'name': 'Junior_Manager',
      'is_default': '0',
      'weight': '2',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '895',
      'option_group_id': '102',
      'label': 'Senior Staff',
      'value': 'Senior Staff',
      'name': 'Senior_Staff',
      'is_default': '0',
      'weight': '3',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '896',
      'option_group_id': '102',
      'label': 'Junior Staff',
      'value': 'Junior Staff',
      'name': 'Junior_Staff',
      'is_default': '0',
      'weight': '4',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }],
    hrleaveandabsences_sickness_reason: [{
      'id': '1129',
      'option_group_id': '145',
      'label': 'Accident',
      'value': '1',
      'name': 'accident',
      'is_default': '0',
      'weight': '0',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1130',
      'option_group_id': '145',
      'label': 'Appointment',
      'value': '2',
      'name': 'appointment',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1131',
      'option_group_id': '145',
      'label': 'Backache',
      'value': '3',
      'name': 'backache',
      'is_default': '0',
      'weight': '2',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1132',
      'option_group_id': '145',
      'label': 'Cold/Flu',
      'value': '4',
      'name': 'cold_or_flu',
      'is_default': '0',
      'weight': '3',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1133',
      'option_group_id': '145',
      'label': 'Food Poisoning',
      'value': '5',
      'name': 'food_poisoning',
      'is_default': '0',
      'weight': '4',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1134',
      'option_group_id': '145',
      'label': 'Gastric Upset',
      'value': '6',
      'name': 'gastric_upset',
      'is_default': '0',
      'weight': '5',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1135',
      'option_group_id': '145',
      'label': 'Headache',
      'value': '7',
      'name': 'headache',
      'is_default': '0',
      'weight': '6',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1136',
      'option_group_id': '145',
      'label': 'Infectious Disease',
      'value': '8',
      'name': 'infectious_disease',
      'is_default': '0',
      'weight': '7',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1137',
      'option_group_id': '145',
      'label': 'Migraine',
      'value': '9',
      'name': 'migraine',
      'is_default': '0',
      'weight': '8',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1138',
      'option_group_id': '145',
      'label': 'Minor Operation',
      'value': '10',
      'name': 'minor_operation',
      'is_default': '0',
      'weight': '9',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1139',
      'option_group_id': '145',
      'label': 'Stress',
      'value': '11',
      'name': 'stress',
      'is_default': '0',
      'weight': '10',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }
    ],
    hrleaveandabsences_leave_request_required_document: [{
      'id': '1126',
      'option_group_id': '144',
      'label': 'Self certification form required',
      'value': '1',
      'name': 'self_certification_form_required',
      'is_default': '0',
      'weight': '0',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1127',
      'option_group_id': '144',
      'label': 'Back to work interview required',
      'value': '2',
      'name': 'back_to_work_interview_required',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1128',
      'option_group_id': '144',
      'label': 'Return to work form required',
      'value': '3',
      'name': 'return_to_work_form_required',
      'is_default': '0',
      'weight': '2',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }
    ],
    hrleaveandabsences_toil_amounts: [{
      'id': '1140',
      'option_group_id': '146',
      'label': '0 Days',
      'value': '0',
      'name': 'zero_days',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1141',
      'option_group_id': '146',
      'label': '1/4 Days',
      'value': '0.25',
      'name': 'quarter_day',
      'is_default': '0',
      'weight': '2',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1142',
      'option_group_id': '146',
      'label': '1/2 Days',
      'value': '0.5',
      'name': 'half_day',
      'is_default': '0',
      'weight': '3',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1143',
      'option_group_id': '146',
      'label': '3/4 Days',
      'value': '0.75',
      'name': 'three_quarters_day',
      'is_default': '0',
      'weight': '4',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1144',
      'option_group_id': '146',
      'label': '1 Day',
      'value': '1',
      'name': 'one_day',
      'is_default': '0',
      'weight': '5',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1145',
      'option_group_id': '146',
      'label': '1 1/4 Days',
      'value': '1.25',
      'name': 'one_and_a_quarter_days',
      'is_default': '0',
      'weight': '6',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1146',
      'option_group_id': '146',
      'label': '1 1/2 Days',
      'value': '1.5',
      'name': 'one_and_a_half_days',
      'is_default': '0',
      'weight': '7',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1147',
      'option_group_id': '146',
      'label': '1 3/4 Days',
      'value': '1.75',
      'name': 'one_and_three_quarters_days',
      'is_default': '0',
      'weight': '8',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1148',
      'option_group_id': '146',
      'label': '2 Days',
      'value': '2',
      'name': 'two_days',
      'is_default': '0',
      'weight': '9',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1149',
      'option_group_id': '146',
      'label': '2 1/4 Days',
      'value': '2.25',
      'name': 'two_and_a_quarter_days',
      'is_default': '0',
      'weight': '10',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1150',
      'option_group_id': '146',
      'label': '2 1/2 Days',
      'value': '2.5',
      'name': 'two_and_a_half_days',
      'is_default': '0',
      'weight': '11',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1151',
      'option_group_id': '146',
      'label': '2 3/4 Days',
      'value': '2.75',
      'name': 'two_and_three_quarters_days',
      'is_default': '0',
      'weight': '12',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    },
    {
      'id': '1152',
      'option_group_id': '146',
      'label': '3 Days',
      'value': '3',
      'name': 'three_days',
      'is_default': '0',
      'weight': '13',
      'is_optgroup': '0',
      'is_reserved': '1',
      'is_active': '1'
    }
    ],
    hrjc_revision_change_reason: [{
      'id': '938',
      'option_group_id': '110',
      'label': 'Promotion',
      'value': '1',
      'name': 'Promotion',
      'filter': '0',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '939',
      'option_group_id': '110',
      'label': 'Increment',
      'value': '2',
      'name': 'Increment',
      'filter': '0',
      'is_default': '0',
      'weight': '2',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '940',
      'option_group_id': '110',
      'label': 'Disciplinary',
      'value': '3',
      'name': 'Disciplinary',
      'filter': '0',
      'is_default': '0',
      'weight': '3',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }],
    safe_file_extension: [{
      'id': '296',
      'option_group_id': '30',
      'label': 'jpg',
      'value': '1',
      'name': 'jpg',
      'filter': '0',
      'is_default': '0',
      'weight': '1',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '297',
      'option_group_id': '30',
      'label': 'jpeg',
      'value': '2',
      'name': 'jpeg',
      'filter': '0',
      'is_default': '0',
      'weight': '2',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '298',
      'option_group_id': '30',
      'label': 'png',
      'value': '3',
      'name': 'png',
      'filter': '0',
      'is_default': '0',
      'weight': '3',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '299',
      'option_group_id': '30',
      'label': 'gif',
      'value': '4',
      'name': 'gif',
      'filter': '0',
      'is_default': '0',
      'weight': '4',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '300',
      'option_group_id': '30',
      'label': 'txt',
      'value': '5',
      'name': 'txt',
      'filter': '0',
      'is_default': '0',
      'weight': '5',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '301',
      'option_group_id': '30',
      'label': 'pdf',
      'value': '6',
      'name': 'pdf',
      'filter': '0',
      'is_default': '0',
      'weight': '6',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '302',
      'option_group_id': '30',
      'label': 'doc',
      'value': '7',
      'name': 'doc',
      'filter': '0',
      'is_default': '0',
      'weight': '7',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '303',
      'option_group_id': '30',
      'label': 'xls',
      'value': '8',
      'name': 'xls',
      'filter': '0',
      'is_default': '0',
      'weight': '8',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '304',
      'option_group_id': '30',
      'label': 'rtf',
      'value': '9',
      'name': 'rtf',
      'filter': '0',
      'is_default': '0',
      'weight': '9',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '305',
      'option_group_id': '30',
      'label': 'csv',
      'value': '10',
      'name': 'csv',
      'filter': '0',
      'is_default': '0',
      'weight': '10',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '306',
      'option_group_id': '30',
      'label': 'ppt',
      'value': '11',
      'name': 'ppt',
      'filter': '0',
      'is_default': '0',
      'weight': '11',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '307',
      'option_group_id': '30',
      'label': 'docx',
      'value': '12',
      'name': 'docx',
      'filter': '0',
      'is_default': '0',
      'weight': '12',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '308',
      'option_group_id': '30',
      'label': 'xlsx',
      'value': '13',
      'name': 'xlsx',
      'filter': '0',
      'is_default': '0',
      'weight': '13',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }, {
      'id': '309',
      'option_group_id': '30',
      'label': 'odt',
      'value': '14',
      'name': 'odt',
      'filter': '0',
      'is_default': '0',
      'weight': '14',
      'is_optgroup': '0',
      'is_reserved': '0',
      'is_active': '1'
    }]
  };

  return {
    /**
     * Pick a random value out of a collection
     *
     * @param {array} the option group collection key
     * @param {string} key - The sub-collection key
     * @return {object}
     */
    randomValue: function (collection, key) {
      var whichOptionGroup = mockedOptionValues[collection];
      var randomOptionValue = _.sample(whichOptionGroup);
      return randomOptionValue[key];
    },
    /**
     * Pick a specific value out of a collection
     *
     * @param {array} the option group collection key
     * @param {string} key - The sub-collection key
     * @param {string} value - The sub-collection key's value to match
     * @return {object}
     */
    specificValue: function (collection, key, value) {
      var whichOptionGroup = mockedOptionValues[collection];
      var specificObject = _.find(whichOptionGroup, function (item) {
        return item[key] === value;
      });
      return specificObject[key];
    },
    /**
     * Gets all values for given key in collection object
     *
     * @param {array} the option group collection key
     * @param {string} key - The sub-collection key
     * @return {Array} of values
     */
    getAllValuesForKey: function (collection, key) {
      return mockedOptionValues[collection].map(function (item) {
        return item[key];
      });
    },
    /**
     * Gets all leave request day types values
     *
     * @return {Array} of values of leave request day types
     */
    getAllRequestDayValues: function () {
      return this.getAllValuesForKey('hrleaveandabsences_leave_request_day_type', 'value');
    },
    /**
     * Gets all leave request statuses values
     *
     * @return {Array} of values of leave request statuses
     */
    getAllRequestStatusesValues: function () {
      return this.getAllValuesForKey('hrleaveandabsences_leave_request_status', 'value');
    },
    /**
     * Returns the specified collection
     *
     * @param  {string} collection
     * @return {Array}
     */
    getCollection: function (collection) {
      return mockedOptionValues[collection];
    },
    /**
     * Pick a specific object out of a collection
     *
     * @param {array} collection - the option group collection key
     * @param {string} key - The sub-collection key
     * @param {string} value - The sub-collection key's value to match
     * @return {object}
     */
    specificObject: function (collection, key, value) {
      var whichOptionGroup = mockedOptionValues[collection];

      return _.find(whichOptionGroup, function (item) {
        return item[key] === value;
      });
    }
  };
});
