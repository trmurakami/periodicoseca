<?php

// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/functions.php';

/* Exibir erros */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(1);


$query["query"]["query_string"]["query"] = '_exists_:doi doi:1* -_exists_:openalex';

$params = [];
$params["index"] = $index;
$params["size"] = $_GET["size"];
$params["body"] = $query;

$cursor = $client->search($params);

foreach ($cursor["hits"]["hits"] as $r) {
    //print("<pre>".print_r($r, true)."</pre>");
    //print("<pre>".print_r($r["_source"]["doi"], true)."</pre>");    
    $openalex_result = openalexAPI($r["_source"]["doi"]);
    unset($openalex_result['abstract_inverted_index']);
    //print("<pre>".print_r($openalex_result, true)."</pre>");
    $body["doc"]["openalex"] = $openalex_result;
    $body["doc_as_upsert"] = true;
    $upsert_openalex = Elasticsearch::update($r["_id"], $body);
    print("<pre>".print_r($upsert_openalex, true)."</pre>");
    //sleep(11);
    ob_flush();
    flush();
}
//header("Refresh: 0");

?>