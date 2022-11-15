#!/usr/bin/php
<?php

require '../inc/config.php';
require 'functions_import.php';

$record = array();
$sysno_old = '000000000';

$i = 0;
while ($line = fgets(STDIN)) {
    $sysno = substr($line, 0, 9);
    if ($sysno_old == '000000000') {
        $sysno_old = $sysno;
    }
    if ($sysno_old == $sysno) {
        $record[] = $line;
    } else {

        foreach ($record as $linha_de_registro) {
            processaAlephseq($linha_de_registro);
        }

        /* Processa os fixes */


        switch ($marc["record"]["BAS"]["a"][0]) {
            case "Catalogação Rápida":
                echo "Não indexar";
                break;
            case "Assinatura Combinada":
                echo "Não indexar";
                break;
            case 01:
                if ($marc["record"]["945"]["b"][0] == "PARTITURA") {

                    $body = fixes($marc);
                    if (isset($marc["record"]["260"])) {
                        if (isset($marc["record"]["260"]["c"])) {
                            $excluir_caracteres = array("[", "]", "c");
                            $only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
                            $body["doc"]["datePublished"] = $only_numbers;
                        } else {
                            $body["doc"]["datePublished"] = "N/D";
                        }
                    }
                    $body["doc"]["base"][] = "Partituras";
                    $response = Elasticsearch::update($id, $body);
                    //print_r($id);				

                } elseif ($marc["record"]["945"]["b"][0] == "TRABALHO DE CONCLUSAO DE CURSO - TCC") {

                    $body = fixes($marc);
                    $body["doc"]["base"][] = "Trabalhos acadêmicos";
                    $body["doc"]["sysno"] = $id;
                    if (isset($marc["record"]["260"])) {
                        if (isset($marc["record"]["260"]["c"])) {
                            $excluir_caracteres = array("[", "]", "c");
                            $only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
                            $body["doc"]["datePublished"] = $only_numbers;
                        } else {
                            $body["doc"]["datePublished"] = "N/D";
                        }
                    }
                    $response = Elasticsearch::update($id, $body);
                    //print_r($id);

                } elseif ($marc["record"]["945"]["b"][0] == "TRABALHO DE ESPECIALIZACAO - TCE") {

                    $body = fixes($marc);
                    $body["doc"]["base"][] = "Trabalhos acadêmicos";
                    $body["doc"]["sysno"] = $id;
                    if (isset($marc["record"]["260"])) {
                        if (isset($marc["record"]["260"]["c"])) {
                            $excluir_caracteres = array("[", "]", "c");
                            $only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
                            $body["doc"]["datePublished"] = $only_numbers;
                        } else {
                            $body["doc"]["datePublished"] = "N/D";
                        }
                    }
                    $response = Elasticsearch::update($id, $type, $body, "bdta_homologacao");
                    //print_r($id);

                } elseif ($marc["record"]["945"]["b"][0] == "E-BOOK") {

                    $body = fixes($marc);

                    if (isset($marc["record"]["260"])) {
                        if (isset($marc["record"]["260"]["c"])) {
                            $excluir_caracteres = array("[", "]", "c");
                            $only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
                            $body["doc"]["datePublished"] = $only_numbers;
                        } else {
                            $body["doc"]["datePublished"] = "N/D";
                        }
                    }
                    $body["doc"]["base"][] = "E-Books";
                    $response = Elasticsearch::update($id, $body);
                } else {

                    $body = fixes($marc);
                    if (isset($marc["record"]["260"])) {
                        if (isset($marc["record"]["260"]["c"])) {
                            $excluir_caracteres = array("[", "]", "c");
                            $only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
                            $body["doc"]["datePublished"] = $only_numbers;
                        } else {
                            $body["doc"]["datePublished"] = "N/D";
                        }
                    }
                    $body["doc"]["base"][] = "Livros";
                    //print_r($body);
                    $response = Elasticsearch::update($id, $body);
                    //print_r($id);

                }
                break;
            case 02:
                echo "Não indexar";
                break;
            case 03:
                $update["update"]["_index"] = $index;
                $update["update"]["_id"] = $id;
                $body = fixes($marc);
                $body["doc"]["base"][] = "Teses e dissertações";
                $body["doc"]["sysno"] = $id;

                $params['body'][] = $update;
                $params['body'][] = $body;

                if ($i % 250 == 0) {

                    $responses = $client->bulk($params);
                    //print_r($responses);

                    // erase the old bulk request
                    $params = ['body' => []];

                    // unset the bulk response when you are done to save memory
                    unset($responses);
                }
                break;

            case 04:
                $update["update"]["_index"] = $index;
                $update["update"]["_id"] = $id;
                $body = fixes($marc);
                $body["doc"]["base"][] = "Produção científica";
                $body["doc"]["sysno"] = $id;

                $params['body'][] = $update;
                $params['body'][] = $body;

                if ($i % 250 == 0) {

                    $responses = $client->bulk($params);
                    //print_r($responses);

                    // erase the old bulk request
                    $params = ['body' => []];

                    // unset the bulk response when you are done to save memory
                    unset($responses);
                }

                //$response = elasticsearch::elastic_update($id, $type, $body);
                break;
            case 06:
                $body = fixes($marc);
                $body["doc"]["base"][] = "Trabalhos acadêmicos";
                $body["doc"]["sysno"] = $id;
                $response = Elasticsearch::update($id, $type, $body, "bdta");
                break;
            default:
                break;
        }


        echo "$sysno \n";

        $marc = [];
        $record = [];
    }

    $sysno_old = $sysno;
    $i++;
}

// Send the last batch if it exists
if (!empty($params['body'])) {
    $responses = $client->bulk($params);
    //print_r($responses);
}