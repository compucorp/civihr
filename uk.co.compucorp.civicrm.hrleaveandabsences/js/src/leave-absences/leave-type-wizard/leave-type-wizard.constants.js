/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  var LINE_BREAK = '\n';
  var DOUBLE_LINE_BREAK = LINE_BREAK + LINE_BREAK;
  var VALIDATOR_DECIMAL = {
    rule: /^\d+(\.\d)?$/,
    message: 'The value should be a positive decimal number up to 1 decimal digit'
  };
  var VALIDATOR_INTEGER = {
    rule: /^[1-9]\d*$/,
    message: 'The value should be a positive whole number'
  };
  var LEAVE_REQUESTS_TAB_DEFAULT_LABEL = 'Leave Requests';

  angular.module('leave-type-wizard.constants', [])
    .constant('form-sections', [
      {
        name: 'general',
        label: 'Leave Category',
        nextSectionButtonCustomText: 'Settings',
        nextSectionButtonChevron: true,
        previousSectionButtonCustomText: 'Cancel',
        tabs: [
          {
            name: 'general',
            fields: [
              {
                name: 'title',
                required: true,
                defaultValue: '',
                label: 'Title',
                helpText: [
                  'The Leave Type Title is displayed for everyone on all leave reports and request forms.',
                  'For example, if you enter "Annual Leave" here, all your staff will see a Leave Type called "Annual Leave" in their Self Service Portal screens.',
                  'Use a term that is familiar to them, and a part of your organisation\'s leave policies.',
                  'Other examples: "Annual Leave Full-time" , "Annual Leave Part-time" , "Paid Sick Leave", "Unpaid Sick Leave", "Compensatory Off".',
                  DOUBLE_LINE_BREAK,
                  'The Leave Type Title is also displayed in the Staff Calendar, but can be made private later in this wizard.'
                ].join(' ')
              },
              {
                name: 'category',
                required: true,
                labelLayout: 'horizontal',
                defaultValue: 'leave',
                label: 'What kind of absence type are you looking to create?'
              },
              {
                name: 'allow_accruals_request',
                defaultValue: false,
                hidden: true
              },
              {
                name: 'add_public_holiday_to_entitlement',
                defaultValue: false,
                hidden: true
              }
            ]
          }
        ]
      },
      {
        name: 'settings',
        label: 'Leave Category Settings',
        nextSectionButtonCustomText: 'Save',
        previousSectionButtonCustomText: 'Choose another category',
        tabs: [
          {
            label: 'Basic',
            name: 'basic',
            fields: [
              {
                name: 'hide_label',
                label: 'Hide leave type title on public staff calendar and feeds?',
                defaultValue: false,
                helpText: [
                  'The CiviHR self service portal has an all staff calendar.',
                  'You can also create calendar feeds that can be integrated with your calendar app.',
                  DOUBLE_LINE_BREAK,
                  'If you enable this option the leave type will simply be given the title "Leave".',
                  'Managers and administrators will continue to see the actual leave title of the request.',
                  DOUBLE_LINE_BREAK,
                  'This may be helpful if the leave type title is sensitive - i.e. Unpaid leave or Compassionate leave.'
                ].join(' ')
              },
              {
                name: 'is_sick',
                label: 'Request button',
                defaultValue: false,
                hidden: true
              },
              {
                name: 'color',
                label: 'Calendar colour',
                defaultValue: '',
                required: true,
                helpText: 'Note this will be a default grey on the public calendar if "Hide leave type label on public calendars and feeds" is set to "Yes".'
              },
              {
                name: 'calculation_unit',
                label: 'Calculate leave in',
                required: true,
                defaultValue: '1'
              },
              {
                name: 'default_entitlement',
                label: 'Default entitlement',
                defaultValue: '',
                validations: [VALIDATOR_DECIMAL]
              },
              {
                name: 'notification_receivers_ids',
                label: 'Backup leave approver',
                defaultValue: [],
                helpText: [
                  'You can configure the system with a backup leave approver that will be notified of a leave request if a staff member does not have an active leave approver.',
                  DOUBLE_LINE_BREAK,
                  'For most organisations it would be sensible for the CiviHR administrator to be entered here.'
                ].join(' ')
              },
              {
                name: 'is_active',
                label: 'Enabled',
                defaultValue: true
              }
            ]
          },
          {
            label: 'TOIL Accruals',
            name: 'toil-accruals',
            fields: [
              {
                name: 'max_leave_accrual',
                label: 'Max accrual amount',
                defaultValue: '',
                validations: [VALIDATOR_DECIMAL],
                helpText: [
                  'Configure the maximum amount of TOIL that a staff member can obtain during a leave period.',
                  'You can leave this field blank for an unlimited amount.'
                ].join(' ')
              },
              {
                name: 'allow_accrue_in_the_past',
                label: 'Overtime requests',
                defaultValue: true,
                required: true,
                helpText: [
                  'Configure whether staff can request overtime (and therefore accrue TOIL) only in advance of the date worked, or whether they can request overtime anytime (i.e. both before or after the date worked).',
                  'Most organisations would set this as Anytime.',
                  DOUBLE_LINE_BREAK,
                  'Note that admin and managers can always record overtime on behalf of employees for any date.'
                ].join(' ')
              },
              {
                name: 'accrual_never_expire',
                label: 'Expiry',
                defaultValue: true,
                required: true,
                helpText: [
                  'Configure the default expiry of TOIL accruals.',
                  'i.e. If I accrue TOIL for working on 1st February, if I set this to 1 month, then the TOIL will exprire on 1st March.'
                ].join(' ')
              },
              {
                name: 'accrual_expiration_duration',
                required: true,
                defaultValue: '',
                validations: [VALIDATOR_INTEGER]
              },
              {
                name: 'accrual_expiration_unit',
                defaultValue: '1',
                hidden: true
              }
            ]
          },
          {
            label: LEAVE_REQUESTS_TAB_DEFAULT_LABEL,
            name: 'leave-requests',
            fields: [
              {
                name: 'max_consecutive_leave_days',
                label: 'Max consecutive duration (Leave blank for unlimited)',
                defaultValue: '',
                validations: [VALIDATOR_DECIMAL],
                helpText: [
                  'Configure the maximum duration of consecutive leave permitted to be selected in a single leave request.',
                  'You can leave this field blank for unlimited duration of leave.'
                ].join(' ')
              },
              {
                name: 'allow_request_cancelation',
                label: 'Staff self-cancellation',
                required: true,
                defaultValue: '3',
                helpText: [
                  'Configure whether staff can cancel the leave request themselves without manager approval.',
                  'There are 3 options:',
                  LINE_BREAK,
                  '- Yes - in advance of the start date',
                  LINE_BREAK,
                  '- Yes - anytime. This will allow them to cancel leave requests even after the start date.',
                  LINE_BREAK,
                  '- No. This will not allow staff to cancel their own leave requests and instead their leave approver or the CiviHR administrator will need to do this on their behalf.',
                  DOUBLE_LINE_BREAK,
                  'Yes in advance of the start date is the default and suggested option.'
                ].join(' ')
              },
              {
                name: 'allow_overuse',
                label: 'Allow negative balances',
                defaultValue: false,
                helpText: [
                  'Configure whether staff members can apply for this leave type even if the request would mean that they would have a negative leave balance after the leave request was approved.',
                  DOUBLE_LINE_BREAK,
                  '- For annual leave and TOIL, the default is no, but some organisations may give staff the flexibility to go into a negative balance. Alternatively you may wish to setup another leave type for unpaid leave so that can be tracked separately.',
                  LINE_BREAK,
                  '- For sickness, the default is yes, as staff do not normally have an entitlement of sickness and the system will keep a total of the sickness absence taken.',
                  'For organisations who have paid and unpaid sickness you can either: a) setup one leave type, do not give staff an entitlement of sickness, set allow negative balance to "yes" and run reports to see whether a staff member has used all their balance or b) setup two leave types (one for paid sickness and another for unpaid sickness), give staff an entitlement for paid sickness, and set allow negative balance to "no" for the paid sickness type.'
                ].join(' ')
              }
            ]
          },
          {
            label: 'Public Holidays',
            name: 'public-holidays',
            fields: [
              {
                name: 'must_take_public_holiday_as_leave',
                label: 'Do staff work on public holidays?',
                defaultValue: false,
                helpText: 'If your staff work on public holidays then set this to yes and public holidays will be considered working days, otherwise by setting this to no, the system will consider these to be non-working days and staff will have public holidays leave requests generated automatically for them.'
              }
            ]
          },
          {
            label: 'Carry Forwards',
            name: 'carry-forwards',
            fields: [
              {
                name: 'allow_carry_forward',
                label: 'Allow carry forward?',
                defaultValue: false,
                helpText: 'Configure whether leave of this type be carried forward from one period to another.'
              },
              {
                name: 'max_number_of_days_to_carry_forward',
                label: 'Maximum carry forward',
                defaultValue: '',
                validations: [VALIDATOR_DECIMAL],
                helpText: 'Configure the maximum amount of days or hours of this leave type that can be carried forward from one period to another.'
              },
              {
                name: 'carry_forward_expiration_duration_switch',
                label: 'Carry forward expiry',
                defaultValue: false,
                helpText: [
                  'You can configure the carry forward leave to expire in the new period after a certain time or to never expire. i.e.',
                  'If I set the expiry for 3 months and the end of my leave period is 31 December, the carry forward will expire on the 31st March.',
                  DOUBLE_LINE_BREAK,
                  'If you configure the leave to never expire the leave will carry forward indefinitely including to future periods.'
                ].join(' ')
              },
              {
                name: 'carry_forward_expiration_duration',
                required: true,
                defaultValue: '',
                validations: [VALIDATOR_DECIMAL]
              },
              {
                name: 'carry_forward_expiration_unit',
                defaultValue: '1',
                hidden: true
              }
            ]
          }
        ]
      }
    ])
    .constant('leave-type-categories-icons', {
      leave: 'plane',
      sickness: 'medkit',
      toil: 'clock-o',
      custom: 'wrench'
    })
    .constant('tabs-hidden-by-category', {
      leave: ['toil-accruals'],
      sickness: ['public-holidays', 'carry-forwards', 'toil-accruals'],
      toil: ['public-holidays']
    })
    .constant('fields-hidden-by-category', {
      allow_overuse: ['toil'],
      is_sick: ['leave', 'sickness', 'toil']
    })
    .constant('custom-tab-names-by-category', {
      'leave-requests': {
        leave: LEAVE_REQUESTS_TAB_DEFAULT_LABEL,
        sickness: LEAVE_REQUESTS_TAB_DEFAULT_LABEL,
        toil: 'Using TOIL',
        custom: LEAVE_REQUESTS_TAB_DEFAULT_LABEL
      }
    })
    .constant('overrides-by-category', {
      leave: {
        is_sick: false,
        allow_accruals_request: false,
        add_public_holiday_to_entitlement: false
      },
      sickness: {
        must_take_public_holiday_as_leave: false,
        allow_carry_forward: false,
        allow_accruals_request: false,
        is_sick: true,
        add_public_holiday_to_entitlement: false
      },
      toil: {
        must_take_public_holiday_as_leave: false,
        allow_accruals_request: true,
        is_sick: false,
        allow_overuse: false,
        add_public_holiday_to_entitlement: false
      },
      custom: {
        allow_accruals_request: true,
        add_public_holiday_to_entitlement: false
      }
    });
});
