<iframe id="reportsIframe" src="/reports{$reportName}?iframe=1" frameborder="0" scrolling="no" style="width: 100%"></iframe>
<script type="text/javascript">
  {literal}
  CRM.$(function() {
    jQuery('#reportsIframe').iFrameResize()
  });
  {/literal}
</script>
