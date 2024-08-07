<!DOCTYPE html>
<?php
session_start();

$errorMsg = "";
$validUser = $_SESSION["login"] === true;
if (isset($_POST["username"])) {
    $validUser = $_POST["username"] == "rppbci_admin" && $_POST["password"] == "rppbci_admin";
    if (!$validUser) $errorMsg = "Usuário ou senha inválidos.";
    else $_SESSION["login"] = true;
}


require 'inc/config.php';
require 'inc/functions.php';

if (!empty($_POST)) {
    Admin::addDivulgacao($_POST["titulo"], $_POST["url"], $_POST["id"]);
}

$result_get = Requests::getParser($_GET);
$limit = $result_get['limit'];
$page = $result_get['page'];
$params = [];
$params["index"] = $index;
$params["body"] = $result_get['query'];
$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];
if (isset($_GET["sort"])) {
    $result_get['query']["sort"][$_GET["sort"]]["unmapped_type"] = "long";
    $result_get['query']["sort"][$_GET["sort"]]["missing"] = "_last";
    $result_get['query']["sort"][$_GET["sort"]]["order"] = "desc";
    $result_get['query']["sort"][$_GET["sort"]]["mode"] = "max";
} else {
    $result_get['query']["sort"]['openalex.cited_by_count'] = "desc";
    $result_get['query']["sort"]['datePublished.keyword'] = "desc";
}
$params["body"] = $result_get['query'];
$params["size"] = $limit;
$params["from"] = $result_get['skip'];
$cursor = $client->search($params);



// /* Citeproc-PHP*/
// require 'inc/citeproc-php/CiteProc.php';
// $csl_abnt = file_get_contents('inc/citeproc-php/style/abnt.csl');
// $csl_apa = file_get_contents('inc/citeproc-php/style/apa.csl');
// $csl_nlm = file_get_contents('inc/citeproc-php/style/nlm.csl');
// $csl_vancouver = file_get_contents('inc/citeproc-php/style/vancouver.csl');
// $lang = "br";
// $citeproc_abnt = new citeproc($csl_abnt, $lang);
// $mode = "reference";

?>
<html>

<head>
    <?php require 'inc/meta-header.php' ?>
    <title>Resultado da busca</title>

    <!-- Altmetric Script -->
    <script type='text/javascript' src='https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js'></script>

    <!-- PlumX Script -->
    <script type="text/javascript" src="//cdn.plu.mx/widget-details.js"></script>


</head>

<body>

    <?php
    if (file_exists('inc/google_analytics.php')) {
        include 'inc/google_analytics.php';
    }
    ?>

    <!-- NAV -->
    <?php require 'inc/navbar.php'; ?>
    <!-- /NAV -->
    <main role="main">
        <div class="container mt-3">
            <div class="row">

                <div class="col-8">
                    <!-- PAGINATION -->
                    <?php UI::pagination($page, $total, $limit); ?>
                    <!-- /PAGINATION -->
                    <br />

                    <?php if ($total == 0) : ?>

                        <div class="alert alert-info" role="alert">
                            Sua busca não obteve resultado. Você pode refazer sua busca abaixo:<br /><br />
                            <form action="result.php">
                                <div class="form-group">
                                    <input type="text" name="search" class="form-control" id="searchQuery" aria-describedby="searchHelp" placeholder="Pesquise por termo ou autor">
                                    <small id="searchHelp" class="form-text text-muted">Dica: Use * para busca por radical.
                                        Ex: biblio*.</small>
                                    <small id="searchHelp" class="form-text text-muted">Dica 2: Para buscas exatas, coloque
                                        entre ""</small>
                                    <small id="searchHelp" class="form-text text-muted">Dica 3: Você também pode usar
                                        operadores booleanos: AND, OR</small>
                                </div>
                                <button type="submit" class="btn btn-primary">Pesquisar</button>

                            </form>
                        </div>
                        <br /><br />

                    <?php endif; ?>

                    <!-- Resultados -->
                    <?php foreach ($cursor["hits"]["hits"] as $r) : ?>

                        <div class="card mt-1">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo $r["_source"]['type']; ?>
                                    <?php if (!empty($r["_source"]['source'])) : ?>
                                        <h6 class="card-subtitle mb-2 text-muted"><?php echo $r["_source"]['source']; ?>
                                            <?php (isset($r["_source"]["isPartOf"]["volume"]) ?  print_r(" - v." . $r["_source"]["isPartOf"]["volume"]) : "") ?>
                                            <?php (isset($r["_source"]["isPartOf"]["issue"]) ? print_r(" - n." . $r["_source"]["isPartOf"]["issue"]) : "") ?>
                                            <?php (isset($r["_source"]["isPartOf"]["initialPage"]) ? print_r(" - p." . $r["_source"]["isPartOf"]["initialPage"]) : "") ?>
                                        </h6>
                                    <?php endif; ?>
                                    <h5 class="card-title">
                                        <a class="text-dark" href="<?php echo $r['_source']['url']; ?>"><?php echo $r["_source"]['name']; ?>
                                            (<?php echo $r["_source"]['datePublished']; ?>)
                                        </a>
                                        <?php if (!empty($r["_source"]['openalex']['id'])) : ?>
                                            <a href="<?php echo $r["_source"]['openalex']['id'] ?>" target="_blank"><img src="inc/images/openalex400x400.jpg" width="20" height="20"></a>
                                        <?php endif; ?>
                                    </h5>
                                    <?php if (!empty($r["_source"]["alternateName"])) : ?>
                                        <h6 class="card-subtitle mb-2 text-muted"><?php echo $r["_source"]['alternateName']; ?>
                                        </h6>
                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]["author"])) : ?>
                                        <?php
                                        foreach ($r["_source"]["author"] as $autores) {
                                            if (!empty($autores["organization"]["name"])) {
                                                $authors_array[] = '' . $autores["person"]["name"] . ' (' . $autores["organization"]["name"] . ')';
                                            } else {
                                                $authors_array[] = '' . $autores["person"]["name"] . '';
                                            }
                                        }
                                        $array_aut = implode("; ", $authors_array);
                                        unset($authors_array);
                                        echo '<p class="text-muted"><b>Autores:</b> ' . '' . $array_aut . '</p>';
                                        ?>
                                    <?php endif; ?>

                                    <?php if (!empty($r['_source']['openalex']['authorships'])) : ?>
                                        <?php
                                        echo '<p class="text-muted"><b>Autores no Openalex:</b>';
                                        echo '<ul>';
                                        foreach ($r['_source']['openalex']['authorships'] as $author) {
                                            //echo "<pre>".print_r($author, true)."</pre>";

                                            echo '<li>' . $author['author']['display_name'] . '
                                            ' . ((isset($author['raw_affiliation_string'])) ? '(' . $author['raw_affiliation_string'] . ')' : '') . '
                                            ' . (($author['author']['orcid']) ? '<a href="' . $author['author']['orcid'] . '" target="_blank"><img src="inc/images/240px-ORCID_iD.svg.png" width="20" height="20"></a>' : '') . '
                                            <a href="' . $author['author']['id'] . '" target="_blank">
                                                <img src="inc/images/openalex400x400.jpg" width="20" height="20">
                                            </a></li>';
                                        }
                                        echo '</ul>';

                                        ?>
                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['about'])) : ?>
                                        <?php
                                        foreach ($r["_source"]['about'] as $assunto) {
                                            $assunto_array[] = '' . $assunto . '';
                                        }
                                        $array_assunto = implode("; ", $assunto_array);
                                        unset($assunto_array);
                                        echo '<p class="text-muted"><b>Assuntos:</b> ' . '' . $array_assunto . '</p>';
                                        ?>
                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['openalex']['concepts'])) : ?>
                                        <?php
                                        foreach ($r["_source"]['openalex']['concepts'] as $concept) {
                                            $concept_array[] = '' . $concept['display_name'] . '';
                                        }
                                        $array_concept = implode("; ", $concept_array);
                                        unset($concept_array);
                                        echo '<p class="text-muted"><b>Conceitos definidos pelo Openalex:</b> ' . '' . $array_concept . '</p>';
                                        ?>
                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['description'])) : ?>
                                        <p class="text-muted"><b>Resumo:</b> <?php echo $r["_source"]['description'] ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['NM_PROGRAMA'])) : ?>
                                        <p class="text-muted"><b>Programa de Pós Graduação:</b>
                                            <?php echo $r["_source"]['NM_PROGRAMA'] ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['publisher']['organization']['name'])) : ?>
                                        <p class="text-muted"><b>Editora:</b>
                                            <?php echo $r["_source"]['publisher']['organization']['name']; ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['ISBN'])) : ?>
                                        <p class="text-muted"><b>ISBN:</b> <?php echo $r["_source"]['ISBN'][0]; ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['doi'])) : ?>
                                        <p class="text-muted"><b>DOI:</b> <a href="http://dx.doi.org/<?php echo $r["_source"]['doi']; ?>" target="_blank"><?php echo $r["_source"]['doi']; ?></a></p>
                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['openalex'])) : ?>
                                        <?php if (!is_null($r["_source"]['openalex']['doi'])) : ?>
                                            <p class="text-muted">
                                                <b>DOI:</b> <a href="<?php echo $r["_source"]['openalex']['doi']; ?>" target="_blank"><?php echo $r["_source"]['openalex']['doi']; ?></a>
                                            </p>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['url'])) : ?>
                                        <p class="text-muted"><b>URL:</b> <a href="<?php echo $r["_source"]['url']; ?>" target="_blank"><?php echo $r["_source"]['url']; ?></a></p>
                                    <?php endif; ?>

                                    <!-- <p class="text-muted"><a class="btn btn-info"
                                        href="node.php?_id=< ?php echo $r["_id"]; ?>" target="_blank"><b>Ver registro
                                            completo</b></a></p>
                                -->

                                    <?php if (!empty($r["_source"]['references'])) : ?>
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#<?php echo substr($r["_id"], 1, 6) ?>">
                                            Ver referências
                                        </button>

                                        <!-- Modal -->
                                        <div class="modal fade" id="<?php echo substr($r["_id"], 1, 6) ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo $r["_id"] ?>Title" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="<?php echo $r["_id"] ?>Title">Referências
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        foreach ($r["_source"]['references'] as $ref) {
                                                            echo '' . $ref["type"] . ': ' . implode("; ", $ref["authors"]) . '. ' . $ref["name"] . '. ' . $ref["publisher"] . ', ' . $ref["datePublished"] . '.<br/>';
                                                            //print_r($ref);
                                                            //echo "<br/><br/>";
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>


                                    <?php if (!empty($r["_source"]['crossref']['message']['is-referenced-by-count'])) : ?>

                                        <div class="alert alert-success" role="alert">
                                            Quantidade de vezes em que o artigo foi citado:
                                            <?php echo $r["_source"]['crossref']['message']['is-referenced-by-count'] ?> (Fonte:
                                            Crossref API)
                                        </div>

                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['openalex']['cited_by_count'])) : ?>

                                        <div class="alert alert-success" role="alert">
                                            Quantidade de vezes em que o artigo foi citado:
                                            <?php echo $r["_source"]['openalex']['cited_by_count'] ?> (Fonte:
                                            Openalex API)
                                        </div>

                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['opencitations']['citation_count'])) : ?>

                                        <div class="alert alert-success" role="alert">
                                            Quantidade de vezes em que o artigo foi citado no OpenCitations:
                                            <?php echo $r["_source"]['opencitations']['citation_count'] ?> (Fonte:
                                            OpenCitations API)
                                        </div>

                                    <?php endif; ?>

                                    <?php if (!empty($r["_source"]['doi'])) : ?>
                                        <div data-badge-popover="right" data-badge-type="1" data-doi="<?php echo $r["_source"]['doi']; ?>" data-hide-no-mentions="true" class="altmetric-embed"></div>
                                        <a href="https://plu.mx/plum/a/?doi=<?php echo $r["_source"]['doi']; ?>" class="plumx-details" data-hide-when-empty="true" data-badge="true"></a>
                                        <div data-badge-details="right" data-badge-type="2" data-doi="<?php echo $r["_source"]['doi']; ?>" data-condensed="true" data-hide-no-mentions="true" class="altmetric-embed"></div>
                                        <div><span class="__dimensions_badge_embed__" data-doi="<?php echo $r["_source"]['doi']; ?>" data-hide-zero-citations="true" data-style="small_rectangle"></span></div>
                                        </li>
                                    <?php endif; ?>

                                    <?php //print("<pre>".print_r($r['_source'], true)."</pre>"); 
                                    ?>



                            </div>
                        </div>


                        <!-- 

                        <div class="uk-grid-divider uk-padding-small" uk-grid>
                            <div class="uk-width-1-5@m">
                                <a href="result.php?search=source.keyword:&quot;< ?php echo $r["_source"]['source'];?>&quot;">< ?php echo $r["_source"]['source'];?></a>
                            </div>
                            <div class="uk-width-4-5@m">
                                <article class="uk-article">
                                <p class="uk-text-lead uk-margin-remove" style="font-size:115%"><a href="< ?php echo $r['_source']['url'];?>">< ?php echo $r["_source"]['name'];?>< ?php if (!empty($r["_source"]['datePublished'])) { echo ' ('.$r["_source"]['datePublished'].')'; } ?></a></p>

                                        <p class="uk-margin-remove">

                                        </p>


                                        < ?php if (isset($_GET["papel"])) : ?>
                                            < ?php if ($_GET["papel"] == "admin") : ?>
                                                <form class="uk-form uk-form-stacked" action="result.php?search=" method="POST">

                                                    <fieldset data-uk-margin>
                                                        <legend>Inserir URL de divulgação científica</legend>
                                                        <div class="uk-form-row">
                                                            <label class="uk-form-label" for="">Título</label>
                                                            <div class="uk-form-controls"><input type="text" placeholder="" name="titulo" class="uk-width-1-1"></div>
                                                        </div>
                                                        <div class="uk-form-row">
                                                            <label class="uk-form-label" for="">URL</label>
                                                            <div class="uk-form-controls"><input type="text" placeholder="" name="url" class="uk-width-1-1"></div>
                                                        </div>
                                                        <input type="hidden" name="id" value="< ?php echo $r['_id']; ?>">
                                                        <button class="uk-button">Enviar</button>
                                                    </fieldset>

                                                </form>
                                            < ?php endif; ?>
                                        < ?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        -->
                    <?php endforeach; ?>

                    <!-- /RECORDS -->
                    <!-- PAGINATION -->
                    <?php UI::pagination($page, $total, $limit); ?>
                    <!-- /PAGINATION -->

                </div>
                <div class="col-4">

                    <!-- Facetas - Início -->
                    <h3 class="uk-panel-title">Refinar busca</h3>
                    <hr>
                    <div class="accordion" id="facets">

                        <?php
                        $facets = new facets();
                        $facets->query = $result_get['query'];

                        if (!isset($_GET)) {
                            $_GET = null;
                        }

                        $facets->facet("type", 100, "Tipo", null, "_term", $_GET);
                        $facets->facet("source", 100, "Título do periódico", null, "_term", $_GET);
                        $facets->facet("datePublished", 120, "Ano de publicação", null, "_term", $_GET);
                        $facets->facet("author.person.name", 120, "Autores", null, "_term", $_GET);
                        $facets->facet("author.organization.name", 120, "Afiliação", null, "_term", $_GET);
                        $facets->facet("openalex.authorships.raw_affiliation_string", 120, "Afiliação obtida pelo Openalex", null, "_term", $_GET);
                        $facets->facet("openalex.authorships.institutions.display_name", 120, "Afiliação normalizada obtida pelo Openalex", null, "_term", $_GET);
                        $facets->facet("originalType", 10, "Seções", null, "_term", $_GET);
                        $facets->facet("about", 100, "Assuntos", null, "_term", $_GET);
                        $facets->facet("publisher.organization.name", 100, "Editora", null, "_term", $_GET);
                        $facets->facet("isPartOf.name", 100, "Fonte", null, "_term", $_GET);
                        $facets->facet("isPartOf.volume", 100, "Volume", null, "_term", $_GET);
                        $facets->facet("isPartOf.issue", 100, "Fascículo", null, "_term", $_GET);
                        $facets->facet("isPartOf.ISSN", 100, "ISSN", null, "_term", $_GET);
                        $facets->facet("references.authors", 100, "Autores mais citados nas referências", null, "_term", $_GET);
                        $facets->facet("references.datePublished", 100, "Ano de publicação das obras citadas nas referências", null, "_term", $_GET);
                        $facets->facet("openalex.concepts.display_name", 100, "Openalex Concepts", null, "_term", $_GET);
                        $facets->facet("inLanguage", 120, "Idioma", null, "_term", $_GET);
                        $facets->facet("NM_PROGRAMA", 120, "Nome do Programa de Pós Graduação", null, "_term", $_GET);
                        $facets->facet_range("openalex.cited_by_count", 100, "Citações no Openalex", 'INT');
                        $facets->facetExistsField("doi", 2, "Possui DOI preenchido?", null, "_term", $_GET);
                        $facets->facetExistsField("openalex.id", 2, "Openalex?", null, "_term", $_GET);
                        $facets->facetExistsField("opencitations.citation_count", 2, "OpenCitations?", null, "_term", $_GET);
                        $facets->facet_range("opencitations.citation_count", 100, "Citações no OpenCitations", 'INT');
                        ?>

                    </div>
                </div>
            </div>
        </div>
        <hr />



        </div>



        <script>
            $('[data-uk-pagination]').on('select.uk.pagination', function(e, pageIndex) {
                var url = window.location.href.split('&page')[0];
                window.location = url + '&page=' + (pageIndex + 1);
            });
        </script>
        <script async src="https://badge.dimensions.ai/badge.js" charset="utf-8"></script>
    </main>
    <!-- FOOTER -->
    <?php require 'inc/footer.php'; ?>
    <!-- /FOOTER -->

</body>

</html>