/* eslint-env amd */

define([
  'common/mocks/module',
  'common/mocks/data/file-uploader.data'
], function (mocks, fileUploaderData) {
  'use strict';

  mocks.factory('FileUploaderMock', function () {
    var fileUploaderConstructor = function (settings) {
      function _onCompleteItem () {
        this.onCompleteItem({}, fileUploaderData.response);
        this.onCompleteAll();
      }

      return {
        uploadAll: function () {
          _onCompleteItem.call(this);
        },
        // empty callbacks defintion requires for mocks to work
        onCompleteItem: settings.onCompleteItem,
        onCompleteAll: settings.onCompleteAll,
        onErrorItem: settings.onErrorItem,
        filters: settings.filters,
        onBeforeUploadItem: function () {}
      };
    };

    return fileUploaderConstructor;
  });
});
