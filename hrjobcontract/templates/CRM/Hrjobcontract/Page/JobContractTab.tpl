{assign var="module" value="hrjob-contract" }
{assign var="prefix" value="hrjc-" }

<div id="{$module}" hrjc-loader hrjc-loader-show="true">
    <div class="container" ng-view>
    </div>
</div>
{literal}
<script type="text/javascript">
    (function(){
        function hrjcInit(){
            document.dispatchEvent(typeof window.CustomEvent == "function" ? new CustomEvent('hrjcInit') : (function(){
                var e = document.createEvent('Event');
                e.initEvent('hrjcInit', true, true);
                return e;
            })());
        };
        hrjcInit();

        document.addEventListener('hrjcReady', function(){
            hrjcInit();
        });
    })();
</script>
{/literal}