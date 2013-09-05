// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function($, CRM) {

  /**
   * Display a link to a contact record.
   *
   * Usage:
   *   <A HREF="#" class="my_contact"  />
   *   <SCRIPT type="text/javascript">
   *     $('.my_contact').hrContactLink({cid: 123});
   *   </SCRIPT>
   */
  $.fn.hrContactLink = function(options) {
    options || (options = {cid: ''});
    return this.each(function(){
      var contactUrl = CRM.url('civicrm/ajax/rest', 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1');
      var widgetEl = this;

      var activeContactId = null;
      var setContactId = function(newContactId) {
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
                var viewContactUrl = CRM.url('civicrm/contact/view', {reset:1, cid: newContactId});
                $(widgetEl)
                  .attr('href', viewContactUrl)
                  .text(htmlText[0])
                  .css({visibility: 'visible'});
              }
            });
          } else {
            // there is no name to lookup - just show a blank
            $(widgetEl)
              .attr('href', '#')
              .text('');
          }
        }
      };

      setContactId(options.cid);
    });
  };

})(jQuery, CRM);
