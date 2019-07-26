(function ($, _, ts) {
  $(document).ready(function () {
    customizeQuickSearchField();
  });

  /**
   * Changes the placeholder text of the quicksearch field
   */
  function changeQuickSearchFieldPlaceholder () {
    $('#crm-qsearch .ui-autocomplete-input').attr('placeholder', ts('Quick Search'));
  }

  /**
   * Customizes the quick search field
   */
  function customizeQuickSearchField () {
    changeQuickSearchFieldPlaceholder();
    giveFocusToQuickSearchFieldWhenBlockGetsClick();
    manageCustomClassOfQuickSearchField();
  }

  /**
   * It gives focus to the quicksearch field when a click is registered on the
   * whole block (= on the icon as well) rather than just the field itself
   */
  function giveFocusToQuickSearchFieldWhenBlockGetsClick () {
    $('#crm-qsearch').click(function () {
      $('#crm-qsearch-input').focus();
    });
  }

  /**
   * Checks if the user has clicked outside of the quick search field
   * by analyzing the given click target
   *
   * @param {Element} target
   * @return {boolean}
   */
  function hasUserClickedOutsideQuickSearchField (target) {
    var $target = $(target);

    return !$target.is('#crm-qsearch') &&
      !$target.is('#root-menu-div') &&
      !$target.closest('#crm-qsearch, #root-menu-div').length;
  }

  /**
   * Checks if the quick search field currently has any value
   *
   * @return {boolean}
   */
  function isQuickSearchOnGoing () {
    var searchValue = $('#crm-qsearch-input').val() || '';

    return !!searchValue.trim();
  }

  /**
   * Manages handlers that deals with the custom class
   * that is used on the quick search field
   */
  function manageCustomClassOfQuickSearchField () {
    var customClass = 'search-ongoing';

    toggleCustomClassToQuickSearchFieldOnHover(customClass);
    removeCustomClassOnOutsideClick(customClass);
  }

  /**
   * Removes the given custom class when the user clicks
   * outside the quick search field (if there is no ongoing search)
   *
   * @param {string} customClass
   */
  function removeCustomClassOnOutsideClick (customClass) {
    $(document).on('click', function (event) {
      if (hasUserClickedOutsideQuickSearchField(event.target) && !isQuickSearchOnGoing()) {
        $('#civicrm-menu').removeClass(customClass);
      }
    });
  }

  /**
   * Toggles the given custom class to the quicksearch field
   * so that custom behaviour can be applied to it
   *
   * The class is removed only when the element
   * loses the hover AND it is empty (which means there is no ongoing search)
   *
   * @param {string} customClass
   */
  function toggleCustomClassToQuickSearchFieldOnHover (customClass) {
    $('#crm-qsearch').hover(
      function () {
        $('#civicrm-menu').addClass(customClass);
      },
      function () {
        var isSearchCriteriaPanelOpen = $('.crm-quickSearchField:visible', '#root-menu-div').length;

        if (!isQuickSearchOnGoing() && !isSearchCriteriaPanelOpen) {
          $('#civicrm-menu').removeClass(customClass);
        }
      }
    );
  }
}(CRM.$, CRM._, CRM.ts('org.civicrm.bootstrapcivihr')));
