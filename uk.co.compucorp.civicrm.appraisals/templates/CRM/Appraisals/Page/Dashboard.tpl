<div id="bootstrap-theme" data-appraisals-app ng-controller="AppraisalsCtrl as mainCtrl">
    <div class="modal fade in" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h2 class="modal-title">Create New Appraisal Cycle</h2>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label for="cycle-name" class="control-label col-sm-3">
                                Cycle Name
                            </label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="cycle-name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cycle-type" class="control-label col-sm-3">
                                Cycle Type
                            </label>
                            <div class="col-sm-9">
                                <select class="form-control" id="cycle-type">
                                    <option value="">- select -</option>
                                    <option value="foo">foo</option>
                                    <option value="bar">bar</option>
                                    <option value="baz">baz</option>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <h3>
                            Cycle Period
                            <i class="fa fa-question-circle fa-1x fa-fw fa-color-primary"></i>
                        </h3>
                        <div class="form-group">
                            <label for="start-date" class="control-label col-sm-5">Cycle Start Date</label>
                            <div class="col-sm-4 col-md-offset-3">
                                <div class="input-group input-group-sm input-group-unstyled">
                                    <input type="text" class="form-control" id="start-date">
                                    <span class="input-group-addon fa fa-calendar"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end-date" class="control-label col-sm-5">Cycle End Date</label>
                            <div class="col-sm-4 col-md-offset-3">
                                <div class="input-group input-group-sm input-group-unstyled">
                                    <input type="text" class="form-control" id="end-date">
                                    <span class="input-group-addon fa fa-calendar"></span>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h3>Appraisal Deadlines</h3>
                        <div class="form-group">
                            <label for="self-appraisal-due" class="control-label col-sm-5">
                                Self Appraisal Due
                            </label>
                            <div class="col-sm-4 col-md-offset-3">
                                <div class="input-group input-group-sm input-group-unstyled">
                                    <input type="text" class="form-control" id="self-appraisal-due">
                                    <span class="input-group-addon fa fa-calendar"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="manager-appraisal-due" class="control-label col-sm-5">
                                Manager Appraisal Due
                            </label>
                            <div class="col-sm-4 col-md-offset-3">
                                <div class="input-group input-group-sm input-group-unstyled">
                                    <input type="text" class="form-control" id="manager-appraisal-due">
                                    <span class="input-group-addon fa fa-calendar"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="grade-due" class="control-label col-sm-5">
                                Grade
                            </label>
                            <div class="col-sm-4 col-md-offset-3">
                                <div class="input-group input-group-sm input-group-unstyled">
                                    <input type="text" class="form-control" id="grade-due">
                                    <span class="input-group-addon fa fa-calendar"></span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-outline text-uppercase" data-dismiss="modal">cancel</button>
                    <button type="button" class="btn btn-primary text-uppercase">create cycle</button>
                </div>
            </div>
        </div>
    </div>

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
                        <a href="#">Add Appraisal Cycle</a>
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
