// Create the namespaces if they don't exist
CRM.HRLeaveAndAbsencesApp = CRM.HRLeaveAndAbsencesApp || {};
CRM.HRLeaveAndAbsencesApp.Form = CRM.HRLeaveAndAbsencesApp.Form || {};


/**
 * This class represents the whole ManageEntitlements form.
 *
 */
CRM.HRLeaveAndAbsencesApp.Form.ManageEntitlements = (function($) {

  /**
   * Creates a new ManageEntitlements form instance
   * @constructor
   */
  function ManageEntitlements() {
    this._filtersElement = $('.entitlement-calculation-filters');
    this._listElement = $('.entitlement-calculation-list');
    this._formElement = $('.CRM_HRLeaveAndAbsences_Form_ManageEntitlements');
    this._overrideFilter = this.OVERRIDE_FILTER_BOTH;
    this._absenceTypeFilter = [];
    this._proposedEntitlements = [];
    this._setUpOverrideFilters();
    this._instantiateProposedEntitlements();
    this._instantiateComments();
    this._addEventListeners();
  }

  //Constants for the Override Filter values
  ManageEntitlements.prototype.OVERRIDE_FILTER_OVERRIDDEN = 1;
  ManageEntitlements.prototype.OVERRIDE_FILTER_NON_OVERRIDDEN = 2;
  ManageEntitlements.prototype.OVERRIDE_FILTER_BOTH = 3;

  /**
   * Transforms the radios of the Override Filter into a jQuery UI button set
   *
   * @private
   */
  ManageEntitlements.prototype._setUpOverrideFilters = function() {
    this._filtersElement.find('.override-filters').buttonset();
  };

  /**
   * Creates new ProposedEntitlement instances for every calculation on the list
   *
   * @private
   */
  ManageEntitlements.prototype._instantiateProposedEntitlements = function() {
    var that = this;
    this._listElement.find('.proposed-entitlement').each(function(i, element) {
      that._proposedEntitlements.push(
        new CRM.HRLeaveAndAbsencesApp.Form.ManageEntitlements.ProposedEntitlement($(element))
      );
    });
  };

  /**
   * Creates new Comment instances for every calculation on the list
   *
   * @private
   */
  ManageEntitlements.prototype._instantiateComments = function() {
    this._listElement.find('td.comment').each(function(i, element) {
      new CRM.HRLeaveAndAbsencesApp.Form.ManageEntitlements.Comment($(element))
    });
  };

  /**
   * Add event listeners to events triggered by elements of managed by this class
   *
   * @private
   */
  ManageEntitlements.prototype._addEventListeners = function() {
    this._filtersElement.find('.override-filter').on('change', this._onOverrideFilterChange.bind(this));
    this._filtersElement.find('.absence-type-filter select').on('change', this._onAbsenceTypeFilterChange.bind(this));
    this._filtersElement.find('.export-csv-action').on('click', this._onExportCSVClick.bind(this));
    this._listElement.find('thead .proposed-entitlement-header .add-one-day').on('click', this._onAddOneDayClick.bind(this));
    this._listElement.find('thead .proposed-entitlement-header .copy-to-all').on('click', this._onCopyToAllClick.bind(this));
    this._listElement.find('tbody tr td').on('click', this._onListRowClick.bind(this));
  };

  /**
   * This is the event listener for when the value of the Override Filter changes.
   *
   * If the new value is different from the previous one, the list is updated to
   * reflect the option selected.
   *
   * @param {Object} event
   * @private
   */
  ManageEntitlements.prototype._onOverrideFilterChange = function(event) {
    var newOverrideFilterValue = parseInt(event.target.value);
    if(newOverrideFilterValue != this._overrideFilter) {
      this._overrideFilter = newOverrideFilterValue;
      this._updateList();
    }
  };

  /**
   * This is the event listener for when the value of the Absence Type filter changes.
   *
   * @param {Object} event
   * @private
   */
  ManageEntitlements.prototype._onAbsenceTypeFilterChange = function(event) {
    this._absenceTypeFilter = $(event.target).val() || [];
    this._updateList();
  };

  /**
   * Updates the entitlements list to reflect the actual filter selection
   *
   * @private
   */
  ManageEntitlements.prototype._updateList = function() {
    this._showAll();
    this._filterEntitlementsByAbsenceType();
    this._filterEntitlementsByOverride();
  };

  /**
   * Makes all the entitlements visible
   *
   * @private
   */
  ManageEntitlements.prototype._showAll = function() {
    this._listElement.find('tr').removeClass('hidden');
  };

  /**
   * Filters the list of entitlements according to the selected values of the
   * Absence Type filter.
   *
   * @private
   */
  ManageEntitlements.prototype._filterEntitlementsByAbsenceType = function() {
    if(this._absenceTypeFilter.length > 0) {
      var selectors = [];
      this._absenceTypeFilter.forEach(function(absenceTypeID) {
        selectors.push("tr[data-absence-type='" + absenceTypeID + "']");
      });

      this._listElement
        .find('tr:not(.hidden)')  // finds all the visible rows
        .not(selectors.join(',')) // that doesn't match the select types
        .addClass('hidden');      // and hide them
    }
  };

  /**
   * Filters the list of entitlements according to the selected value of the
   * Override Filter.
   *
   * @private
   */
  ManageEntitlements.prototype._filterEntitlementsByOverride = function() {
    switch(this._overrideFilter) {
      case this.OVERRIDE_FILTER_OVERRIDDEN:
        this._hideNonOverriddenEntitlements();
        break;
      case this.OVERRIDE_FILTER_NON_OVERRIDDEN:
        this._hideOverriddenEntitlements();
        break;
    }
  };

  /**
   * Hides every entitlement that was not overridden
   *
   * @private
   */
  ManageEntitlements.prototype._hideNonOverriddenEntitlements = function() {
    this._listElement
      .find('.proposed-entitlement .override-checkbox:not(:checked)')
      .parents('tr:not(.hidden)')
      .addClass('hidden');
  };

  /**
   * Hides every entitlement that was overridden
   *
   * @private
   */
  ManageEntitlements.prototype._hideOverriddenEntitlements = function() {
    this._listElement
      .find('.proposed-entitlement .override-checkbox:checked')
      .parents('tr')
      .addClass('hidden');
  };

  /**
   * This is the event handler for when the user clicks on a row of the calculations
   * list.
   *
   * It shows the user a popup with details of the selected calculation. Even if the
   * proposed entitlement was overridden, we display the original calculation.
   *
   * @param event
   * @private
   */
  ManageEntitlements.prototype._onListRowClick = function(event) {
    // If the user clicked to override and entitlement or to add a comment,
    // we don't show the calculationDescription
    if($(event.currentTarget).hasClass('proposed-entitlement') ||
      $(event.currentTarget).hasClass('comment')) {
      return;
    }

    var calculationDescription = ts('' +
      '((Base contractual entitlement + Public Holidays) ' +
      '* ' +
      '(No. of working days to work / No. of working days in period)) = ' +
      '(Period pro rata) + (Brought Forward days) = Period Entitlement'
    );
    var calculationDetails = event.currentTarget.parentNode.dataset.calculationDetails;

    if(!calculationDetails) {
      return;
    }

    CRM.confirm({
      title: ts('Calculation details'),
      message: calculationDescription + '<br /><br />' + calculationDetails,
      width: '70%',
      options: {}
    });
  };

  /**
   * This is the event handler for when the user clicks on the "Export to CSV"
   * link.
   *
   * The CSV is basically the entitlement calculation page in a CSV format, so
   * we get it by submitting the form with a "export_csv" flag set. Another
   * reason for getting the CSV by submitting the form is that, this way, we
   * can get any entitlement that was overridden and include it in the exported
   * file.
   *
   * @param event
   * @private
   */
  ManageEntitlements.prototype._onExportCSVClick = function(event) {
    event.preventDefault();

    this._formElement.find('#export_csv').val(1); //set the export csv flag
    this._formElement.submit();
    this._formElement.find('#export_csv').val(''); //resets the export csv flag
  };

  /**
   * This is the event handler for when the user clicks on the "Add one day" button,
   * on the "New Proposed Entitlement" header.
   *
   * It loops through all the Proposed Entitlements, and overrides them adding one
   * more day to its current value.
   *
   * @private
   */
  ManageEntitlements.prototype._onAddOneDayClick = function() {
    this._proposedEntitlements.forEach(function(proposedEntitlement) {
      proposedEntitlement.addOneDay();
    });
  };

  /**
   * This is the event handler for when the user clicks on the "Copy to All" button,
   * on the "New Proposed Entitlement" header.
   *
   * It gets the value of the proposed entitlement on the first row and then loops
   * through all the Proposed Entitlements setting them to this value.
   *
   * @private
   */
  ManageEntitlements.prototype._onCopyToAllClick = function() {
    var firsEntitlementValue = this._proposedEntitlements[0].getCurrentValue();
    this._proposedEntitlements.forEach(function(proposedEntitlement) {
      proposedEntitlement.setValue(firsEntitlementValue);
    });
  };

  return ManageEntitlements;

})($);


/**
 * This class wraps the small set of controls that each calculation on the ManageEntitlements
 * list has to allow the user to edit/override the proposed entitlement.
 */
CRM.HRLeaveAndAbsencesApp.Form.ManageEntitlements.ProposedEntitlement = (function($) {

  /**
   * Creates a new ProposedEntitlement instance
   *
   * @param {Object} element - The element wrapping all of the proposed entitlement controls
   * @constructor
   */
  function ProposedEntitlement(element) {
    this._overrideButton = element.find('button');
    this._overrideCheckbox = element.find('input[type="checkbox"]');
    this._overrideField = element.find('input[type="text"]');
    this._proposedValue = element.find('.proposed-value');
    this._init();
  }

  /**
   * Initializes the component
   *
   * @private
   */
  ProposedEntitlement.prototype._init = function() {
    if(this._overrideCheckbox.is(':checked')) {
      this._makeEntitlementEditable();
    }
    this._setupOverrideFieldMask();
    this._addEventListeners();
  };

  /**
   * Sets the proposed entitlement value to the one given.
   *
   * If this proposed entitlement is not overridden, it will be
   * marked as so.
   *
   * @param {float} newValue
   */
  ProposedEntitlement.prototype.setValue = function(newValue) {
    if(!this._isOverridden) {
      this._makeEntitlementEditable();
    }

    this._overrideField.val(newValue);
  };

  /**
   * Adds one day to the current Proposed Entitlement value.
   *
   * If this proposed entitlement is not overridden, it will be
   * marked as so.
   */
  ProposedEntitlement.prototype.addOneDay = function() {
    var currentEntitlement = this.getCurrentValue();
    this.setValue(currentEntitlement + 1);
  };

  /**
   * Returns the current value of this Proposed Entitlement.
   *
   * If it has been overridden, the overridden value will be returned,
   * otherwise the original value will be returned.
   *
   * If the overridden value is an invalid number, 0 will be returned.
   *
   * @returns {float}
   */
  ProposedEntitlement.prototype.getCurrentValue = function() {
    var currentValue;

    if(this._isOverridden) {
      currentValue = this._overrideField.val();
    } else {
      currentValue = this._proposedValue.text();
    }

    currentValue = parseFloat(currentValue);

    if(isNaN(currentValue)) {
      currentValue = 0;
    }

    return currentValue;
  };

  ProposedEntitlement.prototype._setupOverrideFieldMask = function() {
    var mask = Inputmask({
      'alias': 'decimal',
      'rightAlign': false
    });

    mask.mask(this._overrideField);
  };

  /**
   * Add event listeners to the override button and the checkbox
   *
   * @private
   */
  ProposedEntitlement.prototype._addEventListeners = function() {
    this._overrideButton.on('click', this._onOverrideButtonClick.bind(this));
    this._overrideCheckbox.on('click', this._onOverrideCheckboxClick.bind(this));
  };

  /**
   * This is the event handler for when the override/edit button is clicked.
   *
   * It makes the field to override the proposed entitlement visible;
   *
   * @private
   */
  ProposedEntitlement.prototype._onOverrideButtonClick = function() {
    this._makeEntitlementEditable();
  };


  /**
   * This is the event handle for when the override checkbox is clicked.
   *
   * If it's checked, then we make the entitlement editable, by showing the
   * field to override the proposed entitlement. Otherwise, we hide the field
   * and display the edit button.
   *
   * @param event
   * @private
   */
  ProposedEntitlement.prototype._onOverrideCheckboxClick = function(event) {
    if(event.target.checked) {
      this._makeEntitlementEditable();
    } else {
      this._displayProposedEntitlementValue();
    }
  };

  /**
   * This make the proposed entitlement editable. That is, the field to override the
   * proposed value is displayed, the edit field, the edit button and the proposed
   * value is hidden, and the checkbox gets checked.
   *
   * @private
   */
  ProposedEntitlement.prototype._makeEntitlementEditable = function() {
    this._overrideButton.hide();
    this._proposedValue.hide();
    this._overrideField
      .val(this._proposedValue.text())
      .show()
      .focus();
    this._overrideCheckbox.prop('checked', true);
    this._isOverridden = true;
  };

  /**
   * This is used to hide the fields to override the entitlement, and display the original
   * proposed entitlement again.
   *
   * @private
   */
  ProposedEntitlement.prototype._displayProposedEntitlementValue = function() {
    this._overrideButton.show();
    this._proposedValue.show();
    this._overrideField
      .val('')
      .hide();
    this._overrideCheckbox.prop('checked', false);
    this._isOverridden = false;
  };

  return ProposedEntitlement;
})($);

/**
 * This class encapsulates all the logic to add/edit comments to an entitlement.
 *
 * It displays the "Add/Edit comment" dialog when the user clicks on the comment action
 * button and updates the entitlement comment in case the user add or edit it.
 *
 */
CRM.HRLeaveAndAbsencesApp.Form.ManageEntitlements.Comment = (function($) {

  /**
   * Creates a new Comment instance
   * @param {Object} commentElement - A jQuery object of the TD wrapping the comment field and button
   * @constructor
   */
  function Comment(commentElement) {
    this._commentElement = commentElement;
    this._addCommentButton = this._commentElement.find('.add-comment');
    this._commentTextarea = this._commentElement.find('.comment-text');
    this._addEventListeners();
  }

  /**
   * Attach handlers to events listened by this object
   *
   * @private
   */
  Comment.prototype._addEventListeners = function() {
    this._addCommentButton.on('click', this._onAddCommentClick.bind(this));
  };

  /**
   * This is the event handler for when the add comment button is clicked.
   *
   * It will display the dialog box and updates the entitlement's comment if
   * the user saved the changes made.
   *
   * @private
   */
  Comment.prototype._onAddCommentClick = function() {
    CRM.HRLeaveAndAbsencesApp.Form.ManageEntitlements.CommentDialog.show(
      this._getCurrentValue(),
      function(comment) {
        this._setCurrentValue(comment);
      }.bind(this)
    );
  };

  /**
   * Returns this comment current value
   *
   * @returns {String}
   * @private
   */
  Comment.prototype._getCurrentValue = function() {
    return this._commentTextarea.val();
  };

  /**
   * Sets the current value for this comment
   *
   * @param comment
   * @private
   */
  Comment.prototype._setCurrentValue = function(comment) {
    this._commentTextarea.val(comment);
  };

  return Comment;
})($);

/**
 * This Object wraps the logic to create and display the "Add Comment" dialog.
 *
 * As there's a single dialog that can be used to add/edit comments for every
 * entitlement, we don't have a constructor function here. We only return a
 * plain object with a single method name "show", that can be used to show the
 * dialog to user, with the given comment.
 */
CRM.HRLeaveAndAbsencesApp.Form.ManageEntitlements.CommentDialog = (function($) {

  var dialogSelector = '#add-comment-dialog';
  var textAreaSelector = dialogSelector + ' .calculation_comment';

  /**
   * Erases the value of the dialog's textarea
   */
  function eraseCommentInDialog() {
    $(textAreaSelector).val('');
  }

  /**
   * Closes the dialog
   */
  function closeDialog() {
    $(dialogSelector).dialog("close");
  }

  /**
   * Returns the comment entered in the dialog's textarea
   *
   * @returns {String}
   */
  function getCommentInDialog() {
    return $(textAreaSelector).val();
  }

  /**
   * Sets the given comment as the value of the dialog's textarea
   *
   * @param {String} comment
   */
  function setCommentInDialog(comment) {
    $(textAreaSelector).val(comment)
  }

  /**
   * Shows the dialog to the user.
   *
   * The given callback will be called if the user closes the dialog
   * by clicking on the "Save" button. The current comment in the
   * dialog textarea will be passed as an argument to the callback.
   *
   * @param {Function} callback
   */
  function showDialog(callback) {
    $('#add-comment-dialog').dialog({
      'width': '500px',
      'close': eraseCommentInDialog,
      buttons: [
        {
          text: ts('Cancel'),
          click: closeDialog
        },
        {
          text: ts('Save'),
          click: function () {
            callback(getCommentInDialog());
            closeDialog();
          }
        }
      ]
    });
  }

  return {
    /**
     * Shows the dialog to the user.
     *
     * The given comment will be displayed in the dialog's textarea.
     *
     * The given callback will be called if the user closes the dialog
     * by clicking on the "Save" button. The current comment in the
     * dialog textarea will be passed as an argument to the callback.
     *
     * @param {String} comment
     * @param {Function} callback
     */
    show: function(comment, callback) {
      setCommentInDialog(comment);
      showDialog(callback);
    }
  };

})($);
