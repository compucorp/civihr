{assign var="module" value="appraisals" }
{assign var="prefix" value="appr-" }

<div id="bootstrap-theme">
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
            <div class="col-sm-2">
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
            </div>
            <div class="col-sm-10">
                <main class="crm_page__main">
                    <div class="row">
                        <div class="col-sm-4">
                            <section class="panel panel-default">
                                <header class="panel-heading">
                                    <h2 class="panel-title">Active Cycles</h2>
                                </header>
                                <div class="panel-body text-center">
                                    9
                                </div>
                                <footer class="panel-footer">
                                    <dl class="dl-horizontal dl-horizontal-inline">
                                        <dt>Total Cycles:</dt>
                                        <dd>20</dd>
                                    </dl>
                                </footer>
                            </section>
                        </div>
                        <div class="col-sm-4">
                            <section class="panel panel-default">
                                <header class="panel-heading">
                                    <h2 class="panel-title">Status</h2>
                                </header>
                                <div class="panel-body">
                                </div>
                                <footer class="panel-footer">
                                    <dl class="dl-horizontal dl-horizontal-inline">
                                        <dt>Total number of appraysals in all cycles:</dt>
                                        <dd>248</dd>
                                    </dl>
                                </footer>
                            </section>
                        </div>
                        <div class="col-sm-4">
                            <section class="panel panel-default">
                                <header class="panel-heading">
                                    <h2 class="panel-title">Grades</h2>
                                </header>
                                <div class="panel-body"></div>
                            </section>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <section class="panel panel-default">
                                <header class="panel-heading clearfix">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <h2 class="panel-title">
                                                Appraisal Cycles
                                            </h2>
                                        </div>
                                        <div class="col-sm-4 text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-secondary-outline active">Active</button>
                                                <button class="btn btn-secondary-outline">Inactive</button>
                                                <button class="btn btn-secondary-outline">All</button>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 text-right">
                                            <a href="#">Filter Appraisals</a>
                                        </div>
                                    </div>
                                </header>
                                <div class="panel-body">
                                    <form class="form-inline">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="cycle-name">Cycle Name:</label>
                                                    <input type="text" class="form-control" id="cycle-name">
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label for="cycle-type">Cycle type:</label>
                                                    <select class="form-control" id="cycle-type">
                                                        <option value="">- select -</option>
                                                        <option value="foo">foo</option>
                                                        <option value="bar">bar</option>
                                                        <option value="baz">baz</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label for="cycle-status">Cycle Status:</label>
                                                    <select class="form-control" id="cycle-status">
                                                        <option value="">- select -</option>
                                                        <option value="foo">foo</option>
                                                        <option value="bar">bar</option>
                                                        <option value="baz">baz</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="self-due-from">Self Appraisal Due From:</label>
                                                    <input type="text" class="form-control" id="self-due-from">
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label for="self-due-to">To:</label>
                                                    <input type="text" class="form-control" id="self-due-to">
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="manager-due-from">Manager Appraisal Due From:</label>
                                                    <input type="text" class="form-control" id="manager-due-from">
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label for="manager-due-to">To:</label>
                                                    <input type="text" class="form-control" id="manager-due-to">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="grade-due-from">Grade Due From:</label>
                                                    <input type="text" class="form-control" id="grade-due-from">
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label for="grade-due-to">To:</label>
                                                    <input type="text" class="form-control" id="grade-due-to">
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="appraisal-due-from">Appraisal Period From:</label>
                                                    <input type="text" class="form-control" id="appraisal-due-from">
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="form-group">
                                                    <label for="appraisal-due-to">To:</label>
                                                    <input type="text" class="form-control" id="appraisal-due-to">
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </section>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <section class="panel panel-primary panel--dashboard-appraisals">
                                <header class="panel-heading">
                                    <h2 class="panel-title">
                                        10 Overdue Appraisals and 80 Due this week
                                    </h2>
                                </header>
                                <article class="panel panel-default panel-default-plain-inverted chr_appraisal-cycle-summary">
                                    <header class="panel-heading">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <h3 class="panel-title panel-title-sm">
                                                    Appraisal Cycle 1 (Active)
                                                </h3>
                                            </div>
                                            <div class="col-sm-6 text-right">
                                                <div class="chr_appraisal-cycle-summary__details">
                                                    <span class="chr_appraisal-cycle-summary__meta">100 Appraisals</span>
                                                    <span class="chr_appraisal-cycle-summary__meta">45% complete</span>
                                                    <div class="chr_appraisal-cycle-summary__actions dropdown" dropdown>
                                                        <a class="chr_appraisal-cycle-summary__actions__toggle dropdown-toggle" href="#" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
                                                            <li>
                                                                <a href="#">
                                                                    <i class="fa fa-chain"></i> View Cycle
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#">
                                                                    <i class="fa fa-pencil"></i> Edit Cycle
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                    </header>
                                    <div class="panel-body">
                                        <dl class="dl-inline">
                                            <dt>Cycle ID:</dt>
                                            <dd>42131</dd>
                                            <dt>Period:</dt>
                                            <dd>01/01/2014 - 01/01/2015</dd>
                                            <dt>Next Due:</dt>
                                            <dd>Manager Appraisal (23/23/2023)</dd>
                                        </dl>
                                    </div>
                                    <footer class="panel-footer">

                                    </footer>
                                </article>
                                <article class="panel panel-default panel-default-plain-inverted chr_appraisal-cycle-summary">
                                    <header class="panel-heading">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <h3 class="panel-title panel-title-sm">
                                                    Appraisal Cycle 2 (Active)
                                                </h3>
                                            </div>
                                            <div class="col-sm-6 text-right">
                                                <div class="chr_appraisal-cycle-summary__details">
                                                    <span class="chr_appraisal-cycle-summary__meta">100 Appraisals</span>
                                                    <span class="chr_appraisal-cycle-summary__meta">45% complete</span>
                                                    <div class="chr_appraisal-cycle-summary__actions dropdown" dropdown>
                                                        <a class="chr_appraisal-cycle-summary__actions__toggle dropdown-toggle" href="#" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
                                                            <li>
                                                                <a href="#">
                                                                    <i class="fa fa-chain"></i> View Cycle
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#">
                                                                    <i class="fa fa-pencil"></i> Edit Cycle
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                    </header>
                                    <div class="panel-body">
                                        <dl class="dl-inline">
                                            <dt>Cycle ID:</dt>
                                            <dd>42131</dd>
                                            <dt>Period:</dt>
                                            <dd>01/01/2014 - 01/01/2015</dd>
                                            <dt>Next Due:</dt>
                                            <dd>Self Appraisal (23/23/2023)</dd>
                                        </dl>
                                    </div>
                                    <footer class="panel-footer">

                                    </footer>
                                </article>
                                <article class="panel panel-default panel-default-plain-inverted chr_appraisal-cycle-summary">
                                    <header class="panel-heading">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <h3 class="panel-title panel-title-sm">
                                                    Appraisal Cycle 3 (Active)
                                                </h3>
                                            </div>
                                            <div class="col-sm-6 text-right">
                                                <div class="chr_appraisal-cycle-summary__details">
                                                    <span class="chr_appraisal-cycle-summary__meta">100 Appraisals</span>
                                                    <span class="chr_appraisal-cycle-summary__meta">45% complete</span>
                                                    <div class="chr_appraisal-cycle-summary__actions dropdown" dropdown>
                                                        <a class="chr_appraisal-cycle-summary__actions__toggle dropdown-toggle" href="#" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
                                                            <li>
                                                                <a href="#">
                                                                    <i class="fa fa-chain"></i> View Cycle
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#">
                                                                    <i class="fa fa-pencil"></i> Edit Cycle
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                    </header>
                                    <div class="panel-body">
                                        <dl class="dl-inline">
                                            <dt>Cycle ID:</dt>
                                            <dd>42131</dd>
                                            <dt>Period:</dt>
                                            <dd>01/01/2014 - 01/01/2015</dd>
                                            <dt>Next Due:</dt>
                                            <dd>Self Appraisal (23/23/2023)</dd>
                                        </dl>
                                    </div>
                                    <footer class="panel-footer">

                                    </footer>
                                </article>
                                <article class="panel panel-default panel-default-plain-inverted chr_appraisal-cycle-summary">
                                    <header class="panel-heading">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <h3 class="panel-title panel-title-sm">
                                                    Appraisal Cycle 4 (Active)
                                                </h3>
                                            </div>
                                            <div class="col-sm-6 text-right">
                                                <div class="chr_appraisal-cycle-summary__details">
                                                    <span class="chr_appraisal-cycle-summary__meta">100 Appraisals</span>
                                                    <span class="chr_appraisal-cycle-summary__meta">45% complete</span>
                                                    <div class="chr_appraisal-cycle-summary__actions dropdown" dropdown>
                                                        <a class="chr_appraisal-cycle-summary__actions__toggle dropdown-toggle" href="#" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
                                                            <li>
                                                                <a href="#">
                                                                    <i class="fa fa-chain"></i> View Cycle
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#">
                                                                    <i class="fa fa-pencil"></i> Edit Cycle
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                    </header>
                                    <div class="panel-body">
                                        <dl class="dl-inline">
                                            <dt>Cycle ID:</dt>
                                            <dd>42131</dd>
                                            <dt>Period:</dt>
                                            <dd>01/01/2014 - 01/01/2015</dd>
                                            <dt>Next Due:</dt>
                                            <dd>Self Appraisal (23/23/2023)</dd>
                                        </dl>
                                    </div>
                                    <footer class="panel-footer">

                                    </footer>
                                </article>
                                <article class="panel panel-default panel-default-plain-inverted chr_appraisal-cycle-summary">
                                    <header class="panel-heading">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <h3 class="panel-title panel-title-sm">
                                                    Appraisal Cycle 5 (Active)
                                                </h3>
                                            </div>
                                            <div class="col-sm-6 text-right">
                                                <div class="chr_appraisal-cycle-summary__details">
                                                    <span class="chr_appraisal-cycle-summary__meta">100 Appraisals</span>
                                                    <span class="chr_appraisal-cycle-summary__meta">45% complete</span>
                                                    <div class="chr_appraisal-cycle-summary__actions dropdown" dropdown>
                                                        <a class="chr_appraisal-cycle-summary__actions__toggle dropdown-toggle" href="#" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
                                                            <li>
                                                                <a href="#">
                                                                    <i class="fa fa-chain"></i> View Cycle
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#">
                                                                    <i class="fa fa-pencil"></i> Edit Cycle
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                    </header>
                                    <div class="panel-body">
                                        <dl class="dl-inline">
                                            <dt>Cycle ID:</dt>
                                            <dd>42131</dd>
                                            <dt>Period:</dt>
                                            <dd>01/01/2014 - 01/01/2015</dd>
                                            <dt>Next Due:</dt>
                                            <dd>Self Appraisal (23/23/2023)</dd>
                                        </dl>
                                    </div>
                                    <footer class="panel-footer">

                                    </footer>
                                </article>
                                <div class="panel--dashboard-appraisals__show-more text-center">
                                    <button class="btn btn-default text-uppercase">
                                        show more
                                    </button>
                                </div>
                            </section>
                        </div>
                    </div>
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
