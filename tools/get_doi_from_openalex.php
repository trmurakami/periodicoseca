<?php

// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/functions.php';

/* Exibir erros */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(1);


$query["query"]["query_string"]["query"] = '-_exists_:doi -_exists_:openalex';

$params = [];
$params["index"] = $index;
$params["body"] = $query;

$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];



$params['body']['fields'][] = 'name';
$params['body']['_source'] = false;
$params["size"] = $_GET["size"];


$cursor = $client->search($params);

echo "Resultado: $total";

foreach ($cursor["hits"]["hits"] as $r) {
    //var_dump($r['fields']['name'][0]);
    //$openalex_result = file_get_contents('https://api.openalex.org/autocomplete/works?q='.$r['fields']['name'][0].'');
    $openalex_result = openalexGetDOI($r['fields']['name'][0]);
    //var_dump($openalex_result);
    if ($openalex_result['meta']['count'] === 1) {
        $body["doc"]["openalex"] = $openalex_result;
    } else {        
        $body["doc"]["openalex"]['empty'] = true;
        $upsert_openalex = Elasticsearch::update($r["_id"], $body);
    }
     $body["doc_as_upsert"] = true;
     var_dump($body);
    $upsert_openalex = Elasticsearch::update($r["_id"], $body);
    ob_flush();
    flush();
}