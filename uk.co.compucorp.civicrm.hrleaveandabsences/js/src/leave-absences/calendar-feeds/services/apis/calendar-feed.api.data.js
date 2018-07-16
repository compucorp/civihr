/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  var methods = {
    all: all
  };

  var mockData = {
    all: {
      is_error: 0,
      version: 3,
      count: 3,
      values: [
        {
          id: '1',
          title: 'One',
          timezone: 'Africa/Asmara',
          hash: 'hsh7779kjJJK',
          created: '2018-01-03 00:00:00'
        },
        {
          id: '5',
          title: 'Five',
          timezone: 'Asia/Ulaanbaatar',
          hash: 'wiYGuefher78ggh38',
          created: '2018-01-18 23:59:59'
        },
        {
          id: '89',
          title: 'Eighty Nine',
          timezone: 'Pacific/Port_Moresby',
          hash: 'wefjh98wefhbwefIHI',
          created: '2019-01-18 12:39:01'
        }
      ]
    }
  };

  /**
   * Returns a response with all feeds
   *
   * @return {Object}
   */
  function all () {
    return _.clone(mockData.all);
  }

  return methods;
});
