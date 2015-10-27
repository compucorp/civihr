{assign var="module" value="civitasks" }
{assign var="prefix" value="ct-" }

<div id="{$module}" ng-controller="MainCtrl" ct-spinner ct-spinner-show>
    <div class="container fade-in" ng-view>
    </div>
</div>
{literal}
    <script type="text/javascript">
        (function(){
            function taInit(){
                var detail = {
                    'app': 'appTasks',
                    'module': 'civitasks'
                };

                document.dispatchEvent(typeof window.CustomEvent == "function" ? new CustomEvent('taInit', {
                    'detail': detail
                }) : (function(){
                    var e = document.createEvent('CustomEvent');
                    e.initCustomEvent('taInit', true, true, detail);
                    return e;
                })());
            };
            taInit();

            document.addEventListener('taReady', function(){
                taInit();
            });
        })();
    </script>
{/literal}