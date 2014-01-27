CRM.fixtures = CRM.fixtures || {};

/**
 * Combine multiple mock datasets to make a new one
 *
 * @param {Array} datasets list of dataset names
 * @return {Array}
 */
CRM.fixtures.get = function(datasets) {
  var result = [];
  _.each(datasets, function(name){
    result = result.concat(CRM.fixtures[name]);
  })
  return result;
}

// List of activity records of type Vacation (dated Feb 2013)
CRM.fixtures.vacationFeb2013 = [
  {
    "id": "201302000",
    "activity_type_id": "10",
    "subject": "Vaction day!",
    "activity_date_time": "2013-02-10 20:10:11",
    "duration": "0",
    "status_id": "2",
    "priority_id": "2",
    "is_test": "0",
    "is_auto": "0",
    "is_current_revision": "1",
    "is_deleted": "0",
    "source_contact_id": "202",
    "absence_range": {
      "items": [
        {id: 201302001, activity_date_time: "2013-02-15 20:10:11", duration: 480},
        {id: 201302002, activity_date_time: "2013-02-16 20:10:11", duration: 240}
      ],
      "low": "2013-02-15 20:10:11",
      "high": "2013-02-16 20:10:11",
      "duration": "720",
      "count": 2
    }
  }
];

// List of activity records of type Vacation (dated Apr 2013)
CRM.fixtures.vacationApr2013 = [
  {
    "id": "20130402000",
    "activity_type_id": "10",
    "subject": "Vaction day!",
    "activity_date_time": "2013-04-02 20:10:11",
    "duration": "0",
    "status_id": "2",
    "priority_id": "2",
    "is_test": "0",
    "is_auto": "0",
    "is_current_revision": "1",
    "is_deleted": "0",
    "source_contact_id": "202",
    "absence_range": {
      "items": [
        {id: 2013040201, activity_date_time: "2013-04-04 20:10:11", duration: 480}
      ],
      "low": "2013-04-04 20:10:11",
      "high": "2013-04-04 20:10:11",
      "duration": "480",
      "count": 1
    }
  },
  {
    "id": "20130425000",
    "activity_type_id": "10",
    "subject": "Vaction day!",
    "activity_date_time": "2013-04-25 20:10:11",
    "duration": "480",
    "status_id": "2",
    "priority_id": "2",
    "is_test": "0",
    "is_auto": "0",
    "is_current_revision": "1",
    "is_deleted": "0",
    "source_contact_id": "202",
    "absence_range": {
      "items": [],
      "low": null,
      "high": null,
      "duration": 0,
      "count": 0
    }
  }
];

// List of activity records of type TOIL (dated Feb 2013)
CRM.fixtures.toilFeb2013 = [
  {
    "id": "625",
    "activity_type_id": "11",
    "subject": "Hello TOIL",
    "activity_date_time": "2013-02-13 20:10:11",
    "duration": "0",
    "status_id": "2",
    "priority_id": "2",
    "is_test": "0",
    "is_auto": "0",
    "is_current_revision": "1",
    "is_deleted": "0",
    "source_contact_id": "202",
    "absence_range": {
      "items": [
        {id: 20130262501, activity_date_time: "2013-02-13 20:10:11", duration: 240}
      ],
      "low": "2013-02-13 20:10:11",
      "high": "2013-02-13 20:10:11",
      "duration": "240",
      "count": 1
    }
  },
  {
    "id": "629",
    "activity_type_id": "11",
    "subject": "Working the over-time",
    "activity_date_time": "2013-02-14 20:10:11",
    "duration": "0",
    "status_id": "2",
    "priority_id": "2",
    "is_test": "0",
    "is_auto": "0",
    "is_current_revision": "1",
    "is_deleted": "0",
    "source_contact_id": "202",
    "absence_range": {
      "items": [
        {id: 20130262901, activity_date_time: "2013-02-14 20:10:11", duration: 480}
      ],
      "low": "2013-02-14 20:10:11",
      "high": "2013-02-14 20:10:11",
      "duration": "480",
      "count": 1
    }
  },
  {
    "id": "630",
    "activity_type_id": "12",
    "subject": "Taking it easy after working the over-time",
    "activity_date_time": "2013-02-15 20:10:11",
    "duration": "240",
    "status_id": "2",
    "priority_id": "2",
    "is_test": "0",
    "is_auto": "0",
    "is_current_revision": "1",
    "is_deleted": "0",
    "source_contact_id": "202",
    "absence_range": {
      "items": [
        {id: 20130263001, activity_date_time: "2013-02-15 20:10:11", duration: 240}
      ],
      "low": "2013-02-15 20:10:11",
      "high": "2013-02-15 20:10:11",
      "duration": "240",
      "count": 1
    }
  }
];
