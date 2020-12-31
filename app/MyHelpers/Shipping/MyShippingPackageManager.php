<?php

namespace App\MyHelpers\Shipping;

use App\PackageItemType;

class MyShippingPackageManager
{
    /**
     * weightLimit is in oz
     */
    public static $predefinePackagesByCarrier = [
        'UPS' => [
            'UPSLetter' => [
                'weightLimit' => 16.00,
                'itemTypeLimits' => ['shirt' => 3.0, 'jersey' => 2.0, 'shorts' => 2.0]
            ],
            'SmallExpressBox' => [
                'weightLimit' => 480.00,
                'itemTypeLimits' => ['shirt' => 10.0, 'jersey' => 10.0, 'shorts' => 6.0, 'hoodie' => 2.0]
            ],
            'UPS10kgBox' => [
                'weightLimit' => 352.00,
                'itemTypeLimits' => ['shirt' => 35.0, 'jersey' => 30.0, 'shorts' => 20.0, 'hoodie' => 8.0, 'shoes' => 4.0]
            ],
            'UPS25kgBox' => [
                'weightLimit' => 880.00,
                'itemTypeLimits' => ['shirt' => 50.0, 'jersey' => 42.0, 'shorts' => 30.0, 'hoodie' => 12.0, 'shoes' => 6.0, 'pctowercase' => 1.0]
            ]
        ],
        'FedEx' => [],
        'DHL' => [],
        'CanadaPost' => []
    ];

    public function test()
    {
        $testCartItems = [
            ['id' => 1, 'quantity' => 2, 'product' => ['name' => 'Durant Jersey', 'itemTypeId' => 2]],
            // ['id' => 2, 'quantity' => 1, 'product' => ['name' => 'Lakers Hoodie', 'itemTypeId' => 4]],
            // ['id' => 3, 'quantity' => 3, 'product' => ['name' => 'Kyrie Jersey', 'itemTypeId' => 2]],
            // ['id' => 4, 'quantity' => 8, 'product' => ['name' => 'Kyrie T-Shirt', 'itemTypeId' => 1]],
        ];
        $convertedTotlaQty = self::getPredefinedPackageName($testCartItems);
        return $convertedTotlaQty;
    }

    public static function getPredefinedPackageName($items)
    {

        $itemTypes = PackageItemType::orderByDesc('encompassing_level')->get();

        // The itemType of the order-item with the Highest Encompassing Level.
        $refItemType = null;

        $hasFoundRef = false;
        foreach ($itemTypes as $t) {

            foreach ($items as $i) {
                $i = json_decode($i);
                if ($t->id === $i->packageItemTypeId) {
                    $refItemType = $t;
                    $hasFoundRef = true;
                    break;
                }
            }

            if ($hasFoundRef) {
                break;
            }
        }



        // Figure out the total quantity of all the order-items in an imaginary unit based on the $refItemType.
        /**
         * ie) Convert 3 shirt to x hoodie quantity. The refItemType is hoodie.
         *      1) currentItemTotalConvertedQty = (ref-conversion-ratio) / (current-item-conversion-ratio) * (current-item-qty)
         *      2) x hoode = (12.00 hoodie) / (50.00 shirt) * (3 shirt)
         *      3) 0.72 hoodie
         *      4) 3 shirt = 0.72 hoodie
         */
        $allItemsTotalConvertedQty = 0.00;

        foreach ($items as $i) {

            $i = json_decode($i);
            $currentItemTotalConvertedQty = 0.00;
            $refConversionRatio = $refItemType->conversion_ratio;
            $currentItemType = PackageItemType::find($i->packageItemTypeId);
            $currentItemConversionRatio = $currentItemType->conversion_ratio;
            $currentItemQty = $i->quantity;

            $currentItemTotalConvertedQty = $refConversionRatio / $currentItemConversionRatio * $currentItemQty;
            $allItemsTotalConvertedQty += $currentItemTotalConvertedQty;
        }



        // Figure-out the cheapest predefined-package that can hold that amount of total-converted-qty.
        $selectedPredefinedPackageName = null;
        $UpsPredefinePackages = self::$predefinePackagesByCarrier['UPS'];

        foreach ($UpsPredefinePackages as $ppName => $ppDetails) {

            $ppItemTypeLimits = $ppDetails['itemTypeLimits'];

            if (array_key_exists($refItemType->name, $ppItemTypeLimits)) {

                $ppMaxCapacityForItemType = $ppItemTypeLimits[$refItemType->name];
                if (
                    isset($ppMaxCapacityForItemType) &&
                    $ppMaxCapacityForItemType >= $allItemsTotalConvertedQty
                ) {

                    $selectedPredefinedPackageName = $ppName;
                    break;
                }
            }
        }




        //
        // return $allItemsTotalConvertedQty;
        // return [
        //     'convertedQty' => $allItemsTotalConvertedQty,
        //     'selectedPredefinedPackageName' => $selectedPredefinedPackageName
        // ];
        return $selectedPredefinedPackageName;
    }
}
