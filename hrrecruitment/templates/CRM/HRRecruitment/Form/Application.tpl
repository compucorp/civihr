{*
 +--------------------------------------------------------------------+
 | CiviHR version 1.4                                                 |
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
{include file="CRM/UF/Form/Block.tpl"}
<div class="crm-section file_displayURL-section file_displayURL{$customname}-section"><div class="content">{$customFiles.$customname.displayURL}</div></div>
<div class="crm-section file_deleteURL-section file_deleteURL{$customname}-section"><div class="content">{$customFiles.$customname.deleteURL}</div></div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl"}
</div>
{*
 | Finds file labels that have file input fields and  adds
 | a data-file="true" attribute. Which is used to style the pseudo elements
 | for drag and drop box for file upload in the form.
*}
{literal}
<script type="text/javascript">
  CRM.$(function ($) {
    $("input[type='file']")
    .closest("div")
    .parent()
    .find('label')
    .attr('data-file','true');
  });
</script>
{/literal}
