<?php

namespace App\MyConstants;


class BmdGlobalConstants
{
    public const RETRIEVED_DATA_FROM_DB = 1001;
    public const RETRIEVED_DATA_FROM_CACHE = 1002;
    public const RETRIEVED_DATA_FROM_LOCAL_STORAGE = 1003;

    public const STORE_SITE_DATA_UPDATE_MAINTENANCE_PERIOD_START_HOUR = 23; // BMD-ON-STAGING: Change to 23.


    public const TAX_RATE = 0.13;

    
    /** BMD-TAGS: constants, consts, inventory, orders, cart */
    public const NUM_OF_DAILY_ORDER_ITEMS_LIMIT = 300;
    public const NUM_OF_DAILY_ORDERS_LIMIT = 100;

}
