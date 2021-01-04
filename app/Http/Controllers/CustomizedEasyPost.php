<?php

namespace App\Http\Controllers;

use App\MyHelpers\Shipping\MyShippingPackageManager;
use Exception;
use Illuminate\Http\Request;
use App\ShippingServiceLevel;

class CustomizedEasyPost extends Controller
{
    private const CUSTOM_INTERNAL_EXCEPTION = ['code' => -500, 'name' => 'CUSTOM_INTERNAL_EXCEPTION'];
    private const ORIGIN_ADDRESS_EXCEPTION = ['code' => -1, 'name' => 'ORIGIN_ADDRESS_EXCEPTION'];
    private const DESTINATION_ADDRESS_EXCEPTION = ['code' => -2, 'name' => 'DESTINATION_ADDRESS_EXCEPTION'];
    private const NULL_PREDEFINED_PACKAGE_EXCEPTION = ['code' => -3, 'name' => 'NULL_PREDEFINED_PACKAGE_EXCEPTION'];
    private const EMPTY_CART_EXCEPTION = ['code' => -4, 'name' => 'EMPTY_CART_EXCEPTION'];
    private const EMPTY_REQUEST_PARAMS = ['code' => -400, 'name' => 'EMPTY_REQUEST_PARAMS'];

    private const ENTIRE_PROCESS_OK = ['code' => 1, 'name' => 'ENTIRE_PROCESS_OK'];

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



    // TODO:DELETE-ON-PRODUCTION
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
            // 'company' => self::COMPANY_INFO['company'], // TODO:LATER-ON-PRODUCTION
            // 'email' => self::COMPANY_INFO['email'], // TODO:LATER-ON-PRODUCTION
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
            // $params['resultCode'] = self::ORIGIN_ADDRESS_EXCEPTION['code'];


            throw new Exception(self::ORIGIN_ADDRESS_EXCEPTION['name']);
        }


        return $originAddress;
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



    public function setParcel(&$params)
    {
        $packageInfo = MyShippingPackageManager::getPackageInfo($params['reducedCartItemsData']);

        if (!isset($packageInfo)) {
            // $params['resultCode'] = self::NULL_PREDEFINED_PACKAGE_EXCEPTION['code'];
            throw new Exception(self::NULL_PREDEFINED_PACKAGE_EXCEPTION['name']);
        }

        $parcel = \EasyPost\Parcel::create([
            "predefined_package" => $packageInfo,
            "weight" => $packageInfo['totalWeight']
        ]);

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



    public function getModifiedRateObjs($parsedRateObjs)
    {
        // For each rate, add value to field "delivery_days" if the retrieved rate has null.
        $modifiedRateObjs = [];
        $shippingServiceLevels = ShippingServiceLevel::all();

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



    public function getRates(Request $request)
    {
        $entireProcessData = [];
        $entireProcessParams = ['entireProcessComments' => [], 'customErrors' => [], 'resultCode' => 0, 'reducedCartItemsData' => $request->reducedCartItemsData, 'shippingInfo' => $request->shippingInfo];

        \EasyPost\EasyPost::setApiKey(env('EASYPOST_TK'));

        try {

            // TODO:LATER-ON-PRODUCTION Finish all validation of request-params.
            if (!isset($request->reducedCartItemsData) || count($request->reducedCartItemsData) === 0) {
                $entireProcessParams['resultCode'] = self::EMPTY_CART_EXCEPTION['code'];
                throw new Exception(self::EMPTY_CART_EXCEPTION['name']);
            }

            $entireProcessData['originAddress'] = $this->setOriginAddress($entireProcessParams);
            $entireProcessData['destinationAddress'] = $this->setDestinationAddress($entireProcessParams);
            $entireProcessData['parcel'] = $this->setParcel($entireProcessParams);
            $entireProcessData['shipment'] = $this->setShipment($entireProcessData);
            $entireProcessData['parsedRateObjs'] = $this->getParsedRateObjs($entireProcessData['shipment']->rates);
            $entireProcessData['modifiedRateObjs'] = $this->getModifiedRateObjs($entireProcessData['parsedRateObjs']);
            $entireProcessData['efficientShipmentRates'] = $this->getEfficientShipmentRates($entireProcessData['modifiedRateObjs']);
            $entireProcessData['isResultOk'] = true;
            $entireProcessParams['resultCode'] = self::ENTIRE_PROCESS_OK['code'];
            $entireProcessParams['entireProcessComments'][] = self::ENTIRE_PROCESS_OK['name'];

            $entireProcessData['shipmentId'] = $entireProcessData['shipment']->id;
            

            // TODO:DELETE-ON-PRODUCTION
            $entireProcessData['jsonOriginAddress'] = $this->jsonifyObj($entireProcessData['originAddress']);
            $entireProcessData['jsonDestinationAddress'] = $this->jsonifyObj($entireProcessData['destinationAddress']);
            $entireProcessData['jsonParcel'] = $this->jsonifyObj($entireProcessData['parcel']);
        } catch (Exception $e) {
            if ($entireProcessParams['resultCode'] === 0) {
                $entireProcessParams['resultCode'] = self::CUSTOM_INTERNAL_EXCEPTION['code'];
            }
            $entireProcessParams['entireProcessComments'][] = "caught exception ==> " . $e->getMessage();
        }


        $entireProcessData['entireProcessComments'] = $entireProcessParams['entireProcessComments'];
        $entireProcessData['customErrors'] = $entireProcessParams['customErrors'];
        $entireProcessData['resultCode'] = $entireProcessParams['resultCode'];

        return [
            'msg' => 'In CLASS: CustomizedEasyPost, METHOD: getRates()',
            'objs' => $entireProcessData
        ];
    }
}
