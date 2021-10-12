<?php

namespace App\Http\Controllers;

use App\Http\BmdCacheObjects\InventoryOrderLimitsCacheObject;
use App\Http\BmdCacheObjects\ShippingServiceLevelModelCollectionCacheObject;
use App\MyConstants\BmdGlobalConstants;
use App\MyHelpers\Shipping\MyShippingPackageManager;
use Exception;
use Illuminate\Http\Request;
use App\ShippingServiceLevel;

class CustomizedEasyPost extends Controller
{

    private const CUSTOM_FORCED_INTERNAL_EXCEPTION = ['code' => -501, 'name' => 'CUSTOM_FORCED_INTERNAL_EXCEPTION'];
    private const CUSTOM_INTERNAL_EXCEPTION = ['code' => -500, 'name' => 'CUSTOM_INTERNAL_EXCEPTION'];

    private const DEFAULT_INITIAL_RESULT = ['code' => 0, 'name' => 'DEFAULT_INITIAL_RESULT'];
    private const ORIGIN_ADDRESS_EXCEPTION = ['code' => -1, 'name' => 'ORIGIN_ADDRESS_EXCEPTION'];
    private const DESTINATION_ADDRESS_EXCEPTION = ['code' => -2, 'name' => 'DESTINATION_ADDRESS_EXCEPTION'];
    private const NULL_PREDEFINED_PACKAGE_EXCEPTION = ['code' => -3, 'name' => 'NULL_PREDEFINED_PACKAGE_EXCEPTION'];
    private const EMPTY_CART_EXCEPTION = ['code' => -4, 'name' => 'EMPTY_CART_EXCEPTION'];
    private const COULD_NOT_FIND_SHIPMENT_RATES = ['code' => -5, 'name' => 'COULD_NOT_FIND_SHIPMENT_RATES'];

    private const NUM_OF_DAILY_ORDERS_LIMIT_REACHED = ['code' => -6, 'name' => 'NUM_OF_DAILY_ORDERS_LIMIT_REACHED'];
    private const NUM_OF_DAILY_ORDER_ITEMS_LIMIT_REACHED = ['code' => -7, 'name' => 'NUM_OF_DAILY_ORDER_ITEMS_LIMIT_REACHED'];

    private const INVALID_DESTINATION_COUNTRY_EXCEPTION = ['code' => -8, 'name' => 'INVALID_DESTINATION_COUNTRY_EXCEPTION'];

    private const EMPTY_REQUEST_PARAMS = ['code' => -400, 'name' => 'EMPTY_REQUEST_PARAMS'];

    private const ENTIRE_PROCESS_OK = ['code' => 1, 'name' => 'ENTIRE_PROCESS_OK'];

    /**
     * BMD-SENSITIVE-INFO
     * BMD-ON-STAGING: Update this everytime when it's needed.
     */
    private const COMPANY_INFO = [
        'owner_name' => 'Bren Baga',
        'street1' => '50 Thorncliffe Park Drive',
        'street2' => 'Unit 105',
        'city' => 'East York',
        'state' => 'ON',
        'country' => 'CA',
        'zip' => 'M4H1K4',
        'phone' => '4164604026'
    ];



    // BMD-FOR-DEBUG
    // BMD-ON-STAGING: Comment-out.
    public function test(Request $request)
    {
        //
        if (!isset($request->reducedCartItemsData) || count($request->reducedCartItemsData) === 0) {
            throw new Exception(self::EMPTY_REQUEST_PARAMS['name']);
        }


        $packageInfo = MyShippingPackageManager::getPackageInfo($request->reducedCartItemsData);

        return [
            'msg' => 'In CLASS: CustomizedEasyPost, METHOD: checkCartItems()',
            'cartItems' => $request->reducedCartItemsData,
            'packageInfo' => $packageInfo
        ];
    }



    public function jsonifyObj($obj)
    {
        $jsonifiedObj = [];

        foreach ($obj as $k => $v) {
            $jsonifiedObj[$k] = $v;
        }

        return $jsonifiedObj;
    }



    public function setOriginAddress(&$params)
    {
        $originAddressParams = [
            'verify' => [true],
            'name' => self::COMPANY_INFO['owner_name'],
            // 'company' => self::COMPANY_INFO['company'], // BMD-ON-STAGING
            // 'email' => self::COMPANY_INFO['email'], // BMD-ON-STAGING
            'street1' => self::COMPANY_INFO['street1'],
            'street2' => self::COMPANY_INFO['street2'],
            'city' => self::COMPANY_INFO['city'],
            'state' => self::COMPANY_INFO['state'],
            'country' => self::COMPANY_INFO['country'],
            'zip' => self::COMPANY_INFO['zip'],
            'phone' => self::COMPANY_INFO['phone'],
        ];

        $originAddress = \EasyPost\Address::create($originAddressParams);


        //
        $isAddressValid = $originAddress->verifications->delivery->success;

        if (!$isAddressValid) {

            $returnedAddressErrors = $originAddress->verifications->delivery->errors;

            $originAddressErrors = [];

            // Parse each error.
            foreach ($returnedAddressErrors as $e) {

                $ithErrorObj = [];
                foreach ($e as $eField => $eVal) {
                    $ithErrorObj[$eField] = $eVal;
                }

                $originAddressErrors[] = $ithErrorObj;
            }


            $params['customErrors']['originAddressErrors'] = $originAddressErrors;
            $params['entireProcessComments'][] = self::ORIGIN_ADDRESS_EXCEPTION['name'];
            $params['resultCode'] = self::ORIGIN_ADDRESS_EXCEPTION['code'];


            throw new Exception(self::ORIGIN_ADDRESS_EXCEPTION['name']);
        }


        return $originAddress;
    }



    private function validateDestinationAddress(&$params)
    {
        $shippingInfo = json_decode($params['shippingInfo']);

        switch (strtolower($shippingInfo->country)) {
            case 'us':
            case 'usa':
            case 'united states':
            case 'united states of america':
            case 'united states america':
            case 'ca':
            case 'canada':
                return true;
            default:
                return false;
        }
    }



    public function setDestinationAddress(&$params)
    {
        $shippingInfo = json_decode($params['shippingInfo']);

        $destinationsAddressParams = [
            'verify' => [true],
            'name' => $shippingInfo->firstName . " " . $shippingInfo->lastName,
            'email' => $shippingInfo->email,
            'phone' => $shippingInfo->phone,
            // 'company' => 'Vandelay Industries',
            'street1' => $shippingInfo->street,
            'city' => $shippingInfo->city,
            'state' => $shippingInfo->province,
            'country' => $shippingInfo->country,
            'zip' => $shippingInfo->postalCode

        ];

        $destinationAddress = \EasyPost\Address::create($destinationsAddressParams);

        $isDestinationAddressValid = $destinationAddress->verifications->delivery->success;

        if (!$isDestinationAddressValid) {

            $destinationAddressVerificationErrors = $destinationAddress->verifications->delivery->errors;

            $destinationAddressErrors = [];
            foreach ($destinationAddressVerificationErrors as $e) {

                $ithErrorObj = [];
                foreach ($e as $field => $val) {
                    $ithErrorObj[$field] = $val;
                }

                $destinationAddressErrors[] = $ithErrorObj;
            }

            $params['customErrors']['destinationAddressErrors'] = $destinationAddressErrors;
            $params['entireProcessComments'][] = self::DESTINATION_ADDRESS_EXCEPTION['name'];
            $params['resultCode'] = self::DESTINATION_ADDRESS_EXCEPTION['code'];

            throw new Exception(self::DESTINATION_ADDRESS_EXCEPTION['name']);
        }

        return $destinationAddress;
    }



    public function setParcel(&$params, $usePredefinedPackageProp = true)
    {
        if (isset($params['packageInfo'])) {
            $packageInfo = $params['packageInfo'];
        } else {
            $packageInfo = MyShippingPackageManager::getPackageInfo($params['reducedCartItemsData']);
        }


        if (!isset($packageInfo)) {
            $params['resultCode'] = self::NULL_PREDEFINED_PACKAGE_EXCEPTION['code'];
            throw new Exception(self::NULL_PREDEFINED_PACKAGE_EXCEPTION['name']);
        }

        $parcel = null;
        if ($usePredefinedPackageProp) {
            $parcel = \EasyPost\Parcel::create([
                "predefined_package" => $packageInfo['predefinedPackageName'],
                "weight" => $packageInfo['totalWeight']
            ]);

            $params['entireProcessComments'][] = 'Parcel-obj created using prop: predefined_package.';
        } else {
            // I'm doing this because EasyPost doesn't seem to return a shipment-obj with rates
            // sometimes if the parcel-obj has the property "predefined_package" when created.
            $parcel = \EasyPost\Parcel::create([
                "length" => $packageInfo['dimensions']['length'],
                "width" => $packageInfo['dimensions']['width'],
                "height" => $packageInfo['dimensions']['height'],
                "weight" => $packageInfo['totalWeight']
            ]);

            $params['entireProcessComments'][] = 'Parcel-obj created using package-info-dimensions.';
        }


        $params['packageInfo'] = $packageInfo;
        return $parcel;
    }



    public function setShipment($data)
    {
        $shipment = \EasyPost\Shipment::create([
            "to_address" => $data['destinationAddress'],
            "from_address" => $data['originAddress'],
            "parcel" => $data['parcel']
        ]);

        return $shipment;
    }



    public function getParsedRateObjs($rates)
    {
        $parsedRateObjs = [];
        foreach ($rates as $r) {

            $aParsedRateObj = [];
            foreach ($r as $rField => $rVal) {
                $aParsedRateObj[$rField] = $rVal;
            }

            $parsedRateObjs[] = $aParsedRateObj;
        }

        return $parsedRateObjs;
    }



    /**
     * For each rate, add value to field "delivery_days" if the retrieved rate has null.
     *
     * @param [] $parsedRateObjs
     * @return []
     */
    public function getModifiedRateObjs($parsedRateObjs)
    {
        $modifiedRateObjs = [];
        $shippingServiceLevels = ShippingServiceLevelModelCollectionCacheObject::getUpdatedModelCollection()->data;

        foreach ($parsedRateObjs as $r) {

            if ($r['carrier'] != "UPS") {
                continue;
            }

            $aModifiedRateObj = $r;
            if (!isset($r['delivery_days'])) {
                $deliveryDays = ShippingServiceLevel::findDeliveryDaysForService($r['service'], $shippingServiceLevels);

                if ($deliveryDays == 0) {
                    continue;
                }

                $aModifiedRateObj['delivery_days'] = $deliveryDays;
            }

            $modifiedRateObjs[] = $aModifiedRateObj;
        }

        return $modifiedRateObjs;
    }



    public function getEfficientShipmentRates($modifiedRateObjs)
    {
        // Get the cheapest of all rates.
        $cheapestWithFastestRate = null;
        $cheapestRate = 1000000.0;
        $fastestDeliveryDays = 365;
        foreach ($modifiedRateObjs as $r) {
            if ((floatval($r['rate']) < $cheapestRate) ||
                (floatval($r['rate']) == $cheapestRate && $r['delivery_days'] < $fastestDeliveryDays)
            ) {
                $cheapestRate = floatval($r['rate']);
                $fastestDeliveryDays = $r['delivery_days'];
                $cheapestWithFastestRate = $r;
            }
        }


        // Get the fastest rate that has the cheapest.
        $fastestWithCheapestRate = null;
        $cheapestRate = 1000000.0;
        $fastestDeliveryDays = 365;
        foreach ($modifiedRateObjs as $r) {
            if (($r['delivery_days'] < $fastestDeliveryDays) ||
                ($r['delivery_days'] == $fastestDeliveryDays && floatval($r['rate']) < $cheapestRate)
            ) {
                $cheapestRate = floatval($r['rate']);
                $fastestDeliveryDays = $r['delivery_days'];
                $fastestWithCheapestRate = $r;
            }
        }


        $efficientShipmentRates = null;
        if ($cheapestWithFastestRate['id'] == $fastestWithCheapestRate['id']) {
            $efficientShipmentRates = [$cheapestWithFastestRate];
        } else {
            $efficientShipmentRates = [$cheapestWithFastestRate, $fastestWithCheapestRate];
        }

        return $efficientShipmentRates;
    }



    private function doesShipmentHaveRates($shipment)
    {
        if (!isset($shipment->rates) || count($shipment->rates) == 0) {
            return false;
        }
        return true;
    }



    private function checkInventoryOrderLimits(&$entireProcessParams)
    {
        $cacheKey = 'inventoryOrderLimits';
        $inventoryOrderLimitsCO = new InventoryOrderLimitsCacheObject($cacheKey);

        if ($inventoryOrderLimitsCO->data['numOfDailyOrders'] >= BmdGlobalConstants::NUM_OF_DAILY_ORDERS_LIMIT) {
            $entireProcessParams['resultCode'] = self::NUM_OF_DAILY_ORDERS_LIMIT_REACHED['code'];
            throw new Exception(self::NUM_OF_DAILY_ORDERS_LIMIT_REACHED['name']);
        }

        if ($inventoryOrderLimitsCO->data['numOfDailyOrderItems'] >= BmdGlobalConstants::NUM_OF_DAILY_ORDER_ITEMS_LIMIT) {
            $entireProcessParams['resultCode'] = self::NUM_OF_DAILY_ORDER_ITEMS_LIMIT_REACHED['code'];
            throw new Exception(self::NUM_OF_DAILY_ORDER_ITEMS_LIMIT_REACHED['name']);
        }
    }


    
    public function getRates(Request $request)
    {
        $entireProcessData = [];
        $entireProcessParams = [
            'entireProcessComments' => [],
            'customErrors' => [],
            'resultCode' => self::DEFAULT_INITIAL_RESULT['code'],
            'reducedCartItemsData' => $request->reducedCartItemsData,
            'shippingInfo' => $request->shippingInfo
        ];


        try {

            $this->checkInventoryOrderLimits($entireProcessParams);

            // BMD-ON-STAGING: Finish all validation of request-params.
            if (!isset($request->reducedCartItemsData) || count($request->reducedCartItemsData) === 0) {
                $entireProcessParams['resultCode'] = self::EMPTY_CART_EXCEPTION['code'];
                throw new Exception(self::EMPTY_CART_EXCEPTION['name']);
            }


            if (!$this->validateDestinationAddress($entireProcessParams)) {
                $entireProcessParams['resultCode'] = self::INVALID_DESTINATION_COUNTRY_EXCEPTION['code'];
                throw new Exception(self::INVALID_DESTINATION_COUNTRY_EXCEPTION['name']);
            }


            \EasyPost\EasyPost::setApiKey(env('EASYPOST_TK'));

            $entireProcessData['originAddress'] = $this->setOriginAddress($entireProcessParams);
            $entireProcessData['destinationAddress'] = $this->setDestinationAddress($entireProcessParams);
            $entireProcessData['parcel'] = $this->setParcel($entireProcessParams);
            $entireProcessData['packageInfo'] = $entireProcessParams['packageInfo'];

            $shipmentObj = $this->setShipment($entireProcessData);


            // Check.
            if (!$this->doesShipmentHaveRates($shipmentObj)) {
                // Re-create the parcel & shipment.
                $usePredefinedPackageProp = false;
                $entireProcessData['parcel'] = $this->setParcel($entireProcessParams, $usePredefinedPackageProp);

                $shipmentObj = $this->setShipment($entireProcessData);
            }
            

            // 2nd check.
            if (!$this->doesShipmentHaveRates($shipmentObj)) {
                $params['resultCode'] = self::COULD_NOT_FIND_SHIPMENT_RATES['code'];
                throw new Exception(self::COULD_NOT_FIND_SHIPMENT_RATES['name']);
            }


            $entireProcessData['shipment'] = $shipmentObj;
            $entireProcessData['parsedRateObjs'] = $this->getParsedRateObjs($entireProcessData['shipment']->rates);
            $entireProcessData['modifiedRateObjs'] = $this->getModifiedRateObjs($entireProcessData['parsedRateObjs']);
            $entireProcessData['efficientShipmentRates'] = $this->getEfficientShipmentRates($entireProcessData['modifiedRateObjs']);
            $entireProcessData['shipmentId'] = $entireProcessData['shipment']->id;

            $entireProcessParams['resultCode'] = self::ENTIRE_PROCESS_OK['code'];
            $entireProcessParams['entireProcessComments'][] = self::ENTIRE_PROCESS_OK['name'];
        } catch (Exception $e) {
            if ($entireProcessParams['resultCode'] === self::DEFAULT_INITIAL_RESULT['code']) {
                $entireProcessParams['resultCode'] = self::CUSTOM_INTERNAL_EXCEPTION['code'];
            }
            $entireProcessParams['entireProcessComments'][] = "caught exception ==> " . $e->getMessage();
            $entireProcessParams['entireProcessComments'][] = "caught exception trace ==> " . $e->getTraceAsString();
        }


        $entireProcessData['entireProcessComments'] = $entireProcessParams['entireProcessComments'];
        $entireProcessData['customErrors'] = $entireProcessParams['customErrors'];
        $entireProcessData['resultCode'] = $entireProcessParams['resultCode'];


        // BMD-ON-STAGING: Don't include info that are unnecessary or sensitive.
        return [
            'objs' => $entireProcessData,
            'jsonifiedProcessData' => $this->extractToJsonifiedData($entireProcessData) // BMD-FOR-DEBUG
        ];
    }



    private function extractToJsonifiedData(&$entireProcessData)
    {
        return [
            'jsonOriginAddress' => isset($entireProcessData['originAddress']) ? $this->jsonifyObj($entireProcessData['originAddress']) : 'NOT SET',
            'jsonDestinationAddress' => isset($entireProcessData['destinationAddress']) ? $this->jsonifyObj($entireProcessData['destinationAddress']) : 'NOT SET',
            'packageInfo' => isset($entireProcessData['packageInfo']) ? $entireProcessData['packageInfo'] : 'NOT SET',
            'jsonParcel' => isset($entireProcessData['parcel']) ? $this->jsonifyObj($entireProcessData['parcel']) : 'NOT SET',
            'jsonShipment' => isset($entireProcessData['shipment']) ? $this->jsonifyObj($entireProcessData['shipment']) : 'NOT SET',
            'jsonParsedRateObjs' => isset($entireProcessData['parsedRateObjs']) ? $this->jsonifyObj($entireProcessData['parsedRateObjs']) : 'NOT SET'

        ];
    }
}
