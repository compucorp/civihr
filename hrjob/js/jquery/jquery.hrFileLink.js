// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function($, CRM) {

  /**
   * Display a link to a contract file.
   *
   * Usage:
   *   <div id="contract_file"  />
   *   <SCRIPT type="text/javascript">
   *     $('#contract_file').hrFileLink({id: 1});
   *   </SCRIPT>
   */
    $.fn.hrFileLink = function(options) {
    options.id || (options.id = '');
    options.entityTable || (options.entityTable = '');

    return this.each(function(){
      var fileUrl = CRM.url('civicrm/hrjob/file/display');
      var widgetEl = this;
      var activeEntityId = null;
      var setEntityId = function(newEntityId, entityTable) {
        if (activeEntityId != newEntityId) {
          activeEntityId = newEntityId;

          if (activeEntityId) {
            $(widgetEl).css({visibility: 'hidden'}); // don't allow input during ajax
            $.ajax({
              type: "POST",
              url : fileUrl,
              data: { entityID: newEntityId, entityTable: entityTable},
              async   : false,
              success : function(html){
                $(widgetEl)
                  .html(html)
                  .css({visibility: 'visible'});
              }
            });
          } else {
            $(widgetEl)
              .text('');
          }
        }
      };
      setEntityId(options.id, options.entityTable);
    });
    };
})(jQuery, CRM);
