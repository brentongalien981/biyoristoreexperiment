<?php

namespace App\MyConstants;


class BmdGlobalConstants
{
    // BMD-ON-STAGING
    public const BMD_SELLER_NAME = 'ASB Inc.';


    public const RETRIEVED_DATA_FROM_DB = 1001;
    public const RETRIEVED_DATA_FROM_CACHE = 1002;
    public const RETRIEVED_DATA_FROM_LOCAL_STORAGE = 1003;

    // BMD-ON-STAGING: Always set this to desired value like 23.
    public const STORE_SITE_DATA_UPDATE_MAINTENANCE_PERIOD_START_HOUR = 23;


    public const TAX_RATE = 0.13;
    public const NUM_OF_SEC_IN_DAY = 60 * 60 * 24;
    public const NUM_OF_ORDER_RETURN_WINDOW_DAYS = 45;


    /** BMD-TAGS: constants, consts, inventory, orders, cart */
    // BMD-ON-STAGING: Always set this to desired values.
    public const NUM_OF_DAILY_ORDERS_LIMIT = 50;
    public const NUM_OF_DAILY_ORDER_ITEMS_LIMIT = 200;

    // BMD-ON-STAGING
    // NOTE: Whenever you change this, make sure to edit both frontend and backend constant values.
    public const PAYMENT_TO_FUNDS_PERIOD = 1;
    public const ORDER_PROCESSING_PERIOD = 1;



    // BMD-TAGS: email, order, queue, order-received
    // BMD-ON-STAGING
    public const EMAIL_SENDER_FOR_GENERAL_PURPOSES = 'no-reply@asbdev.com';
    public const EMAIL_SENDER_FOR_ORDER_RECEIVED = 'no-reply@asbdev.com';
    public const EMAIL_FOR_ORDER_EMAILS_TRACKER = 'bren.baga@asbdev.com'; // Maybe change this to orderemailstracker@bmd.com

    public const CUSTOMER_SERVICE_EMAIL = 'customerservice@penguinjam.com';



    // BMD-TAGS: email, order, queue, order-received
    // BMD-ON-STAGING
    public const QUEUE_FOR_EMAILING_ORDER_DETAILS = 'TestBmd-QEmailUserOrderDetails';
}
