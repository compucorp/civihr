{include file='CRM/Appraisals/Reminder/Header.tpl'}
<span class="h4" style="color:#202020;display:block;font-family:Arial;font-size:22px;font-weight:normal;line-height:100%;margin-bottom:10px;text-align:left;">{if $isReminder}Reminder: {/if}<a class="mlink" style="color:#42b0cb;font-weight:normal;text-decoration:underline;" href="{$activityUrl}">{$activityName}</a></span>
{if $notes}
<table class="mtable notes" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;margin-top:8px;" width="100%">
    <tr style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
        <td width="15%" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">Notes:</td>
        <td style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">{$notes}</td>
    </tr>
</table>
{/if}
<hr style="height:0px;border:0px none;border-bottom:1px solid;border-color:#e0e0e0;margin:16px 0 10px;"/>
<table class="mtable" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;" width="100%">
    <tr style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
        <td width="100%" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
            <table class="mtable" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;" width="100%">
                <tr style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
                    <td width="33%" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">{$appraisalCycleName}</td>
                    <td width="33%" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">Cycle ID: {$appraisalCycleId}</td>
                    <td width="33%" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">Period: {$appraisalCyclePeriod}</td>
                </tr>
            </table>
        </td>
    </tr>
{if $contact}
    <tr style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
        <td width="15%" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">Contact:</td>
        <td style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">{$contact}</td>
    </tr>
{/if}
{if $line_manager}
    <tr style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
        <td width="15%" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">Line Manager:</td>
        <td style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">{$line_manager}</td>
    </tr>
{/if}
{if $status}
    <tr style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
        <td width="15%" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">Status:</td>
        <td style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">{$status}</td>
    </tr>
{/if}
{if $due}
    <tr style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
        <td width="15%" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">Due:</td>
        <td style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">{$due}</td>
    </tr>
{/if}
</table>
<hr style="height:0px;border:0px none;border-bottom:1px solid;border-color:#e0e0e0;margin:16px 0 10px;"/>
{include file='CRM/Appraisals/Reminder/Footer.tpl'}