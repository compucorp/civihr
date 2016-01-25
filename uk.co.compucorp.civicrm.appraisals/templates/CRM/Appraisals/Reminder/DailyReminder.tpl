{include file='CRM/Tasksassignments/Reminder/Header.tpl'}
<span class="h4" style="color:#202020;display:block;font-family:Arial;font-size:22px;font-weight:normal;line-height:100%;margin-bottom:10px;text-align:left;">Daily Task Reminder</span>
You have {math equation="x + y" x=$reminder.todayMine|@count y=$reminder.today_keydates_count} task(s){if $settings.documents_tab.value} and document(s){/if} due today. You have {$reminder.overdue|@count} overdue task(s){if $settings.documents_tab.value} and document(s){/if}.

{if $reminder.overdue}
<span class="h4 dailyreminder overdue" style="color:#202020;display:block;font-family:Arial;font-size:22px;font-weight:normal;line-height:100%;margin-bottom:10px;text-align:left;margin-top: 16px;margin-bottom: 16px;">Overdue Tasks{if $settings.documents_tab.value} and Documents{/if} ({$reminder.overdue|@count})</span>
<hr style="height:0px;border:0px none;border-bottom:1px solid;border-color:#e0e0e0;margin:16px 0 10px;"/>
    {foreach from=$reminder.overdue item=row}
        {include file='CRM/Tasksassignments/Reminder/DailyReminderActivity.tpl'}
    {/foreach}
{/if}

{if $reminder.today_mine}
<span class="h4 dailyreminder today-mine" style="color:#202020;display:block;font-family:Arial;font-size:22px;font-weight:normal;line-height:100%;margin-bottom:10px;text-align:left;margin-top: 16px;margin-bottom: 16px;">Today's Tasks{if $settings.documents_tab.value} and Documents{/if} ({$reminder.today_mine|@count})</span>
<hr style="height:0px;border:0px none;border-bottom:1px solid;border-color:#e0e0e0;margin:16px 0 10px;"/>
    {foreach from=$reminder.today_mine item=row}
        {include file='CRM/Tasksassignments/Reminder/DailyReminderActivity.tpl'}
    {/foreach}
{/if}

{if $reminder.today_others}
<span class="h4 dailyreminder today-others" style="color:#202020;display:block;font-family:Arial;font-size:22px;font-weight:normal;line-height:100%;margin-bottom:10px;text-align:left;margin-top: 16px;margin-bottom: 16px;">Today's Tasks{if $settings.documents_tab.value} and Documents{/if} for others ({$reminder.today_others|@count})</span>
<hr style="height:0px;border:0px none;border-bottom:1px solid;border-color:#e0e0e0;margin:16px 0 10px;"/>
    {foreach from=$reminder.today_others item=row}
        {include file='CRM/Tasksassignments/Reminder/DailyReminderActivityOthers.tpl'}
    {/foreach}
{/if}

{if $reminder.coming_up}
<span class="h4 dailyreminder coming-up" style="color:#202020;display:block;font-family:Arial;font-size:22px;font-weight:normal;line-height:100%;margin-bottom:10px;text-align:left;margin-top: 16px;margin-bottom: 16px;">Coming up this week ({$reminder.coming_up|@count})</span>
<hr style="height:0px;border:0px none;border-bottom:1px solid;border-color:#e0e0e0;margin:16px 0 10px;"/>
    {foreach from=$reminder.coming_up item=row}
        {include file='CRM/Tasksassignments/Reminder/DailyReminderActivity.tpl'}
    {/foreach}
{/if}

{if $reminder.upcoming_keydates}
<span class="h4 dailyreminder upcoming-keydates" style="color:#202020;display:block;font-family:Arial;font-size:22px;font-weight:normal;line-height:100%;margin-bottom:10px;text-align:left;margin-top: 16px;margin-bottom: 16px;">Upcoming Key Dates ({$reminder.upcoming_keydates|@count})</span>
<hr style="height:0px;border:0px none;border-bottom:1px solid;border-color:#e0e0e0;margin:16px 0 10px;"/>
    {foreach from=$reminder.upcoming_keydates item=row}
        {include file='CRM/Tasksassignments/Reminder/DailyReminderKeyDate.tpl'}
    {/foreach}
{/if}
{include file='CRM/Tasksassignments/Reminder/Footer.tpl'}