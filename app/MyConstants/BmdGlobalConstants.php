<?php

namespace App\MyConstants;


class BmdGlobalConstants
{
    public const RETRIEVED_DATA_FROM_DB = 1001;
    public const RETRIEVED_DATA_FROM_CACHE = 1002;
    public const RETRIEVED_DATA_FROM_LOCAL_STORAGE = 1003;

    // BMD-ON-STAGING: Always set this to desired value like 23.
    public const STORE_SITE_DATA_UPDATE_MAINTENANCE_PERIOD_START_HOUR = 23;


    public const TAX_RATE = 0.13;


    /** BMD-TAGS: constants, consts, inventory, orders, cart */
    // BMD-ON-STAGING: Always set this to desired values.
    public const NUM_OF_DAILY_ORDERS_LIMIT = 50;
    public const NUM_OF_DAILY_ORDER_ITEMS_LIMIT = 200;



    // BMD-TAGS: email, order, queue, order-received
    // BMD-ON-STAGING
    public const EMAIL_SENDER_FOR_ORDER_RECEIVED = 'no-reply@asbdev.com';



    // BMD-TAGS: email, order, queue, order-received
    // BMD-ON-STAGING
    public const QUEUE_FOR_EMAILING_ORDER_DETAILS = 'TestBmd-QEmailUserOrderDetails';
}
