/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  var LINE_BREAK = '\n\n';

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
                  LINE_BREAK +
                  'The Leave Type Title is also displayed in the Staff Calendar, but can be made private later in this wizard.'
                ]
              },
              {
                name: 'leave_category',
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
                  LINE_BREAK,
                  'If you enable this option the leave type will simply be given the title "Leave".',
                  'Managers and administrators will continue to see the actual leave title of the request.',
                  LINE_BREAK,
                  'This may be helpful if the leave type title is sensitive - i.e. Unpaid leave or Compassionate leave.'
                ].join(' ')
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
              }
            ]
          },
          {
            label: 'Public Holidays',
            fields: [
              {
                name: 'must_take_public_holiday_as_leave',
                label: 'Do staff work on public holidays?'
              }
            ]
          },
          {
            label: 'Carry Forwards',
            fields: [
              {
                name: 'allow_carry_forward',
                label: 'Allow carry forward?'
              }
            ]
          }
        ]
      }
    ])
    .constant('form-settings-tabs');
});
