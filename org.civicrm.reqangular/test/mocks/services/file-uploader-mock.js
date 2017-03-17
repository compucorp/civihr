define([
  'common/mocks/module',
  'common/mocks/data/file-upload-data'
], function (mocks, mockData) {
  'use strict';

  mocks.factory('FileUploaderMock', function () {
    var fileUploaderConstructor = function (settings) {


      function _onCompleteItem() {
        this.onCompleteItem({}, mockData.getResponse());
        this.onCompleteAll();
      }

      return {
        uploadAll: function () {
          _onCompleteItem.call(this);
        },
        //empty callbacks defintion requires for mocks to work
        onCompleteItem: settings.onCompleteItem,
        onCompleteAll: settings.onCompleteAll,
        onErrorItem: settings.onErrorItem,
        onBeforeUploadItem: function () {}
      };
    }

    return fileUploaderConstructor;
  });
});
