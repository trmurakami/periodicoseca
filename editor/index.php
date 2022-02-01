<?php 

/* Load libraries for PHP composer */ 
require (__DIR__.'/vendor/autoload.php'); 

require 'functions.php';

if (file_exists('../inc/config.php')) {
    include '../inc/config.php';
    $_REQUEST["formType"] = "rppbci";
}

/* Exibir erros */
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

if (isset($_REQUEST["record"])) {
    if (isset($_REQUEST["rppbci_id"])) {
        $_REQUEST["formType"] = "rppbci";
        $record["rppbci_id"] = $_REQUEST["rppbci_id"];
    } elseif (isset($_REQUEST["coletaprod_id"])) {
        $_REQUEST["formType"] = "produsp";
        $record["coletaprod_id"] = $_REQUEST["coletaprod_id"];
    }    
    $record = json_decode(urldecode($_REQUEST["record"]), true);
    if (strpos($record["name"], ":")) {
        $titleExploded = explode(":", $record["name"]);
        $record["name"] = trim($titleExploded[0]);
        $record["subtitle"] = trim($titleExploded[1]);
    }
    $recordJson = json_encode($record);
    //print("<pre>".print_r($record, true)."</pre>");    
} else {
    $record["rppbci_id"] = hash('sha256', (random_bytes(16)));
}

if (!isset($_REQUEST["formType"])) {
    $_REQUEST["formType"] = "produsp";
}

if ($_REQUEST["crossrefDoi"]) {
    $clientCrossref = new RenanBr\CrossRefClient();
    $clientCrossref->setUserAgent('GroovyBib/1.1 (http://tecbib.com/metadataEditor/; mailto:trmurakami@gmail.com)');
    $exists = $clientCrossref->exists('works/'.$_REQUEST["crossrefDoi"].'');
    if ($exists == true) {
        $work = $clientCrossref->request('works/'.$_REQUEST["crossrefDoi"].'');
        print("<pre>".print_r($work, true)."</pre>");
        echo '
        <script type="text/javascript">
        var crossrefData = '.json_encode($work) .';
        </script>';
        $record["name"] = $work["message"]["title"][0];
        $record["subtitle"] = $work["message"]["subtitle"][0];
        $record["ignoreCharacters"] = Lookup::article($work["message"]["title"][0]);
        $record["doi"] = $work["message"]["DOI"];
        $i_author = 0;
        foreach ($work["message"]["author"] as $crossrefAuthor) {
            $record["author"][$i_author]["person"]["name"] = ''.$crossrefAuthor["family"].', '.$crossrefAuthor["given"].'';
            if (isset($crossrefAuthor["affiliation"])) {
                $record["author"][$i_author]["organization"]["name"] = $crossrefAuthor["affiliation"][0];
            }
            $i_author++;
        }
        $record["datePublished"] = (string)$work["message"]["issued"]["date-parts"][0][0];
        $record["publisher"]["organization"]["name"] = $work["message"]["publisher"];
        $record["isPartOf"]["name"] = $work["message"]["container-title"];
        $record["isPartOf"]["ISSN"] = implode(";", $work["message"]["ISSN"]);
        $record["isPartOf"]["volume"] = $work["message"]["volume"];
        $record["isPartOf"]["issue"] = $work["message"]["journal-issue"]["issue"];
        $record["isPartOf"]["pageStart"] = $work["message"]["page"];
        $record["url"] = $work["message"]["url"];
        $record["about"] = $work["message"]["subject"];

        $i_funder = 0;
        if (isset($work["message"]["funder"])) {
            foreach ($work["message"]["funder"] as $crossrefFunder) {
                $record["funder"][$i_funder]["organization"]["name"] = $crossrefFunder["name"];
                $record["funder"][$i_funder]["organization"]["projectNumber"] = $crossrefFunder["award"][0];
                $i_funder++;

            }
        }        
        $recordJson = json_encode($record);

    } else {
        $crossrefMessage = '<br/><br/><div class="alert alert-warning" role="alert">DOI não encontrado na Crossref</div>';
        $record["name"] = "";
        $record["subtitle"] = "";
        $record["ignoreCharacters"] = 0;

        
    }
}

if (!isset($_REQUEST["crossrefDoi"]) && !isset($_REQUEST["record"])) {
    $record["name"] = "";
    $record["subtitle"] = "";
    $record["ignoreCharacters"] = 0;
    $recordJson = json_encode($record);
}


?>

<!doctype html>
<html lang="pt_BR">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- dependencies (jquery, handlebars and bootstrap) -->
        <script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js"></script>
        <link type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet"/>
        <script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
        
        <!-- alpaca -->
        <link type="text/css" href="//cdn.jsdelivr.net/npm/alpaca@1.5.27/dist/alpaca/bootstrap/alpaca.min.css" rel="stylesheet"/>
        <script type="text/javascript" src="//cdn.jsdelivr.net/npm/alpaca@1.5.27/dist/alpaca/bootstrap/alpaca.min.js"></script>



        <!-- jQuery UI Support -->
        <script type="text/javascript" src="http://www.alpacajs.org/lib/jquery-ui/jquery-ui.js"></script>
        <link type="text/css" href="http://www.alpacajs.org/lib/jquery-ui/themes/cupertino/jquery-ui.min.css" rel="stylesheet"/>

        <!-- Required for jQuery UI DateTimePicker control -->
        <script type="text/javascript" src="http://www.alpacajs.org/lib/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.js"></script>
        <link type="text/css" href="http://www.alpacajs.org/lib/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.css" rel="stylesheet"/>        

        <!-- bootstrap datetimepicker for date, time and datetime controls -->
        <script src="http://www.alpacajs.org/lib/moment/min/moment-with-locales.min.js"></script>
        <script src="http://www.alpacajs.org/lib/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
        <link rel="stylesheet" media="screen" href="http://www.alpacajs.org/lib/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css"/>

        <title>Editor de registros</title>
    </head>
    <body>



        <div class="container">
            <h1>Editor</h1>

            <?php if ($_REQUEST["formType"] == "produsp"):  ?>

                <?php if (!$_REQUEST["crossrefDoi"]):  ?>

                    <div id="crossref"></div>
                    <script type="text/javascript">
                    $("#crossref").alpaca({
                        "options": {
                            "form": {
                                "attributes": {
                                    "action": "http://localhost/metadataEditor/index.php",
                                    "method": "get"
                                },
                                "buttons": {
                                    "submit": {
                                        "title": "Buscar DOI"
                                    }
                                }
                            }
                        },
                        "schema": {
                            "title": "Crossref",
                            "type": "object",
                            "properties": {
                                "crossrefDoi": {
                                    "type": "string",
                                    "title": "doi",
                                    "pattern": "^10.*",
                                    "minLength": 10
                                }
                            }
                        },
                        "view": {
                        "locale": "pt_BR"
                        }                    
                    });

                    </script>
                <?php endif; ?>            

                <?php echo $crossrefMessage ?>

                <div id="form"></div>
                <script type="text/javascript">
                $("#form").alpaca({
                    "data": <?php echo $recordJson; ?>,
                    "optionsSource": "./options.json",
                    "schemaSource": "./schema.json",
                    "view": {
                        "locale": "pt_BR"
                    }
                });

                </script>

                <?php elseif($_REQUEST["formType"] == "rppbci") : ?>

                    <div id="form"></div>
                    <script type="text/javascript">
                    $("#form").alpaca({
                        "data": <?php echo $recordJson; ?>,
                        "optionsSource": "./optionsRPPBCI.json",
                        "schemaSource": "./schemaRPPBCI.json",
                        "view": {
                            "locale": "pt_BR"
                        }
                    });

                    </script>

                <?php endif; ?> 

            <br/><br/><br/><br/><br/>

        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->

        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>






<?php if (isset($record["rppbci_id"])) : ?>

<form action="rppbci.php" method="post">
  <div class="form-group">   
    <input type="hidden" class="form-control" id="delete_id" name="delete_id" placeholder="delete_id" value="<?php echo $record["rppbci_id"]; ?>">
  </div>
  <button type="submit" class="btn btn-danger">Excluir registro</button>
</form>
    
<?php endif ?>

<?php if (isset($record["coletaprod_id"])) : ?>

<form action="coletaprod.php" method="post">
  <div class="form-group">   
    <input type="hidden" class="form-control" id="delete_id" name="delete_id" placeholder="delete_id" value="<?php echo $record["coletaprod_id"]; ?>">
  </div>
  <button type="submit" class="btn btn-danger">Excluir registro</button>
</form>
    
<?php endif ?>

<?php 
    unset($record);
    unset($recordJson); 

?>


    </body>
</html>