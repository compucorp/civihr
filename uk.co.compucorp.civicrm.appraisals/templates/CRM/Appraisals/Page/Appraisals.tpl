{assign var="module" value="appraisals" }
{assign var="prefix" value="appr-" }

<div id="bootstrap-theme">
    <section id="{$module}" class="chr_page">
        <div class="crm_page__topbar">
            <div class="row">
                <div class="col-md-8">
                    <ol class="breadcrumb">
                        <li>CiviHR</li>
                        <li>Appraisals</li>
                        <li class="active">Dashboard</li>
                    </ol>
                </div>
                <div class="col-md-4 text-right">
                    <div class="crm_page__topbar__link">
                        <span class="fa fa-plus-circle"></span>&nbsp;
                        <a href="#">Add Appraisal Cycle</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="crm_page__content">
            <aside class="crm_page__sidebar">
                <ul class="nav nav-pills nav-stacked nav-pills-stacked-sidebar">
                    <li class="active">
                        <a href="#">
                            <i class="fa fa-list"></i>
                            &nbsp; Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fa fa-search"></i>&nbsp; Profile
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fa fa-sign-in fa-flip-horizontal"></i>
                            &nbsp; Import
                        </a>
                    </li>
                </ul>
            </aside>
            <main class="crm_page__main">
                <footer class="crm_page__footer">
                    <span class="crm_page__footer__logo">
                        <i class="icomoon-logo--full"></i>
                    </span>
                    <p>
                        CiviHR is openly available under the GNU AGPL License
                    </p>
                </footer>
            </main>
        </div>
    </section>
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
