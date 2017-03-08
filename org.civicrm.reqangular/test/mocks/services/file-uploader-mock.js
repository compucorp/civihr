define([
  'common/mocks/module'
], function (mocks) {
  'use strict';

  mocks.factory('FileUploaderMock', function () {
    var fileUploaderConstructor = function (settings) {
      var response = {
        "file": {
          "is_error": 0,
          "version": 3,
          "count": 1,
          "id": 1,
          "values": {
            "1": {
              "id": "1",
              "name": "PCHR_101089347_200217_0949.pdf",
              "mime_type": "application/pdf",
              "description": "",
              "upload_date": "2017-02-21 14:29:58",
              "entity_table": "civicrm_hrleaveandabsences_leave_request",
              "entity_id": "1",
              "url": "http://civicrm.host/index.php?q=civicrm/file&reset=1&id=1&eid=1"
            },
            "2": {
              "id": "2",
              "name": "SampleTextFile.txt",
              "mime_type": "text/plain",
              "description": "",
              "upload_date": "2017-02-21 14:31:58",
              "entity_table": "civicrm_hrleaveandabsences_leave_request",
              "entity_id": "1",
              "url": "http://civicrm.host/index.php?q=civicrm/file&reset=1&id=2&eid=1"
            }
          }
        }
      };

      return {
        uploadAll: function () {
          this._onCompleteItem();
        },
        _onCompleteItem: function () {
          this.onCompleteItem({}, response);
          this.onCompleteAll();
        },
        onCompleteItem: function (item, response, status, headers) {},
        onCompleteAll: function () {}
      };
    }

    return fileUploaderConstructor;
  });
});
