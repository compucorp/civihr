<?php

class CRM_Hrjobcontract_Callback
{
    public static function getJSON($param)
    {
        return json_decode($param, true);
    }
}
