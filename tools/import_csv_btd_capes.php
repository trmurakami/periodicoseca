<?php

require '../inc/config.php';
require '../inc/functions.php';

function recursive_implode(array $array, $glue = ',', $include_keys = false, $trim_all = true)
{
	$glued_string = '';

	// Recursively iterates array and adds key/value to glued string
	array_walk_recursive($array, function($value, $key) use ($glue, $include_keys, &$glued_string)
	{
		$include_keys and $glued_string .= $key.$glue;
		$glued_string .= $value.$glue;
	});

	// Removes last $glue from string
	strlen($glue) > 0 and $glued_string = substr($glued_string, 0, -strlen($glue));

	// Trim ALL whitespace
	$trim_all and $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);

	return (string) $glued_string;
}

if (isset($_FILES['file'])) {

    if (isset($_REQUEST["area"])) {
        $area = $_REQUEST["area"];
    } else {
        $area = "";
    }

    $fh = fopen($_FILES['file']['tmp_name'], 'r+');
    $row = fgetcsv($fh, 108192, "\t");

    foreach ($row as $key => $value) {
        $array_values[] = $value;
    }
    define("HEADER", $array_values);

    while (($row = fgetcsv($fh, 1888192, "\t")) !== false) {
        $doc = CSVThesisRecord::build($row, HEADER, $area);
        $sha_string = recursive_implode($doc);
        $sha256 = hash('sha256', $sha_string);
        //print_r($sha256);
        //echo '<br/>';
        //print_r($doc);
        $resultado_elastic = Elasticsearch::update($sha256, $doc);
        //print_r($resultado_elastic);
    }
}

//sleep(5);
//echo '<script>window.location = \'result.php?filter[]=type:"Work"&filter[]=tag:"'.$_POST["tag"].'"\'</script>';

class CSVThesisRecord
{
    public static function build($row, $header, $area)
    {
        foreach ($row as $key => $value) {

            $doc["doc"]["origin"] = "BTD CAPES";

            if ($header[$key] == "AN_BASE") {
                $doc["doc"]["datePublished"] = $value;
            }

            if ($header[$key] == "NM_PROGRAMA") {
                $doc["doc"]["NM_PROGRAMA"] = $value;
            }

            if ($header[$key] == "NM_PRODUCAO") {
                $doc["doc"]["name"] = $value;
            }

            if ($header[$key] == "NM_SUBTIPO_PRODUCAO") {
                $doc["doc"]["type"] = $value;
                //$doc["doc"]["source"] = $value;
            }

            if ($header[$key] == "DS_RESUMO") {
                $doc["doc"]["description"] = $value;
            }

            if ($header[$key] == "DS_URL_TEXTO_COMPLETO") {
                $doc["doc"]["url"] = $value;
            }

            if ($header[$key] == "NM_IDIOMA") {
                $doc["doc"]["inLanguage"] = $value;
            }

            if ($header[$key] == "NM_DISCENTE") {
                $doc["doc"]["author"][0]["person"]["name"] = $value;
            }

            if ($header[$key] == "NM_ENTIDADE_ENSINO") {
                $doc["doc"]["author"][0]["organization"]["name"] = $value;
            }

            if ($header[$key] == "DS_PALAVRA_CHAVE") {
                $value_array = explode(";", $value);
                foreach($value_array as $palavra_chave) {
                    $doc["doc"]["about"][] = $palavra_chave;
                }
            }
        }
        $doc["doc"]["area"] = $area;
        $doc["doc_as_upsert"] = true;
        return $doc;
    }
}