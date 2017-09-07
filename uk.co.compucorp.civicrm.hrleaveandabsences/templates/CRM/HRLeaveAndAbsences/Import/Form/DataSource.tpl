{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2017                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{* Leave Request Import Wizard - Step 1 (upload data file) *}
{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller *}
<div id="bootstrap-theme" class="crm-leave-and-balance-import crm-activity-import-uploadfile-form-block">
  <div class="panel panel-default">
    <div class="panel-header">
      {* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
      {include file="CRM/common/WizardHeader.tpl"}

      <div class="action-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
      </div>
    </div>
    <div class="panel-body">
      <p class="alert alert-info">
        {ts}The Leave Request Import Wizard allows you to easily upload leave requests into CiviHR.{/ts}
        {help id="id-upload"}
      </p>
      <div class="row">

      </div>
      <div id="upload-file">
        <h3>{ts}Upload Data File{/ts}</h3>

        <div class="form-control-group row crm-activity-import-uploadfile-form-block-uploadFile">
          <div class="col-sm-3">{$form.uploadFile.label}</div>
          <div class="col-sm-6">{$form.uploadFile.html}</div>
          <div class="col-sm-6 col-sm-offset-3">
            <span class="description">{ts}File format must be comma-separated-values (CSV).{/ts}</span>
            <br />
            <span>{ts 1=$uploadSize}Maximum Upload File Size: %1 MB{/ts}</span>
          </div>
        </div>

        <div class="form-control-group row crm-activity-import-uploadfile-form-block-skipColumnHeader">
          <div class="col-sm-6 col-sm-offset-3">
            {$form.skipColumnHeader.html}{$form.skipColumnHeader.label}
          </div>
          <div class="col-sm-6 col-sm-offset-3">
            <span class="description">
              {ts}Check this box if the first row of your file consists of field names (Example: 'Contact ID', 'Absence ID', 'Absence Date').{/ts}
            </span>
          </div>
        </div>

        <div class="form-control-group row crm-import-datasource-form-block-fieldSeparator">
          <div class="col-sm-3">
            {$form.fieldSeparator.label}
            {help id='id-fieldSeparator' file='CRM/Contact/Import/Form/DataSource'}
          </div>
          <div class="col-sm-6">
            {$form.fieldSeparator.html}
          </div>
        </div>

        <div class="form-control-group row">
          <div class="col-sm-3">
            <label>{ts}Date Format{/ts}</label>
          </div>

          <div class="col-sm-6">
            <table class="table date-formats">
              <tr>
                {include file="CRM/Core/Date.tpl"}
              </tr>
            </table>
          </div>
        </div>

        {if $savedMapping}
          <div class="form-control-group row crm-activity-import-uploadfile-form-block-savedMapping">
            <div class="col-sm-3">
              {if $loadedMapping}
                {ts}Select a Different Field Mapping{/ts}
              {else}
                {ts}Load Saved Field Mapping{/ts}
              {/if}
            </div>

            <div class="col-sm-4">
              {$form.savedMapping.html}
            </div>

            <div class="col-sm-6 col-sm-offset-3">
              <span class="description">
                {ts}Select Saved Mapping or Leave blank to create a new One.{/ts}
              </span>
            </div>
          </div>
        {/if}
      </div>
    </div>

    <div class="panel-footer">
      <div class="row">
        <div class="action-buttons pull-right">
          {include file="CRM/common/formButtons.tpl" location="bottom"}
        </div>
      </div>
    </div>
  </div>
</div>
