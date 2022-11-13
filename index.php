<!doctype html>
<html lang="en">

<head>

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
    require 'inc/meta-header.php';
    require 'inc/functions.php';
    ?>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo $branch; ?></title>

    <link rel="canonical" href="">

    <style>
    .background {
        background-image: url("<?php echo $background_1 ?>");
        background-size: 100%;
        background-repeat: no-repeat;
    }
    </style>
</head>

<body>

    <!-- NAV -->
    <?php require 'inc/navbar.php'; ?>
    <!-- /NAV -->

    <main role="main">
        <div class="px-5 py-5 text-center background m-5">
            <h1 class="display-5 fw-bold p-5"><?php echo $branch; ?></h1>
            <div class="col-lg-6 mx-auto">
                <p class="lead mb-4"><?php echo $branch_description; ?></p>

                <form action="result.php">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" id="searchQuery"
                            aria-describedby="searchHelp" placeholder="Pesquise por termo ou autor">
                        <ul>
                            <li><small id="searchHelp" class="form-text text-muted">Dica: Use * para busca por
                                    radical. Ex: biblio*.</small></li>
                            <li><small id="searchHelp" class="form-text text-muted">Dica 2: Para buscas exatas,
                                    coloque entre ""</small></li>
                            <li><small id="searchHelp" class="form-text text-muted">Dica 3: Você também pode usar
                                    operadores booleanos: AND, OR</small></li>
                        </ul>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-lg px-4">Pesquisar</button>
                </form>

            </div>
        </div>

        <div class="container mt-5">

            <div class="row">
                <div class="col">
                    <h3 class="fs-2">Periódicos indexados</h3>
                    <ul class="list-group list-group-flush">
                        <?php Homepage::fieldAgg("source", "Artigo"); ?>
                    </ul>
                    <h2>Eventos indexados</h2>
                    <ul class="list-group list-group-flush">
                        <?php Homepage::fieldAgg("source", "Trabalho em evento"); ?>
                    </ul>
                </div>
                <div class="col">
                    <h3 class="fs-2">Estatísticas</h3>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Total de registros: <?php echo Admin::totalRecords(); ?></li>
                        <li class="list-group-item">Citações via Openalex API: <a
                                href="result.php?search=openalex.cited_by_count:[1 TO *]"><?php echo Homepage::sumFieldAggOpenalex(); ?></a>
                        </li>
                        <li class="list-group-item">Citações via Crossref API: <a
                                href="result.php?search=crossref.message.is-referenced-by-count:[1 TO *]"><?php echo Homepage::sumFieldAggCrossref(); ?></a>
                        </li>
                    </ul>
                </div>
                <!--
                <div class="col">
                    <h3 class="fs-2">Interações no facebook</h3>
                    <ul class="list-group list-group-flush">
                        < ?php Homepage::sumFieldAggFacebook(); ?>
                    </ul>
                </div>
                -->
            </div>

            <hr class="mt-5">
            <h3 class="fs-2">
                Registros com mais citações no <a href="https://openalex.org" target="_blank">Openalex</a>
            </h3>

            <?php Homepage::getLastRecords(); ?>

        </div>

    </main>

    <!-- FOOTER -->
    <?php require 'inc/footer.php'; ?>
    <!-- /FOOTER -->

</html>