<?php

$file = "export_field.tsv";
header('Content-type: text/tab-separated-values; charset=utf-8');
header("Content-Disposition: attachment; filename=$file");
// Set directory to ROOT
chdir('../');
// Include essencial files
include 'inc/config.php';
include 'inc/config.php';

if (!empty($_GET["field"])) {
    $query["query"]["bool"]["must"]["query_string"]["query"] = "*";
    $params = [];
    $params["index"] = $index;
    $params["size"] = 2;
    $params["scroll"] = "30s";
    $params["_source"] = ["_id", $_GET['field']];
    $params["body"] = $query;
    $cursor = $client->search($params);

    $total = $cursor["hits"]["total"];

    while (isset($cursor['hits']['hits']) && count($cursor['hits']['hits']) > 0) {
        $scroll_id = $cursor['_scroll_id'];
        $cursor = $client->scroll(
            [
                "scroll_id" => $scroll_id,
                "scroll" => "30s"
            ]
        );
        foreach ($cursor["hits"]["hits"] as $r) {
            $fieldRow = [];
            $fieldArray = explode(".", $_GET["field"]);
            $count = count($fieldArray);
            //echo "<pre>" . print_r($r, true) . "</pre>";
            foreach ($r['_source'][$_GET["field"]] as $field) {
                $fieldRow[] = trim($field);
            }
            $content[] = implode("|", $fieldRow);
            unset($fieldRow);
        }
    }
    echo implode("\n", $content);
}
