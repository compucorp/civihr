/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  var LINE_BREAK = '\n';
  var DOUBLE_LINE_BREAK = LINE_BREAK + LINE_BREAK;

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
            fields: [
              {
                name: 'title',
                label: 'Title',
                helpText: [
                  'The Leave Type Title is displayed for everyone on all leave reports and request forms.' +
                  'For example, if you enter "Annual Leave" here, all your staff will see a Leave Type called "Annual Leave" in their Self Service Portal screens.' +
                  'Use a term that is familiar to them, and a part of your organisation"s leave policies.' +
                  'Other examples: "Annual Leave Full-time" , "Annual Leave Part-time" , "Paid Sick Leave", "Unpaid Sick Leave", "Compensatory Off".' +
                  DOUBLE_LINE_BREAK +
                  'The Leave Type Title is also displayed in the Staff Calendar, but can be made private later in this wizard.'
                ].join(' ')
              },
              {
                name: 'category',
                labelLayout: 'horizontal',
                label: 'What kind of absence type are you looking to create?'
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
            fields: [
              {
                name: 'hide_label',
                label: 'Hide leave type label on public calendars and feeds?',
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
                name: 'colour',
                label: 'Calendar colour',
                helpText: 'Note this will be a default grey on the public calendar if "Hide leave type label on public calendars and feeds" is set to "Yes".'
              },
              {
                name: 'calculation_unit',
                label: 'Calculate leave in'
              },
              {
                name: 'default_entitlement',
                label: 'Default entitlement'
              },
              {
                name: 'notification_receivers_ids',
                label: 'Backup leave approver',
                helpText: [
                  'You can configure the system with a backup leave approver that will be notified of a leave request if a staff member does not have an active leave approver.',
                  DOUBLE_LINE_BREAK,
                  'For most organisations it would be sensible for the CiviHR administrator to be entered here.'
                ].join(' ')
              },
              {
                name: 'is_active',
                label: 'Enabled'
              }
            ]
          },
          {
            label: 'Leave Requests',
            fields: [
              {
                name: 'max_consecutive_leave_days',
                label: 'Max consecutive duration (Leave blank for unlimited)',
                helpText: [
                  'Configure the maximum duration of consecutive leave permitted to be selected in a single leave request.',
                  'You can leave this field blank for unlimited duration of leave.'
                ].join(' ')
              },
              {
                name: 'allow_request_cancelation',
                label: 'Staff self-cancellation',
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
            fields: [
              {
                name: 'must_take_public_holiday_as_leave',
                label: 'Do staff work on public holidays?',
                helpText: 'If your staff work on public holidays then set this to yes and public holidays will be considered working days, otherwise by setting this to no, the system will consider these to be non-working days and staff will have public holidays leave requests generated automatically for them.'
              }
            ]
          },
          {
            label: 'Carry Forwards',
            fields: [
              {
                name: 'allow_carry_forward',
                label: 'Allow carry forward?'
              },
              {
                name: 'max_number_of_days_to_carry_forward',
                label: 'Maximum carry forward',
                helpText: 'Configure the maximum amount of days or hours of this leave type that can be carried forward from one period to another.'
              },
              {
                name: 'carry_forward_expiration',
                label: 'Carry forward expiry',
                helpText: [
                  'You can configure the carry forward leave to expire in the new period after a certain time or to never expire. i.e.',
                  'If I set the expiry for 3 months and the end of my leave period is 31 December, the carry forward will expire on the 31st March.',
                  DOUBLE_LINE_BREAK,
                  'If you configure the leave to never expire the leave will carry forward indefinitely including to future periods.'
                ]
              }
            ]
          }
        ]
      }
    ]);
});