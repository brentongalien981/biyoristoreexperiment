<?php

namespace App\Http\BmdResponseCodes;



class OrderBmdResponseCodes
{
    public const INVALID_ORDER_RETURN_DATE_WINDOW = [
        'code' => 'ORDER-RESPONSE-CODE-1001', 
        'message' => 'INVALID_ORDER_RETURN_DATE_WINDOW', 
        'readableMessage' => 'The order-return-date has passed.'
    ];
    
}