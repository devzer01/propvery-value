<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get("/data", function (Request $request, Response $response, array $args) {
//   $json = file_get_contents("http://pituwa:pituwa@localhost:5984/market/_design/property/_view/pppcolombo");
//   $j = json_decode($json, true);
//   $x = array_map(function($val) {
//        return  ['area' => $val['value'][0], 'ppp' => $val['value'][1]];
//   }, $j['rows']);
//
//   $x = array_slice($x, 0, 10);

   return $response->withJson([]);

});

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});