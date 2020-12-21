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
        $parsedRates = [];
        $jsShipmentObj = [];
        $jsDestinationAddress = [];
        $customErrors = [];
        $entireProcessComments = [];
        $entireProcessResultCode = 0;
        $fromAddressErrors = [];
        $parsedRateObjs = [];

        try {
            \EasyPost\EasyPost::setApiKey(env('EASYPOST_PK'));

            $fromAddressParams = [
                'verify' => [true],
                'street1' => '417 Montgomery Street',
                'street2' => '5th Floor',
                'city' => 'San Francisco',
                'state' => 'CA',
                'zip' => '94104',
                'phone' => '415-528-7555'
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
                'name' => 'George Costanza',
                'company' => 'Vandelay Industries',
                'street1' => '1 E 161st St.',
                'city' => 'Bronx',
                'state' => 'NY',
                'zip' => '10451'
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

            //ish
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
                
                // $parsedRates[] = $r->carrier;
                // $parsedRates[] = $r->service;
                // $parsedRates[] = $r->rate;
                // break;
            }

            $entireProcessComments[] = "RETRIEVED_SHIPPING_RATES";
            $entireProcessResultCode = 5;


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
                'parsedRates' => $parsedRates,
                'parsedRateObjs' => $parsedRateObjs,
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
