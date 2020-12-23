<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class CustomizedEasyPost extends Controller
{
    public function getRates()
    {
        $isResultOk = false;
        $jsFromAddres = [];
        $jsShipmentObj = [];
        $jsDestinationAddress = [];
        $customErrors = [];
        $entireProcessComments = [];
        $entireProcessResultCode = 0;
        $fromAddressErrors = [];
        $parsedRateObjs = [];
        $efficientShipmentRates = [];

        try {
            \EasyPost\EasyPost::setApiKey(env('EASYPOST_TK'));

            $fromAddressParams = [
                'verify' => [true],
                'street1' => '50 Thorncliffe Park Drive',
                // 'street2' => '5th Floor',
                'city' => 'East York',
                'state' => 'ON',
                'country' => 'CA',
                'zip' => 'M4H1K4',
                // 'phone' => '415-528-7555'

                // 'verify' => [true],
                // 'street1' => '417 Montgomery Street',
                // 'street2' => '5th Floor',
                // 'city' => 'San Francisco',
                // 'state' => 'CA',
                // 'country' => 'US'
                // 'zip' => '94104',
                // 'phone' => '415-528-7555'
            ];

            $fromAddress = \EasyPost\Address::create($fromAddressParams);


            foreach ($fromAddress as $k => $v) {
                $jsFromAddres[$k] = $v;
            }



            // Set origin-address.
            $isFromAddressValid = $fromAddress->verifications->delivery->success;

            if (!$isFromAddressValid) {

                $addressErrors = $fromAddress->verifications->delivery->errors;

                $fromAddressErrors = [];
                foreach ($addressErrors as $e) {

                    $ithErrorObj = [];
                    foreach ($e as $field => $val) {
                        $ithErrorObj[$field] = $val;
                    }

                    $fromAddressErrors[] = $ithErrorObj;
                }

                $customErrors['fromAddressErrors'] = $fromAddressErrors;

                $entireProcessComments[] = "ORIGIN_ADDRESS_EXCEPTION";
                $entireProcessResultCode = 1;
                throw new Exception("ORIGIN_ADDRESS_EXCEPTION");
            }



            // Set destination-address.
            $destinationsAddressParams = [
                'verify' => [true],
                // 'name' => 'George Costanza',
                // 'company' => 'Vandelay Industries',
                // 'street1' => '1 E 161st St.',
                // 'city' => 'Bronx',
                // 'state' => 'NY',
                // 'zip' => '10451'

                'street1' => '78 Monkhouse Rd',
                // 'street2' => '5th Floor',
                'city' => 'Markham',
                'state' => 'ON',
                'country' => 'CA',
                'zip' => 'L6E1V5',
            ];


            $destinationAddress = \EasyPost\Address::create($destinationsAddressParams);

            foreach ($destinationAddress as $k => $v) {
                $jsDestinationAddress[$k] = $v;
            }

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

                $customErrors['destinationAddressErrors'] = $destinationAddressErrors;

                $entireProcessComments[] = "DESTINATION_ADDRESS_EXCEPTION";
                $entireProcessResultCode = 3;
                throw new Exception("DESTINATION_ADDRESS_EXCEPTION");
            }



            // Create parcel.
            $parcel = \EasyPost\Parcel::create([
                "length" => 9,
                "width" => 6,
                "height" => 2,
                "weight" => 10
            ]);

            $entireProcessComments[] = "CREATED_PARCEL_OBJ";
            $entireProcessResultCode = 3;



            // Create shipment.
            $shipment = \EasyPost\Shipment::create([
                "to_address" => $fromAddress,
                "from_address" => $destinationAddress,
                "parcel" => $parcel
            ]);


            foreach ($shipment as $sField => $sVal) {
                $jsShipmentObj[$sField] = $sVal;
            }

            $entireProcessComments[] = "CREATED_SHIPMENT_OBJ";
            $entireProcessResultCode = 4;



            // Retrieve shipping-rates.
            foreach ($shipment->rates as $r) {

                $aParsedRateObj = [];
                foreach ($r as $rField => $rVal) {
                    $aParsedRateObj[$rField] = $rVal;
                }
                
                $parsedRateObjs[] = $aParsedRateObj;
            }

            $entireProcessComments[] = "RETRIEVED_SHIPPING_RATES";
            $entireProcessResultCode = 5;




            // Set the most efficient shipment-rates
            $cheapestWithFastestRate = null;
            $cheapestRate = 1000000.0;
            $fastestDeliveryDays = 365;
            foreach ($parsedRateObjs as $r) {
                if ((floatval($r['rate']) < $cheapestRate) ||
                    (floatval($r['rate']) == $cheapestRate && $r['delivery_days'] < $fastestDeliveryDays)) {
                    $cheapestRate = floatval($r['rate']);
                    $fastestDeliveryDays = $r['delivery_days'];
                    $cheapestWithFastestRate = $r;
                } 
            }


            $fastestWithCheapestRate = null;
            $cheapestRate = 1000000.0;
            $fastestDeliveryDays = 365;
            foreach ($parsedRateObjs as $r) {
                if (($r['delivery_days'] < $fastestDeliveryDays) ||
                    ($r['delivery_days'] == $fastestDeliveryDays && floatval($r['rate']) < $cheapestRate)) {
                    $cheapestRate = floatval($r['rate']);
                    $fastestDeliveryDays = $r['delivery_days'];
                    $fastestWithCheapestRate = $r;
                }
            }

            $efficientShipmentRates = [$cheapestWithFastestRate, $fastestWithCheapestRate];
            $entireProcessComments[] = "HAS_SET_EFFICEINT_SHIPPING_RATES";
            $entireProcessResultCode = 6;


            //
            $isResultOk = true;

        } catch (Exception $e) {
            $entireProcessComments[] = "caught exception ==> " . $e->getMessage();
        }


        return [
            'msg' => 'In CLASS: CustomizedEasyPost, METHOD: getRates()',
            'objs' => [
                'isResultOk' => $isResultOk,
                'jsFromAddres' => $jsFromAddres,
                'jsDestinationAddress' => $jsDestinationAddress,
                'jsShipmentObj' => $jsShipmentObj,
                'parsedRateObjs' => $parsedRateObjs,
                'efficientShipmentRates' => $efficientShipmentRates,
                'entireProcessComments' => $entireProcessComments,
                'entireProcessResultCode' => $entireProcessResultCode,
                'customErrors' => $customErrors
            ]
        ];



        // $toAddress = \EasyPost\Address::create(array(
        //     'name' => 'George Costanza',
        //     'company' => 'Vandelay Industries',
        //     'street1' => '1 E 161st St.',
        //     'city' => 'Bronx',
        //     'state' => 'NY',
        //     'zip' => '10451'
        // ));



        // $parcel = \EasyPost\Parcel::create(array(
        //     "length" => 9,
        //     "width" => 6,
        //     "height" => 2,
        //     "weight" => 10
        //   ));



        //   $shipment = \EasyPost\Shipment::create(array(
        //     "to_address" => $toAddress,
        //     "from_address" => $fromAddress,
        //     "parcel" => $parcel
        //   ));
    }
}
