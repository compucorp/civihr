CRM.HRApp.module('JobTabApp.General', function(General, HRApp, Backbone, Marionette, $, _){
  General.EditView = Marionette.ItemView.extend({
    template: '#hrjob-general-template',
    templateHelpers: function() {
      return {
        'RenderUtil': CRM.HRApp.RenderUtil,
        'FieldOptions': CRM.FieldOptions.HRJob
      };
    },
    initialize: function() {
      CRM.HRApp.Common.mbind(this);
    },
    onRender: function() {
      var model = this.model;

      /**
       * Setup a contact-selector widget for use in CRUD forms.
       *
       * Usage:
       *   <INPUT type="hidden" name="my_contact_id" value="123" />
       *   <SCRIPT type="text/javascript">
       *     $('[name=my_contact_id]').crmContactSelector();
       *   </SCRIPT>
       *
       * The hidden element is the canonical representation of the selected contact.
       * A visible helper widget will be added for displaying/entering the contact's name.
       * The two widgets are sync'd.
       *
       * Cases to consider/test:
       *  - Initializing the widget based on some starting value
       *  - Unsetting the widget (making it blank)
       *  - If third party code updates the hidden value, then one must trigger a 'change'
       *    event to update the visible widget.
       */
      $.fn.crmContactField = function() {
        return this.each(function(){
          var contactUrl = CRM.url('civicrm/ajax/rest', 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1');
          var hiddenEl = this;
          var widgetEl = $('<input type="text" />');

          var activeContactId = null;
          var setContactId = function(newContactId) {
            if (newContactId != $(hiddenEl).val()) {
              $(hiddenEl).val(newContactId);
              $(hiddenEl).trigger('change');
            }
            if (activeContactId != newContactId) {
              activeContactId = newContactId;

              if (activeContactId) {
                // lookup the name
                $(widgetEl).css({visibility: 'hidden'}); // don't allow input during ajax
                $.ajax({
                  url     : contactUrl + '&id=' + newContactId,
                  async   : false,
                  success : function(html){
                    var htmlText = html.split( '|' , 2);
                    $(widgetEl).val(htmlText[0]);
                    $(widgetEl).css({visibility: 'visible'});
                  }
                });
              } else {
                // there is no name to lookup - just show a blank
                $(widgetEl).val('');
              }
            }
          };

          $(hiddenEl).after(widgetEl);
          $(widgetEl).autocomplete(contactUrl, {
            width: 200,
            selectFirst: false,
            minChars: 1,
            matchContains: true,
            delay: 400
          }).result(function(event, data) {
              activeContactId = data[1];
              setContactId(activeContactId);
            }).bind('change blur', function() {
              if (! $(widgetEl).val()) {
                activeContactId = '';
                setContactId(activeContactId);
              }
            });

          $(hiddenEl).bind('change', function(){
            setContactId($(this).val());
          });
          setContactId($(hiddenEl).val());
        });
      };

      this.$('.crm-contact-selector').crmContactField();
      //this.$('.crm-contact-selector').crmAutocomplete();
    }
  });
});
