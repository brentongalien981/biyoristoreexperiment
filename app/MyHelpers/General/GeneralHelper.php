<?php

namespace App\MyHelpers\General;

use App\MyConstants\BmdGlobalConstants;


class GeneralHelper
{
    public static function isWithinStoreSiteDataUpdateMaintenancePeriod($nowInDateObj = null) {
        $nowInDateObj = $nowInDateObj ?? getdate();
        if ($nowInDateObj['hours'] >= BmdGlobalConstants::STORE_SITE_DATA_UPDATE_MAINTENANCE_PERIOD_START_HOUR) {
            return true;
        }
        return false;
    }
}
