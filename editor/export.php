<?php

require 'functions.php';

//print("<pre>".print_r($_REQUEST, true)."</pre>");

if ($_REQUEST["format"] == "alephseq") {

    $file="export.seq";
    header("Content-Disposition: attachment; filename=$file");

    $record_blob[] = Exporters::alephseq($_REQUEST);

    foreach ($record_blob as $record) {
        $record_array = explode('\n', $record);
        echo implode("\n", $record_array);
    }   

} else {
    echo "Não foi informado nenhum formato";
}


?>