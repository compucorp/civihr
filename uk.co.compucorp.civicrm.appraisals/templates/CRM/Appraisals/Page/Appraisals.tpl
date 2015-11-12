{assign var="module" value="appraisals" }
{assign var="prefix" value="appr-" }

Appraisals Main Template

<div id="{$module}">
    <div class="container" ng-view>
    </div>
</div>
{literal}
<script type="text/javascript">
    (function(){
        function apprInit(){
            document.dispatchEvent(typeof window.CustomEvent == "function" ? new CustomEvent('apprInit') : (function(){
                var e = document.createEvent('Event');
                e.initEvent('apprInit', true, true);
                return e;
            })());
        };
        apprInit();

        document.addEventListener('apprReady', function(){
            apprInit();
        });
    })();
</script>
{/literal}