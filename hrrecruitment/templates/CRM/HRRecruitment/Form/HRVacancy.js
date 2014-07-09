// https://civicrm.org/licensing
(function($, _) {
  $(document).on('DOMNodeInserted','div.crm-designer-palette-region', function() {
    $('button#crm-designer-add-custom-set').hide();
    $.each(CRM.profileSelectorSet, function( key, value ) {
      $.each(value, function( keyName, cgID ) {
        $('li[data-section="cg_'+cgID+'"]').removeClass('jstree-closed').addClass('jstree-open');
      });
    });
  });
}(CRM.$, _));
