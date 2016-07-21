// Create the namespaces if they don't exist
CRM.HRLeaveAndAbsencesApp = CRM.HRLeaveAndAbsencesApp || {};
CRM.HRLeaveAndAbsencesApp.Form = CRM.HRLeaveAndAbsencesApp.Form || {};


/**
 * This class represents the AbsencePeriod form.
 *
 * It wraps the form#AbsencePeriod element and handle things like
 * adding event listeners to it, updating fields values, showing
 * confirmation and more.
 */
CRM.HRLeaveAndAbsencesApp.Form.AbsencePeriod = (function($) {

  /**
   * Creates a new AbsencePeriod form object for the given form Element
   *
   * @constructor
   */
  function AbsencePeriod() {
    this._formElement = $('form#AbsencePeriod');
    this._saveButton = $('#_qf_AbsencePeriod_next-bottom');
    this._addEventListeners();
  }

  /**
   * Add events listeners to events specific to the form.
   *
   * @private
   */
  AbsencePeriod.prototype._addEventListeners = function() {
    this._saveButton.on('click', this._onSaveButtonClick.bind(this));
  };

  /**
   * Event handler called when the form's save button has been clicked
   *
   * @param event
   * @private
   */
  AbsencePeriod.prototype._onSaveButtonClick = function(event) {
    event.preventDefault();
    this._setSaveButtonValidatingState();
    this._validateOrder();
  };

  /**
   * Checks if there's another Absence Period with the same
   * Order number as the one the user is trying to add/edit.
   *
   * @private
   */
  AbsencePeriod.prototype._validateOrder = function() {
    var id = null;
    var params = {
      weight: document.getElementById('weight').value
    };

    if((id = document.getElementsByName('_id')[0].value)) {
      params.id = {"!=": id};
    }

    CRM.api3('AbsencePeriod', 'getcount', params)
      .done(this._validateOrderAPICallback.bind(this))
  };

  /**
   * This is the callback for the API call made by the validaOrder method.
   *
   * If the returned data shows we have another AbsencePeriod with the same
   * Order number, then a confirmation message is displayed. Otherwise, we
   * just submit the form.
   *
   * @param {Object} data - The JSON data returned by the API call
   * @private
   */
  AbsencePeriod.prototype._validateOrderAPICallback = function(data) {
    this._unsetSaveButtonValidatingState();
    if(data.result > 0) {
      this._showConfirmation();
    } else {
      this._submitForm();
    }
  };

  /**
   * Uses the CRM.confirm to ask the user confirmation if they really want to
   * save this Absence Period witht the same Order number of another existing
   * Period.
   *
   * @private
   */
  AbsencePeriod.prototype._showConfirmation = function() {
    var confirmationMessage = 'Another period has this order number. ' +
                              'If you choose to continue all periods ' +
                              'with the same or greater order number ' +
                              'will be increased by 1 and hence will ' +
                              'follow this period';
    CRM.confirm({
      title: ts('Alert'),
      message: ts(confirmationMessage),
      width: '30%',
      options: {
        yes: ts('Yes'),
        no: ts('No')
      }
    })
    .on('crmConfirm:yes', this._submitForm.bind(this))
  };

  /**
   * Submits the form by calling the form submit method.
   *
   * We need this because, in order to validate the Order number,
   * the event of the submit button was canceled on its onclick event handler.
   *
   * @private
   */
  AbsencePeriod.prototype._submitForm = function() {
    this._formElement.submit();
  };

  /**
   * Sets the Save button on the Validating state. That is, after the user clicks it
   * we disable the button (so it can't be clicked more than once) and change its
   * value to an text indicating that the validation is running.
   *
   * @private
   */
  AbsencePeriod.prototype._setSaveButtonValidatingState = function() {
    this._saveButton.attr('disabled', 'disabled');
    this._saveButton.val(ts('Validating order...'));
  };

  /**
   * Removes the Validating state of the Save button. This means the button will
   * be enabled again and its value will be changed to "Save".
   *
   * @private
   */
  AbsencePeriod.prototype._unsetSaveButtonValidatingState = function() {
    this._saveButton.removeAttr('disabled');
    this._saveButton.val(ts('Save'));
  };


  return AbsencePeriod;
})(CRM.$);
