// https://civicrm.org/licensing
(function($, _) {
  $(document).on('DOMNodeInserted','div.crm-designer-palette-region', function() {
    $('button#crm-designer-add-custom-set').hide();
    $('li[data-entity="case_1"]').hide();
    $.each(CRM.profileSelectorSet, function( key, value ) {
      $.each(value, function( keyName, cgID ) {
        $('li[data-section="cg_'+cgID+'"]').removeClass('jstree-closed').addClass('jstree-open').show();
      });
    });
  });
}(CRM.$, _));
