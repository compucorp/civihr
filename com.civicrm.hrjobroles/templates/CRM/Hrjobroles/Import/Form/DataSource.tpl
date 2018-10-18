{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.5                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2014                                |
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
{* Job Roles Import Wizard - Step 1 (upload data file) *}
{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller *}
<div class="crm-block crm-form-block crm-jobroles-import-uploadfile-form-block">

 {* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
 {include file="CRM/common/WizardHeader.tpl"}

 <div class="help">
   <p>
     {ts}The Job Roles Import Wizard allows you to easily upload Job Roles from other applications into CiviHR. Job Contracts must already exist in your CiviHR database prior to importing Job Roles.{/ts}
   </p>
 </div>
 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
 <div id="upload-file">
 <h3>{ts}Upload Data File{/ts}</h3>
      <table class="form-layout-compressed">
        <tr class="crm-jobroles-import-uploadfile-form-block-uploadFile">
           <td class="label">{$form.uploadFile.label}</td>
           <td>{$form.uploadFile.html}<br />
                <span class="description">{ts}File format must be comma-separated-values (CSV).{/ts}</span><br /><span>{ts 1=$uploadSize}Maximum Upload File Size: %1 MB{/ts}</span>
           </td>
        </tr>
        <tr class="crm-jobroles-import-uploadfile-form-block-skipColumnHeader">
           <td class="label"></td>
           <td>{$form.skipColumnHeader.html}{$form.skipColumnHeader.label}<br />
               <span class="description">{ts}Check this box if the first row of your file consists of field names (Example: 'Contract ID', 'Role Title', 'Start Date').{/ts}</span>
           </td>
        </tr>
        <tr class="crm-import-datasource-form-block-fieldSeparator hiddenElement">
          <td class="label">{$form.fieldSeparator.label} {help id='id-fieldSeparator' file='CRM/Contact/Import/Form/DataSource'}</td>
          <td>{$form.fieldSeparator.html}</td>
        </tr>
        <tr>{include file="CRM/Core/Date.tpl"}</tr>
        {if $savedMapping}
        <tr class="crm-jobroles-import-uploadfile-form-block-savedMapping">
        <td>{if $loadedMapping}{ts}Select a Different Field Mapping{/ts}{else}{ts}Load Saved Field Mapping{/ts}{/if}</td>
           <td>{$form.savedMapping.html}<br />
              <span class="description">{ts}Select Saved Mapping or Leave blank to create a new One.{/ts}</span>
{/if}
           </td>
        </tr>
 </table>
 <div class="spacer"></div>
 </div>
 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
