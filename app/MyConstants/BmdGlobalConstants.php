<?php

namespace App\MyConstants;


class BmdGlobalConstants
{
    public const RETRIEVED_DATA_FROM_DB = 1001;
    public const RETRIEVED_DATA_FROM_CACHE = 1002;
    public const RETRIEVED_DATA_FROM_LOCAL_STORAGE = 1003;

    public const STORE_SITE_DATA_UPDATE_MAINTENANCE_PERIOD_START_HOUR = 23; //bmd-todo: ON-DEPLOYMENT: Change to 23.
}
