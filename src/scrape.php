
<?php
ini_set('max_execution_time', 3000);
ini_set('memory_limit', '5000M');
include_once('simple_html_dom.php');


// -----------------------------------------------------------------------------

//load main page category

$location = "sri-lanka";
$url = "https://ikman.lk/en/ads/sri-lanka/property";
$html_source = file_get_contents($url);
//echo file_get_html('https://www.google.com/')->plaintext;
$html = str_get_html($html_source);


//$spans = $html->find("span.t-small", 0);
//
//var_dump($spans->innertext);
//exit;

preg_match("/of ([0-9,]+) ads/", $html->find("span.t-small", 0)->innertext, $total);


$pagelimit = intval((preg_replace("/,/", "", $total[1])) / 25) + 1;

$totalpage = $pagelimit ;

$response = array();

$datajson = array();

$items = [];
for($i=31; $i<$pagelimit; $i++ ) {

    $url = "https://ikman.lk/en/ads/sri-lanka/property?page=" . $i;



    echo $url . "\n";

    $html = str_get_html(file_get_contents($url));

    if ($html === false) continue;

    $ncheck = $html->find('span[class="pag-number is-ellipse"]', 0)->innertext;


    $ret['Title'] = $html->find('title', 0)->innertext;

    foreach ($html->find('div[class="item-thumb has-frames"]') as $div) {
        $data = [];
        //load inner link page
        preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $div, $result);


        $data['url'] = 'http://ikman.lk/' . $result['href'][0];

        printf("URL: %s\n", $data['url']);
        $htmlAd = str_get_html(file_get_contents($data['url']));
        if ($htmlAd === false) continue;
        $fva = $htmlAd->find('div[class="item-properties"]', 0)->innertext;
        $data['price'] = preg_replace("/,/", "", $htmlAd->find('span.amount', 0)->innertext);
        printf("Price %s\n", $data['price']);
        $dataHtml = str_get_html($fva);
        $dataHtmlSections = $dataHtml->find("dl");
        foreach ($dataHtmlSections as $dd) {
            $dx = clone $dd;
            $typeOfData = strtolower($dd->find("dt", 0)->innertext);
            $valueOfData = strtolower($dx->find("dd", 0)->innertext);
            printf("%s - %s\n", $typeOfData, $valueOfData);
            $data[] = [$typeOfData, $valueOfData];

        }
        $items[] = $data;
        sleep(5);
    }

    file_put_contents("scrape" . $i .  ".json", json_encode($items));
    $items = [];
}