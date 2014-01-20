CRM.mockData = CRM.mockData || {};

/**
 * Combine multiple mock datasets to make a new one
 *
 * @param {Array} datasets list of dataset names
 * @return {Array}
 */
CRM.mockData.get = function(datasets) {
  var result = [];
  _.each(datasets, function(name){
    result = result.concat(CRM.mockData[name]);
  })
  return result;
}

CRM.mockData.vacation2013 = [
  {
    "id": "620",
    "activity_type_id": "10",
    "subject": "Vaction day!",
    "activity_date_time": "2013-02-10 20:10:11",
    "duration": "480",
    "status_id": "2",
    "priority_id": "2",
    "is_test": "0",
    "is_auto": "0",
    "is_current_revision": "1",
    "is_deleted": "0",
    "source_contact_id": "202"
  }
];

CRM.mockData.toil2013 = [
  {
    "id": "625",
    "activity_type_id": "11",
    "subject": "Hello TOIL",
    "activity_date_time": "2013-02-13 20:10:11",
    "duration": "240",
    "status_id": "2",
    "priority_id": "2",
    "is_test": "0",
    "is_auto": "0",
    "is_current_revision": "1",
    "is_deleted": "0",
    "source_contact_id": "202"
  },
  {
    "id": "629",
    "activity_type_id": "11",
    "subject": "Working the over-time",
    "activity_date_time": "2013-02-14 20:10:11",
    "duration": "480",
    "status_id": "2",
    "priority_id": "2",
    "is_test": "0",
    "is_auto": "0",
    "is_current_revision": "1",
    "is_deleted": "0",
    "source_contact_id": "202"
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
    "source_contact_id": "202"
  }
];
