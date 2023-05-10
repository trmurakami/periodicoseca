<?php

// Set directory to ROOT
chdir('../');
// Include essencial files
require 'inc/functions.php';

/* Exibir erros */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(1);


$query["query"]["query_string"]["query"] = '_exists_:doi doi:1* -_exists_:opencitations';

$params = [];
$params["index"] = $index;
$params["body"] = $query;

$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];

$params["size"] = $_GET["size"];

$cursor = $client->search($params);

echo "Resultado: $total";

foreach ($cursor["hits"]["hits"] as $r) {
    $opencitations_result = opencitationsAPIcitationcount($r["_source"]["doi"]);
    //print("<pre>".print_r($opencitations_result, true)."</pre>");
    $body["doc"]["opencitations"]['citation_count'] = $opencitations_result[0]['count'];
    $body["doc_as_upsert"] = true;
    print("<pre>".print_r($body, true)."</pre>");
    $upsert_opencitations = Elasticsearch::update($r["_id"], $body);
    print("<pre>" . print_r($upsert_opencitations, true) . "</pre>");
    ob_flush();
    flush();
}