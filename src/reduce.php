<?php
$files = glob("scrape*.json");
$reduced = [];
$min = null;
$max = null;
$averageArea = [];
foreach ($files as $file) {
    $json = file_get_contents($file);
    foreach (json_decode($json, true) as $property) {
        if (preg_match("/rent/", $property['url'])) {
            continue;
        }

        if (!preg_match("/house/", $property['url'])) {
            continue;
        }


        // echo $property['url'];
        $address = null;
        $land_size = null;
        $price = "";

        foreach ($property as $k => $attr) {
            if ($k === 'price') $price = $attr;
            $latitude = null;
            $longitude = null;
            if ($k >= 0 && $k < 10) {
                if (preg_match("/address/", $attr[0])) {
                    $address = $attr[1];
                    if (preg_match("/[0-9]+\/[0-9a-z]+/", $address)) {
                        $address = preg_replace("/[0-9]+\/[0-9a-z]/", "", $address);
                    }
                    $addrParts = explode(" ", $address);
//                    $address = $addrParts[count($addrParts) - 1];

                }
                if (in_array($address, ['kundasala', 'road)'])) continue;

                if (preg_match("/land size/", $attr[0])) {
                    $land_size = $attr[1];
                    $land_size = preg_replace("/[a-zA-Z]+/", "", $land_size);
                }
                }
            }

            if($address === null) continue;
            echo $address;
            $locality = null;
            $haveSub = false;
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=AIzaSyC1K8YFMvB0cKMHzFnY29Ea6hEuuM8Zq4o";
            $json = file_get_contents($url);
            $geoCode = json_decode($json, true);
            //printf("status %s", print_r($geoCode, true));
            if ($geoCode['status'] == "OK") {
                foreach ($geoCode['results'][0]['address_components'] as $component) {
                    if (in_array('country', $component['types'])) {
                        if ($component['short_name'] === 'LK') {
                            $latitude = $geoCode['results'][0]['geometry']['location']['lat'];
                            $longitude = $geoCode['results'][0]['geometry']['location']['lng'];
                        }
                    }
                    if (in_array('sublocality_level_1', $component['types'])) {
                        $address = $component['short_name'];
                        $haveSub = true;
                    }

                    if (in_array('locality', $component['types'])) {
                        $locality = $component['short_name'];
                    }
                }

                if (!$haveSub) {
                    $address = $locality;
                }

                if ($land_size != null && $address != null) {
                    $reduced[$address]['price_per_pirch'][] = (intval($price) / intval($land_size));
                    $reduced[$address]['lat'] = $latitude;
                    $reduced[$address]['lng'] = $longitude;
                }

                sleep(2);
            } else {
                continue;
            }
            //mapping

            echo "number of elements " . count($reduced) .  "\n";
            if (count($reduced) > 3) {
           //    break;
            }
        }

    foreach ($reduced as $area => $reducedElement) {
        $averagePrice = array_sum($reducedElement['price_per_pirch']) / count($reducedElement['price_per_pirch']);
        if ($min > $averagePrice || $min === null) $min = $averagePrice;
        if ($max < $averagePrice || $max === null) $max = $averagePrice;
        if (isset($averageArea[$area]['price_per_pirch'])) {
            $averageArea[$area]['price_per_pirch'] = ($averageArea[$area]['price_per_pirch'] + $averagePrice) / 2;
        } else {
            $averageArea[$area] = ['lng' => $reducedElement['lng'], 'lat' => $reducedElement['lat']];
            $averageArea[$area]['price_per_pirch'] = $averagePrice;
        }

    }

    printf("Number of elements %d\n", count($averageArea));
    if (count($averageArea) > 10) {
      //  break;
    }
}

printf("Min price %s, Max price %s\n", $min, number_format($max));

foreach ($averageArea as $city => $price) {
    $weight = ($price['price_per_pirch'] / $max * 9) + 1;
    if ($price['price_per_pirch'] > "9000000") continue;

        printf("[%s, %s, %d, \"%s\", \"%s\"],\n", $price['lat'], $price['lng'],
            $weight, $city, number_format($price['price_per_pirch']));
}