//intercepts paths for real APIs and returns mock data
define([
  'mocks/module',
  'mocks/apis/data/entitlement-data',
  'common/angularMocks',
], function (mocks, mock_data) {
  'use strict';

  mocks.factory('api.leave-absences.entitlement.mock', ['$httpBackend', function($httpBackend) {
    //when the URL has this pattern entity=LeavePeriodEntitlement&action=get
    //civicrm/ajax/rest?action=get&entity=LeavePeriodEntitlement
    $httpBackend.whenGET(/action=get&entity=LeavePeriodEntitlement/)
      .respond(mock_data.all_data);

    ///civicrm/ajax/rest?action=getbreakdown&entity=LeavePeriodEntitlement&json={}&sequential=1
    $httpBackend.whenGET(/action=getbreakdown&entity=LeavePeriodEntitlement/)
      .respond(mock_data.breakdown_data);

    //GET /civicrm/ajax/rest?action=get&entity=LeavePeriodEntitlement&json={"sequential":1,"api.LeavePeriodEntitlement.getremainder":{"entitlement_id":"$value.id","include_future":true}}&sequential=1
    $httpBackend.whenGET(/api\.LeavePeriodEntitlement\.getremainder/)
      .respond(mock_data.all_data_with_remainder);
  }]);
});
