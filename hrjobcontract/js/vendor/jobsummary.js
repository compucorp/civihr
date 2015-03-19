// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function ($, _) {
  $(document).on('crmLoad', function() {
    var gid = CRM.grID;
    var joinDate = $('#custom-set-content-'+gid+' .crm-inline-block-content div:nth-child(2) .crm-custom-data').html();
    var finalDate =$('#custom-set-content-'+gid+' .crm-inline-block-content div:nth-child(3) .crm-custom-data').html();

    if (!moment.preciseDiff){
        var STRINGS = {
            nodiff: '',
            year: 'year',
            years: 'years',
            month: 'month',
            months: 'months',
            day: 'day',
            days: 'days',
            hour: 'hour',
            hours: 'hours',
            minute: 'minute',
            minutes: 'minutes',
            second: 'second',
            seconds: 'seconds',
            delimiter: ' '
        };
        moment.fn.preciseDiff = function(d2) {
            return moment.preciseDiff(this, d2);
        };
        moment.preciseDiff = function(d1, d2) {
            var m1 = moment(d1), m2 = moment(d2);
            if (m1.isSame(m2)) {
                return STRINGS.nodiff;
            }
            if (m1.isAfter(m2)) {
                var tmp = m1;
                m1 = m2;
                m2 = tmp;
            }

            var yDiff = m2.year() - m1.year();
            var mDiff = m2.month() - m1.month();
            var dDiff = m2.date() - m1.date();
//code commented to show only year,month and days on job summary
            /*
             var hourDiff = m2.hour() - m1.hour();
             var minDiff = m2.minute() - m1.minute();
             var secDiff = m2.second() - m1.second();

             if (secDiff < 0) {
             secDiff = 60 + secDiff;
             minDiff--;
             }
             if (minDiff < 0) {
             minDiff = 60 + minDiff;
             hourDiff--;
             }
             if (hourDiff < 0) {
             hourDiff = 24 + hourDiff;
             dDiff--;
             }
             */
            if (dDiff < 0) {
                var daysInLastFullMonth = moment(m2.year() + '-' + (m2.month() + 1), "YYYY-MM").subtract('months', 1).daysInMonth();
                if (daysInLastFullMonth < m1.date()) { // 31/01 -> 2/03
                    dDiff = daysInLastFullMonth + dDiff + (m1.date() - daysInLastFullMonth);
                } else {
                    dDiff = daysInLastFullMonth + dDiff;
                }
                mDiff--;
            }
            if (mDiff < 0) {
                mDiff = 12 + mDiff;
                yDiff--;
            }

            function pluralize(num, word) {
                return num + ' ' + STRINGS[word + (num === 1 ? '' : 's')];
            }
            var result = [];

            if (yDiff) {
                result.push(pluralize(yDiff, 'year'));
            }
            if (mDiff) {
                if (yDiff) {
                    result.push(','); //HR-350
                }
                result.push(pluralize(mDiff, 'month'));
            }
            if (dDiff) {
                if (mDiff) {
                    result.push('and'); //HR-350
                }
                result.push(pluralize(dDiff, 'day'));
            }
//code commented to show only year,month and days on job summary
            /*
             if (hourDiff) {
             result.push(pluralize(hourDiff, 'hour'));
             }
             if (minDiff) {
             result.push(pluralize(minDiff, 'minute'));
             }
             if (secDiff) {
             result.push(pluralize(secDiff, 'second'));
             }
             */
            return result.join(STRINGS.delimiter);
        };
    }

    if (joinDate) {
      lengthEmployment(joinDate,finalDate,gid);
    }

    $(document).on("click", "#_qf_CustomData_upload", function() {
      $(document).ajaxSuccess(function(data, textStatus, jqXHR) {
        if(jqXHR.extraData) {
          if (jqXHR.extraData.class_name == 'CRM_Contact_Form_Inline_CustomData' && jqXHR.extraData.groupID == gid) {
            setTimeout(function(){
	      var joinDate = $('#custom-set-content-'+gid+' .crm-inline-block-content div:nth-child(2) .crm-custom-data').html();
	      var finalDate =$('#custom-set-content-'+gid+' .crm-inline-block-content div:nth-child(3) .crm-custom-data').html();
	      if (joinDate) {
                lengthEmployment(joinDate,finalDate,gid);
	      }
            },300 );
          }
        }
      });
    });
  });

  function lengthEmployment(joinDate,finalDate,gid) {
    var join_date = moment(joinDate,"MMMM DD, YYYY");
    var duration = '';
    if(finalDate) {
      var final_date = moment(finalDate,"MMMM DD, YYYY");
    }
    var now = moment();
    if (finalDate) {
      var diff =  final_date.diff(now, 'days');
    }
    else {
      var diff =  now.diff(join_date, 'days');
    }
    if (diff < 0 ) {
      duration = moment.preciseDiff(join_date,final_date);
    }
    else {
      duration = moment().preciseDiff(join_date);
    }
    var diffDate =  now.diff(join_date, 'days');
    if (diffDate <= 0 ) {
      duration = '0 days';
    }

    $('#initial_join_date').remove();
    var length = "<div class='crm-summary-row' id='initial_join_date'><div class='crm-label'>Length Of Employment</div><div class='crm-content crm-custom-data lengthEmployment'></div></div>";
    $('#custom-set-content-'+gid+' .crm-inline-block-content').append(length);
    $('.lengthEmployment').html(duration);
	if (finalDate && (diff < 0)) {
      $('.lengthEmployment').css({'color':'#FF0000'});
    }
  }
}(CRM.$, CRM._));
