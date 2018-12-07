<script type="text/javascript">
  {literal}
    // @NOTE This code was copied from `civicrm/templates/CRM/common/pager.tpl`,
    // refactored to satisfy the Styleguide and a couple of code changes were done:
    // 1) `.crm-pager` class was changed to `.staff-directory__results-list_pager`
    // 2) Naked code was wrapped or split and wrapped into functions
    CRM.$(function ($) {
      var containerSelector = '.staff-directory__results-list_pager';
      var formSelector =
        {/literal}
          {if empty($form.formClass)}
            '#crm-main-content-wrapper'
          {else}
            'form.{$form.formClass}'
          {/if}
        {literal};
      var $form = $(formSelector);
      var numPages = {/literal}{$pager->_response.numPages}{literal};
      var currentPage = {/literal}{$pager->_response.currentPage}{literal};
      var perPageCount = {/literal}{$pager->_perPage}{literal};
      var currentLocation = {/literal}{$pager->_response.currentLocation|json_encode}{literal};
      var spinning = null;
      var refreshing = false;

      (function () {
        initPaginator();
        initItemsPerPageAmountSelector();
        initSpinners();
        initSorters();
      })();

      /**
       * Changes the amount of items to show per page
       *
       * @param {String|Number} numberOfItems
       */
      function changeItemsAmountPerPage (numberOfItems) {
        numberOfItems = parseInt(numberOfItems, 10);

        if (
          isNaN(numberOfItems) ||
          numberOfItems < 1 ||
          numberOfItems === perPageCount
        ) {
          return;
        }

        refresh(currentLocation.replace(/&crmRowCount=\d+/, '')
          + '&crmRowCount=' + numberOfItems);
      }

      /**
       * Paginates to a specific page
       *
       * @param {String|Number} pageNumber
       */
      function paginate (pageNumber) {
        pageNumber = parseInt(pageNumber, 10);

        if (
          isNaN(pageNumber) ||
          pageNumber < 1 ||
          pageNumber > numPages ||
          pageNumber === currentPage
        ) {
          return;
        }

        refresh(currentLocation.replace(/crmPID=\d+/, 'crmPID=' + pageNumber));
      }

      /**
       * Refreshes the view
       *
       * @param {String} url
       */
      function refresh (url) {
        var options = url ? { url: url } : {};

        if (!refreshing) {
          refreshing = true;

          $form
            .off(containerSelector)
            .closest('.crm-ajax-container, #crm-main-content-wrapper')
            .crmSnippet(options)
            .crmSnippet('refresh');
        }
      }

      /**
       * Prevents form submission
       *
       * @param {Event} event
       */
      function preventSubmit (event) {
        if (event.keyCode == 13) {
          event.preventDefault();
          $(this).trigger('change');

          return false;
        }
      }

      /**
       * Initialises the selector for items per page amount
       */
      function initItemsPerPageAmountSelector () {
        $('input.crm-rows-per-page-select', $form)
          .spinner({
            min: 25,
            step: 25
          })
          .on('change', function () {
            changeItemsAmountPerPage($(this).spinner('value'));
          })
          .on('keyup keydown keypress', preventSubmit);
      }

      /**
       * Initialises paginator
       */
      function initPaginator () {
        $('input[name^=crmPID]', $form)
          .spinner({
            min: 1,
            max: numPages
          })
          .on('change', function () {
            paginate($(this).spinner('value'));
          })
          .on('keyup keydown keypress', preventSubmit);
      }

      /**
       * Initialises sorting
       */
      function initSorters () {
        $form
          .on('click' + containerSelector, [
            'a.crm-pager-link',
            '#alpha-filter a',
            'th a.sorting',
            'th a.sorting_desc',
            'th a.sorting_asc'
          ].join(), function (e) {
            refresh($(this).attr('href'));
            e.preventDefault();
          });
      }

      /**
       * Initialises spinners
       */
      function initSpinners () {
        $form
          .on('click' + containerSelector, 'a.ui-spinner-button', function (e) {
            var $el = $(this);
            var $input = $el.closest('.ui-spinner').find('input');
            var value = $input.spinner('value');
            var isItemsPerPageSelector = $input.hasClass('crm-rows-per-page-select');

            spinning !== null && window.clearTimeout(spinning);

            spinning = window.setTimeout(function () {
              if (!isItemsPerPageSelector) {
                paginate(value);
              } else {
                changeItemsAmountPerPage(value);
              }
            }, 200);
          });
      }
    });
  {/literal}
</script>
