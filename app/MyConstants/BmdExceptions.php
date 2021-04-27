<?php

namespace App\MyConstants;


class BmdExceptions
{
    /** TAGS: order, cart, checkout */
    public const DEFAULT_INITIAL_FINALIZING_ORDER_EXCEPTION = ['id' => -8000, 'name' => 'DEFAULT_INITIAL_FINALIZING_ORDER_EXCEPTION', 'description' => 'Xxx'];
    public const INVALID_CART_EXCEPTION = ['id' => -8001, 'name' => 'INVALID_CART_EXCEPTION', 'description' => 'Xxx'];
    public const CART_HAS_NO_ITEM_EXCEPTION = ['id' => -8002, 'name' => 'CART_HAS_NO_ITEM_EXCEPTION', 'description' => 'Xxx'];

}