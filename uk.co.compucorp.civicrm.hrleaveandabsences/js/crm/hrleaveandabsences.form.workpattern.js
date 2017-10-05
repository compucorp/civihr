/* globals ts, Inputmask */

// Create the namespaces if they don't exist
CRM.HRLeaveAndAbsencesApp = CRM.HRLeaveAndAbsencesApp || {};
CRM.HRLeaveAndAbsencesApp.Form = CRM.HRLeaveAndAbsencesApp.Form || {};

openTabWithErrorsIfPresented();

/**
 * This class represents the whole WorkPattern form.
 *
 * It instantiates the Weeks objects and handles form
 * specific operations, like the delete action.
 */
CRM.HRLeaveAndAbsencesApp.Form.WorkPattern = (function ($) {
  /**
   * The maximum number of weeks in a Work Pattern
   * @type {number}
   */
  var MAX_NUMBER_OF_WEEKS = 5;

  /**
   * Creates a new WorkPattern form
   *
   * @param {string} deleteUrl - The URL to be used by the delete action
   * @constructor
   */
  function WorkPattern (deleteUrl) {
    this._deleteUrl = deleteUrl;
    this._instantiateWeeks();
    this._addEventListeners();
  }

  /**
   * Instantiate all the Weeks on this form.
   *
   * For every element on the form containing a '.work-pattern-week' class,
   * a Week object will be created
   *
   * @private
   */
  WorkPattern.prototype._instantiateWeeks = function () {
    var that = this;
    this._weeks = [];
    $('.work-pattern-week').each(function (i, weekElement) {
      var week = that._instantiateWeek(i, weekElement);
      that._weeks.push(week);
    });
  };

  /**
   * Instantiates a single Week object for a given '.work-pattern-week' element.
   *
   * This method also takes care of fetching the weekVisibilityField, that is available
   * outside of the '.work-patter-week' element, and passes it to the Week constructor.
   *
   * @param {integer} weekIndex - The index of week on the Work Pattern form
   * @param {Object} weekElement - The '.work-pattern-week' element
   * @returns {Object} - A new Week instance
   * @private
   */
  WorkPattern.prototype._instantiateWeek = function (weekIndex, weekElement) {
    var fieldName = 'weeks[' + weekIndex + '][is_visible]';
    var visibilityField = document.getElementsByName(fieldName)[0];
    return new CRM.HRLeaveAndAbsencesApp.Form.WorkPattern.Week(weekIndex, weekElement, visibilityField);
  };

  /**
   * Add events listeners to events specific to the form.
   *
   * @private
   */
  WorkPattern.prototype._addEventListeners = function () {
    $('#number_of_weeks').on('change', this._onNumberOfWeeksChange.bind(this));
    $('.crm-button-type-delete').on('click', this._onDeleteButtonClick.bind(this));
  };

  /**
   * Event handler called when the value of the number of weeks select changes
   *
   * @param event
   * @private
   */
  WorkPattern.prototype._onNumberOfWeeksChange = function (event) {
    for (var i = 0; i < MAX_NUMBER_OF_WEEKS; i++) {
      if (i < parseInt(event.target.value)) {
        this._weeks[i].show();
      } else {
        this._weeks[i].hide();
      }
    }
  };

  /**
   * Event handler called when the delete button is clicked
   *
   * It shows a confirmation screen before deleting the pattern.
   * The confirm dialog callback is the method that actually deletes the pattern.
   *
   * @param event
   * @private
   */
  WorkPattern.prototype._onDeleteButtonClick = function (event) {
    event.preventDefault();
    CRM.confirm({
      title: ts('Delete Work Pattern'),
      message: ts('Are you sure you want to delete this Work Pattern?'),
      options: {
        yes: ts('Yes'),
        no: ts('No')
      }
    })
    .on('crmConfirm:yes', this._deleteWorkPattern.bind(this));
  };

  /**
   * This method actually executes the delete button action.
   *
   * It deletes the pattern by redirecting the browser to the given deleteUrl
   * passed to the WorkPattern constructor.
   *
   * Before the redirect, we need to disabled the changes notification for the
   * form. Since user already confirmed they want to delete the pattern,
   * there's no reason to notify the unsaved changes on the form.
   *
   * @private
   */
  WorkPattern.prototype._deleteWorkPattern = function () {
    this._disableFormChangesNotification();
    window.location = this._deleteUrl;
  };

  /**
   * Disable notification for unsaved changes on the form
   *
   * @private
   */
  WorkPattern.prototype._disableFormChangesNotification = function () {
    var form = $('form.CRM_HRLeaveAndAbsences_Form_WorkPattern');
    form.attr('data-warn-changes', 'false');
  };

  return WorkPattern;
})(CRM.$);

/**
 * This class represents a single Week on the Work Pattern form.
 *
 * It basically wraps a '.work-pattern-week' element and handles
 * the inner days.
 */
CRM.HRLeaveAndAbsencesApp.Form.WorkPattern.Week = (function ($) {
  /**
   * The number of days a Week should have
   *
   * @type {number}
   */
  var NUMBER_OF_DAYS = 7;

  /**
   * Constructs a new Week object
   *
   * @param {integer} weekIndex - The index of the week on the Work Pattern form. It's used to build the fields names.
   * @param {Object} weekElement - The actual '.work-pattern-week' element this object represents
   * @param {Object} weekVisibilityField - The hidden field used to keep track of this week visibility
   * @constructor
   */
  function Week (weekIndex, weekElement, weekVisibilityField) {
    this._weekIndex = weekIndex;
    this._weekElement = $(weekElement);
    this._weekVisibilityField = weekVisibilityField;
    this._numberOfHoursElement = this._weekElement.find('.number-of-hours');
    this._instantiateDays();
    this._addEventListeners();
  }

  /**
   * Instantiates all the Days instances for this week
   *
   * @private
   */
  Week.prototype._instantiateDays = function () {
    this._days = [];
    for (var i = 0; i < NUMBER_OF_DAYS; i++) {
      this._days[i] = this._instantiateDay(i);
    }
  };

  /**
   * Instantiate a single Day object.
   *
   * Since the day's fields are in the cells of a table, there isn't any
   * selector to match them all, so we use the weekIndex and the index
   * of the day in the Week to build the names of the fields that will
   * be used to create the Day object.
   *
   * @param {integer} dayIndex - The index of the Day in the week element
   * @returns {Object} - The new Day object
   * @private
   */
  Week.prototype._instantiateDay = function (dayIndex) {
    var prefix = 'weeks_' + this._weekIndex + '_days_' + dayIndex + '_';
    return new CRM.HRLeaveAndAbsencesApp.Form.WorkPattern.Day(
      document.getElementById(prefix + 'type'),
      document.getElementById(prefix + 'time_from'),
      document.getElementById(prefix + 'time_to'),
      document.getElementById(prefix + 'break'),
      document.getElementById(prefix + 'number_of_hours'),
      document.getElementById(prefix + 'leave_days')
    );
  };

  /**
   * Add events listeners to events specific to the week and its days.
   *
   * @private
   */
  Week.prototype._addEventListeners = function () {
    this._days.forEach(function (day) {
      day.on('numberofhourschange', this._calculateNumberOfHours.bind(this));
    }, this);
  };

  /**
   * This is the event handler called whenever one of this week's days
   * has its number of hours updated.
   *
   * We need to listen to this event to be able to calculates the total
   * number of hours for this week
   *
   * @private
   */
  Week.prototype._calculateNumberOfHours = function () {
    var totalNumberOfHours = 0.0;
    this._days.forEach(function (day) {
      totalNumberOfHours += day.getNumberOfHours();
    });

    this._numberOfHoursElement.text(totalNumberOfHours.toFixed(2));
  };

  /**
   * Checks if this week is visible on the form
   *
   * The visibility status of a week is controlled by a hidden
   * field on the form. If the field value is 1, it means the
   * week is visible
   *
   * @returns {boolean}
   */
  Week.prototype.isVisible = function () {
    return this._weekVisibilityField.value === '1';
  };

  /**
   * Makes the week visible on the form and set its days initial
   * state as:
   * - Monday to Friday as Working Days
   * - Saturday and Sunday as Weekend
   */
  Week.prototype.show = function () {
    if (!this.isVisible()) {
      this._weekElement.removeClass('hidden-week');
      this._setInitialWeekDaysValues();
      this._setWeekVisibleFlag(true);
    }
  };

  /**
   * Hides this week on the form.
   *
   * When hidding we need to erase and disabled all the days
   * of this week, to make sure they won't be submitted.
   */
  Week.prototype.hide = function () {
    if (this.isVisible()) {
      this._weekElement.addClass('hidden-week');
      this._resetWeekDays();
      this._setWeekVisibleFlag(false);
    }
  };

  /**
   * Updates the value of the visibility field
   *
   *
   * @param {boolean} flagValue - If true, the field value will be 1, otherwise it will 0
   * @private
   */
  Week.prototype._setWeekVisibleFlag = function (flagValue) {
    if (flagValue) {
      this._weekVisibilityField.value = 1;
    } else {
      this._weekVisibilityField.value = 0;
    }
  };

  /**
   * Sets the initial values for a week's days.
   *
   * The values are:
   * - Monday to Friday as Working Days
   * - Saturday and Sunday as Weekend
   *
   * @private
   */
  Week.prototype._setInitialWeekDaysValues = function () {
    var workingDays = this._days.slice(0, 5);
    var weekendDays = this._days.slice(5);
    workingDays.forEach(function (day) {
      day.setInitialValuesForWorkingDay();
    });
    weekendDays.forEach(function (day) {
      day.setAsWeekendDay();
    });
  };

  /**
   * Sets all days are non working days, erase their values and
   * disable their fields.
   *
   * @private
   */
  Week.prototype._resetWeekDays = function () {
    this._days.forEach(function (day) {
      day.setAsNonWorkingDay();
    });
  };

  return Week;
})(CRM.$);

/**
 * This class represents a single Day of a Week.
 *
 * It wraps of the fields that compose the Day (time_from, time_to,
 * break etc), and takes care of calculating the number of Hours for
 * a day and disabled/enable the fields according to the selected day
 * type.
 *
 */
CRM.HRLeaveAndAbsencesApp.Form.WorkPattern.Day = (function ($) {
  var NON_WORKING_DAY = 1;
  var WORKING_DAY = 2;
  var WEEKEND_DAY = 3;

  /**
   * Constructs a new Day object and makes sure to setUp every (like
   * adding masks and disabling non working days fields).
   *
   * All of its parameters are expected to be Element objects for the
   * days fields.
   *
   * @param {Object} typeField - The type select field of this day
   * @param {Object} timeFromField - The time from input field of this day
   * @param {Object} timeToField - The time to input field of this day
   * @param {Object} breakField - The break input field of this day
   * @param {Object} numberOfHoursField - The number of hours input field of this day
   * @param {Object} leaveDaysField - The leave days select field of this day
   * @constructor
   */
  function Day (typeField, timeFromField, timeToField, breakField, numberOfHoursField, leaveDaysField) {
    this._typeField = typeField;
    this._timeFromField = timeFromField;
    this._timeToField = timeToField;
    this._breakField = breakField;
    this._numberOfHoursField = numberOfHoursField;
    this._leaveDaysField = leaveDaysField;
    this._emitter = $({});
    this.on = $.proxy(this._emitter, 'on');
    this._addEventListeners();
    this._setFieldsMasks();
    if (+this._typeField.value === +NON_WORKING_DAY || +this._typeField.value === +WEEKEND_DAY) {
      this._setFieldsDisabledAttribute(true);
    }
  }

  /**
   * Add events listeners to events specific to the day's fields.
   *
   * @private
   */
  Day.prototype._addEventListeners = function () {
    $(this._typeField).on('change', this._onDayTypeChange.bind(this));
    $(this._numberOfHoursField).on('blur', this._roundNumberOfHours.bind(this));
  };

  /**
   * This event handler is called whenever the users changes the type of this day.
   *
   * If the selected type is a Non Working Day, then we should erase and disable
   * all the fields. Otherwise, we just enabled the fields.
   *
   * @param event
   * @private
   */
  Day.prototype._onDayTypeChange = function (event) {
    if (+event.target.value === +NON_WORKING_DAY || +event.target.value === +WEEKEND_DAY) {
      this._eraseFields();
      this._setFieldsDisabledAttribute(true);
    } else {
      this._setFieldsDisabledAttribute(false);
    }
  };

  /**
   * The Time From and Time To fields have masks to only allow the user do
   * enter times in the HH:MM format.
   *
   * The Break field has a mask that only allow decimal number to be
   * entered.
   *
   * Whenever the mask is complete, that is the user entered a value that
   * matchs the mask, the number of hours for the day will be calculated.
   *
   * @private
   */
  Day.prototype._setFieldsMasks = function () {
    var hourMask = Inputmask({
      'mask': '99:99',
      'oncomplete': this._calculateNumberOfHours.bind(this)
    });

    var breakMask = Inputmask({
      'alias': 'decimal',
      'rightAlign': false,
      'oncomplete': this._calculateNumberOfHours.bind(this)}
    );

    var numberOfHoursMask = Inputmask({
      'alias': 'decimal',
      'rightAlign': false
    });

    hourMask.mask(this._timeFromField);
    hourMask.mask(this._timeToField);
    breakMask.mask(this._breakField);
    numberOfHoursMask.mask(this._numberOfHoursField);
  };

  /**
   * The number of hours is calculated whenever the user enters valid values for
   * Time From, Time To and Break.
   *
   * If any of this fields are empty, the number of hours will also be empty.
   *
   * @private
   */
  Day.prototype._calculateNumberOfHours = function () {
    var secondsInPeriod = 0;
    var secondsInBreak = 0;
    var numberOfHours = 0;

    if (!this._timeFromField.value || !this._timeToField.value || !this._breakField.value) {
      this._numberOfHoursField.value = '';
      this._emitter.trigger('numberofhourschange');
      return;
    }

    var timeFrom = Date.parse('2016-01-01 ' + this._timeFromField.value);
    var timeTo = Date.parse('2016-01-01 ' + this._timeToField.value);
    var breakHours = parseFloat(this._breakField.value);

    if (!isNaN(timeFrom) && !isNaN(timeTo) && !isNaN(breakHours)) {
      secondsInPeriod = (timeTo - timeFrom) / 1000;
      secondsInBreak = breakHours * 3600;
      numberOfHours = (secondsInPeriod - secondsInBreak) / 3600;
      numberOfHours = numberOfHours < 0 ? 0 : numberOfHours.toFixed(2);
      this._numberOfHoursField.value = numberOfHours;
      this._roundNumberOfHours();
      this._emitter.trigger('numberofhourschange');
    }
  };

  /**
   * Set this as a Working Day, making its initial values
   * empty and the fields enabled.
   */
  Day.prototype.setInitialValuesForWorkingDay = function () {
    this._typeField.value = WORKING_DAY;
    this._eraseFields();
    this._setFieldsDisabledAttribute(false);
  };

  /**
   * Set this as a Non Working day, making it's initial
   * values empty and the fields disabled.
   */
  Day.prototype.setAsNonWorkingDay = function () {
    this._typeField.value = NON_WORKING_DAY;
    this._eraseFields();
    this._setFieldsDisabledAttribute(true);
  };

  /**
   * Set this as a Weekend day, making it's initial
   * values empty and the fields disabled.
   */
  Day.prototype.setAsWeekendDay = function () {
    this._typeField.value = WEEKEND_DAY;
    this._eraseFields();
    this._setFieldsDisabledAttribute(true);
  };

  /**
   * Returns the number of hours for this day
   *
   * @returns {float} - The number of hours for this day
   */
  Day.prototype.getNumberOfHours = function () {
    var numberOfHours = parseFloat(this._numberOfHoursField.value);
    return isNaN(numberOfHours) ? 0 : numberOfHours;
  };

  /**
   * Erases the values of all the fields of this day
   *
   * @private
   */
  Day.prototype._eraseFields = function () {
    this._timeFromField.value = '';
    this._timeToField.value = '';
    this._breakField.value = '';
    this._numberOfHoursField.value = '';
    this._leaveDaysField.value = 0;
  };

  /**
   * Rounds the Number Of Hours so it becomes dividable by 0.25 (15 minutes)
   */
  Day.prototype._roundNumberOfHours = function () {
    var numberOfHours = parseFloat(this._numberOfHoursField.value);
    var divider = 0.25; // 15 minutes

    if (!isNaN(numberOfHours)) {
      numberOfHours = Math.round(numberOfHours / divider) * divider;
    }

    this._numberOfHoursField.value = numberOfHours;
  };

  /**
   * Enable/Disable all the fields of this day
   *
   * The type field is not touched as it's the field the
   * user uses to select if this is a working day or not.
   *
   * @private
   */
  Day.prototype._setFieldsDisabledAttribute = function (disabled) {
    this._timeFromField.disabled = disabled;
    this._timeToField.disabled = disabled;
    this._breakField.disabled = disabled;
    this._numberOfHoursField.disabled = disabled;
    this._leaveDaysField.disabled = disabled;
  };

  return Day;
})(CRM.$);

/**
 * Opens tab with form errors if they are presented
 */
function openTabWithErrorsIfPresented () {
  var indexOfTabWithErrors;

  CRM.$(document).on('ready', function () {
    indexOfTabWithErrors =
      CRM.$('.tab-pane').index(CRM.$('.crm-error:first').closest('.tab-pane'));

    (indexOfTabWithErrors !== -1) && CRM.$('.nav-tabs a').eq(indexOfTabWithErrors).click();
  });
}
