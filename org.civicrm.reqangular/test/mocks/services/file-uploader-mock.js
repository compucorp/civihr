define([
  'common/mocks/module',
  'common/mocks/data/file-upload-data'
], function (mocks, mockData) {
  'use strict';

  mocks.factory('FileUploaderMock', function () {
    var fileUploaderConstructor = function (settings) {


      function _onCompleteItem() {
        //call callbacks from callee
        this.onCompleteItem({}, mockData.getResponse());
        this.onCompleteAll();
      }

      return {
        uploadAll: function () {
          this.onBeforeUploadItem({ formData: [] });
          _onCompleteItem.call(this);
        },
        //empty callbacks defintion requires for mocks to work
        onCompleteItem: function () {},
        onCompleteAll: function () {},
        onErrorItem: function () {},
        onBeforeUploadItem: function () {}
      };
    }

    return fileUploaderConstructor;
  });
});
