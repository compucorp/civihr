'use strict';

var page = require('../../../page-objects/ssp-leave-absences-manager-leave-requests');
var capturePath = './backstop_data/screenshots/reference/';

// precondition: need to have the login of manager and all status types having atleast one leave request
module.exports = function (casper) {
  page.init(casper)
    .openSideMenu(1)
    .openActionsForRow(1)
    .takeScreenShot(capturePath + 'Manager_Actions_Approved_Leave_Requests.png')
    .openSideMenu(3)
    .openActionsForRow(1)
    .takeScreenShot(capturePath + 'Manager_Actions_Waiting_Approval_Leave_Requests.png')
    .openSideMenu(4)
    .openActionsForRow(1)
    .takeScreenShot(capturePath + 'Manager_Actions_More_Info_Leave_Requests.png')
    .openSideMenu(5)
    .openActionsForRow(1)
    .takeScreenShot(capturePath + 'Manager_Actions_Rejected_Leave_Requests.png')
    .openSideMenu(6)
    .openActionsForRow(1)
    .takeScreenShot(capturePath + 'Manager_Actions_Cancelled_Leave_Requests.png');
};
