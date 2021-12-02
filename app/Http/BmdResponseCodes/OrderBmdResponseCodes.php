<?php

namespace App\Http\BmdResponseCodes;

use App\MyConstants\BmdGlobalConstants;



class OrderBmdResponseCodes
{
    public const INVALID_ORDER_RETURN_DATE_WINDOW = [
        'code' => 'ORDER-RESPONSE-CODE-1001', 
        'message' => 'INVALID_ORDER_RETURN_DATE_WINDOW', 
        'readableMessage' => 'The order-return-date is invalid.'
    ];
    
    public const NOT_ALLOWED_ORDER_STATUS_FOR_ORDER_RETURN = [
        'code' => 'ORDER-RESPONSE-CODE-1002', 
        'message' => 'NOT_ALLOWED_ORDER_STATUS_FOR_ORDER_RETURN', 
        'readableMessage' => 'The current order status is not allowed for order-return. Please wait for your order to be delivered before requesting an order return. Or for further assistance, you can email us at ' . BmdGlobalConstants::CUSTOMER_SERVICE_EMAIL . '.'
    ];
    
}