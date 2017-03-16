importScripts(location.origin + '/sites/all/modules/civicrm/tools/extensions/civihr/org.civicrm.reqangular/src/common/vendor/moment.min.js');
importScripts(location.origin + '/sites/all/modules/civicrm/tools/extensions/civihr/org.civicrm.reqangular/src/common/vendor/lodash.min.js');


var self = this;
self.addEventListener('message', function (e) {
  var variable;

  if (e.data.command === 'start') {
    init();
  } else if (e.data.command === 'setValue') {
    for (variable in e.data.variables) {
      self[variable] = e.data.variables[variable];
    }
  }
}, false);


function init() {
  self.setCalendarProps = function () {
    var dateObj,
      leaveRequest,
      dates = Object.keys(self.calendar.days);

    //Had to re assign these functions as functions cannot be sent to web worker
    self.calendar.isWeekend = function (date) {
      var searchedDate = self.calendar.days[self.getDateObjectWithFormat(date).valueOf()];
      return searchedDate.type.name === 'weekend';
    };

    self.calendar.isNonWorkingDay = function (date) {
      var searchedDate = self.calendar.days[self.getDateObjectWithFormat(date).valueOf()];
      return searchedDate.type.name === 'non_working_day';
    };

    dates.forEach(function (date) {
      dateObj = self.calendar.days[date];
      leaveRequest = self.getLeaveRequestByDate(self.contactID, dateObj.date);

      dateObj.UI = {
        isWeekend: self.calendar.isWeekend(self.getDateObjectWithFormat(dateObj.date)),
        isNonWorkingDay: self.calendar.isNonWorkingDay(self.getDateObjectWithFormat(dateObj.date)),
        isPublicHoliday: self.isPublicHoliday(dateObj.date)
      };

      // set below props only if leaveRequest is found
      if (leaveRequest) {
        dateObj.UI.styles = self.getStyles(leaveRequest, dateObj);
        dateObj.UI.isRequested = self.isPendingApproval(leaveRequest);
        dateObj.UI.isAM = self.isDayType('half_day_am', leaveRequest, dateObj.date);
        dateObj.UI.isPM = self.isDayType('half_day_pm', leaveRequest, dateObj.date);
      }
    });

    //After calculation is done, send the value back to original context
    self.postMessage({
      error: 0,
      calendar: JSON.stringify(calendar)
    });
  };
  self.isPublicHoliday = function (date) {
    return !!self.publicHolidays[self.getDateObjectWithFormat(date).valueOf()];
  };
  self.getLeaveRequestByDate = function (contactID, date) {
    return _.find(self.leaveRequests, function (leaveRequest) {
      return contactID == leaveRequest.contact_id && !!_.find(leaveRequest.dates, function (leaveRequestDate) {
          return moment(leaveRequestDate.date).isSame(date);
        });
    });
  };
  self.getDateObjectWithFormat = function (date) {
    return moment(date, self.sharedSettings.serverDateFormat).clone();
  };
  self.getStyles = function (leaveRequest, dateObj) {
    var absenceType,
      status = self.leaveRequestStatuses[leaveRequest.status_id];

    if (status.name === 'waiting_approval'
      || status.name === 'approved'
      || status.name === 'admin_approved') {
      absenceType = _.find(self.absenceTypes, function (absenceType) {
        return absenceType.id == leaveRequest.type_id;
      });

      //If Balance change is positive, mark as Accrued TOIL
      if (leaveRequest.balance_change > 0) {
        dateObj.UI.isAccruedTOIL = true;
        return {
          border: '1px solid ' + absenceType.color
        };
      }

      return {
        backgroundColor: absenceType.color,
        borderColor: absenceType.color
      };
    }
  };
  self.isPendingApproval = function (leaveRequest) {
    var status = self.leaveRequestStatuses[leaveRequest.status_id];

    return status.name === 'waiting_approval';
  };
  self.isDayType = function (name, leaveRequest, date) {
    var dayType = self.dayTypes[name];

    if (moment(date).isSame(leaveRequest.from_date)) {
      return dayType.value == leaveRequest.from_date_type;
    }

    if (moment(date).isSame(leaveRequest.to_date)) {
      return dayType.value == leaveRequest.to_date_type;
    }
  };

  self.setCalendarProps();
}
