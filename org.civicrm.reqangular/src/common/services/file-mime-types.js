/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/services'
], function (_, services) {
  'use strict';

  services.factory('FileMimeTypes', [function () {
    var mimeTypesMap = {
      // If other extensions are added in the OptionGroups,
      // mime types need to be added here
      'txt': 'plain',
      'png': 'png',
      'jpeg': 'jpeg',
      'bmp': 'bmp',
      'gif': 'gif',
      'pdf': 'pdf',
      'doc': 'msword',
      'docx': 'vnd.openxmlformats-officedocument.wordprocessingml.document',
      'xls': 'vnd.ms-excel',
      'xlsx': 'vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'ppt': 'vnd.ms-powerpoint',
      'pptx': 'vnd.openxmlformats-officedocument.presentationml.presentation'
    };

    return {
      getMimeTypeFor: function (extension) {
        return mimeTypesMap[extension];
      }
    };
  }]);
});
