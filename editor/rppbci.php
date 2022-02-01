<?php

/* Exibir erros */
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require 'functions.php';

if (file_exists('../inc/config.php')) {
    include '../inc/config.php';
}

if (file_exists('../elasticfind/elasticfind.php')) {
    include '../elasticfind/elasticfind.php';
}

if (isset($_REQUEST["delete_id"])) {
    $delete = Elasticsearch::delete($_REQUEST["delete_id"]);
    header("Location: ../index.php");
}

print("<pre>".print_r($_REQUEST, true)."</pre>");


$body["doc"]["type"] = $_REQUEST["type"];
$body["doc"]["name"] = $_REQUEST["name"];
$body["doc"]["alternateName"] = $_REQUEST["alternateName"];

$i = 0;
do {
    $key =  'author_'.$i.'_person_name';
    $key_person_identifier_value =  'author_'.$i.'_person_identifier_value';
    $key_organization_name =  'author_'.$i.'_organization_name';
    $key_organization_external =  'author_'.$i.'_organization_external';

    if (isset($_REQUEST[$key])) {
        $body["doc"]["author"][$i]["person"]["name"] = trim($_REQUEST[$key]);
        $body["doc"]["author"][$i]["person"]["identifier"]["value"] = trim($_REQUEST[$key_person_identifier_value]);        
        $body["doc"]["author"][$i]["organization"]["name"] = trim($_REQUEST[$key_organization_name]);
        
    }
    $i++;
} while ($i < 100);


$body["doc"]["publisher"]["organization"]["name"] = trim($_REQUEST["publisher_organization_name"]);
$body["doc"]["doi"] = trim($_REQUEST["doi"]);
$body["doc"]["url"] = trim($_REQUEST["url"]);
$body["doc"]["datePublished"] = trim($_REQUEST["datePublished"]);
$body["doc"]["isPartOf"]["name"] = trim($_REQUEST["isPartOf_name"]);
$body["doc"]["isPartOf"]["ISSN"] = trim($_REQUEST["isPartOf_ISSN"]);
$body["doc"]["isPartOf"]["volume"] = trim($_REQUEST["isPartOf_volume"]);
$body["doc"]["isPartOf"]["issue"] = trim($_REQUEST["isPartOf_issue"]);
$body["doc"]["isPartOf"]["pageStart"] = trim($_REQUEST["isPartOf_pageStart"]);
$body["doc"]["isPartOf"]["pageEnd"] = trim($_REQUEST["isPartOf_pageEnd"]);

$i_ISBN = 0;
do {
    $key =  'ISBN_'.$i_ISBN.'';
    if (isset($_REQUEST[$key])) {
        $body["doc"]["ISBN"][$i_ISBN] = trim($_REQUEST[$key]);        
    }
    $i_ISBN++;
} while ($i_ISBN < 5);

$i_about = 0;
do {
    $key =  'about_'.$i_about.'';
    if (isset($_REQUEST[$key])) {
        $body["doc"]["about"][$i_about] = trim($_REQUEST[$key]);        
    }
    $i_about++;
} while ($i_about < 30);

$body["doc"]["description"] = trim($_REQUEST["description"]);


$i_ref = 0;
do {
    $key_type =  'references_'.$i_ref.'_type';
    $key_name =  'references_'.$i_ref.'_name';
    $key_isPartOf_name =  'references_'.$i_ref.'_isPartOf_name';
    $key_datePublished =  'references_'.$i_ref.'_datePublished';
    $key_publisher =  'references_'.$i_ref.'_publisher';
    $key_doi =  'references_'.$i_ref.'_doi';
    $key_url =  'references_'.$i_ref.'_url';

    $i_aut = 0;
    do {
        $key_aut =  'references_'.$i_ref.'_authors_'.$i_aut.'';
        if (isset($_REQUEST[$key_aut])) {
            $body["doc"]["references"][$i_ref]["authors"][$i_aut] = trim($_REQUEST[$key_aut]);        
        }
        $i_aut++;
    } while ($i_aut < 30);

    if (isset($_REQUEST[$key_type])) {
        $body["doc"]["references"][$i_ref]["type"] = trim($_REQUEST[$key_type]);
        $body["doc"]["references"][$i_ref]["name"] = trim($_REQUEST[$key_name]);
        $body["doc"]["references"][$i_ref]["isPartOf_name"] = trim($_REQUEST[$key_isPartOf_name]);
        $body["doc"]["references"][$i_ref]["datePublished"] = trim($_REQUEST[$key_datePublished]);
        $body["doc"]["references"][$i_ref]["publisher"] = trim($_REQUEST[$key_publisher]);
        $body["doc"]["references"][$i_ref]["doi"] = trim($_REQUEST[$key_doi]);
        $body["doc"]["references"][$i_ref]["url"] = trim($_REQUEST[$key_url]);    
    }
    $i_ref++;
} while ($i_ref < 1000);



$body["doc_as_upsert"] = true;

print("<pre>".print_r($body, true)."</pre>");

$upsert = Elasticsearch::update($_REQUEST["rppbci_id"], $body);
print_r($upsert);

//header("Location: ../index.php");

?>