define([
  'common/mocks/module',
  'common/mocks/data/file-upload-data'
], function (mocks, mockData) {
  'use strict';

  mocks.factory('FileUploaderMock', function () {
    var fileUploaderConstructor = function (settings) {


      function _onCompleteItem() {
        var fileItem = { file: { name: 'filename' } };

        //call callbacks from callee
        this.onErrorItem(fileItem);
        this.onBeforeUploadItem({ formData: [] });
        this.onCompleteItem({}, mockData.getResponse());
        this.onCompleteAll();
      }

      return {
        uploadAll: function () {
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
