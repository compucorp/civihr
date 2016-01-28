<table class="mtable" width="100%" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
    <tr style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
        <td width="80%" align="left" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
            {$row.label}
        </td>
        <td align="right" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
            {$row.keydate}
        </td>
    </tr>
    <tr style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
        <td align="left" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
            <table class="mtable subtable" width="100%" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;font-size: 12px;">
                <tr style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
                    <td width="100%" align="left" style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
                        Contact: <a href="{$baseUrl}/civicrm/contact/view?reset=1&cid={$row.contact_id}" style="color:#42b0cb;font-weight:normal;text-decoration:underline;">{$row.contact_name}</a>
                    </td>
                </tr>
            </table>
        </td>
        <td style="margin: 0px;padding: 0px;border: 0;vertical-align: top;">
            &nbsp;
        </td>
    </tr>
</table>
<hr style="height:0px;border:0px none;border-bottom:1px solid;border-color:#e0e0e0;margin:16px 0 10px;"/>
