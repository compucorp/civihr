<div id="bootstrap-theme" data-appraisals-app ng-controller="AppraisalsCtrl as main">
    <section id="{$module}" class="crm_page">
        <div class="crm_page__topbar">
            <div class="row">
                <div class="col-sm-8">
                    <ol class="breadcrumb">
                        <li>CiviHR</li>
                        <li>Appraisals</li>
                        <li class="active">Dashboard</li>
                    </ol>
                </div>
                <div class="col-sm-4 text-right">
                    <div class="crm_page__topbar__link">
                        <span class="fa fa-plus-circle"></span>&nbsp;
                        <a href ng-click="main.openAddAppraisalCycleModal()">Add Appraisal Cycle</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="crm_page__content row">
            <div class="col-md-2">
                <aside class="crm_page__sidebar">
                    <ul class="nav nav-pills nav-stacked nav-pills-stacked-sidebar">
                        <li class="active">
                            <a ui-sref="dashboard">
                                <i class="fa fa-list"></i> &nbsp; Dashboard
                            </a>
                        </li>
                        <li>
                            <a ui-sref="profile">
                                <i class="fa fa-search"></i>&nbsp; Profile
                            </a>
                        </li>
                        <li>
                            <a ui-sref="import">
                                <i class="fa fa-sign-in fa-flip-horizontal"></i> &nbsp; Import
                            </a>
                        </li>
                    </ul>
                </aside>
            </div>
            <div class="col-md-10">
                <main class="crm_page__main">
                    <div ui-view></div>
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
        </div>
    </section>
</div>
