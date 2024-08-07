<?php

session_start();
if (!$_SESSION["login"]) {
    header("Location: login.php");
    die();
}

?>

<html>

<head>
    <?php
    include('inc/config.php');
    include('inc/functions.php');
    include('inc/meta-header.php');
    ?>
    <title>Administração</title>
    <script type="text/javascript" src="inc/uikit/js/components/grid.js"></script>
    <script type="text/javascript" src="inc/uikit/js/components/parallax.min.js"></script>
</head>

<body>
    <main role="main">
        <div class="container">

            <?php include('inc/navbar.php') ?>
            <br /><br /><br /><br />

            <h2>Administração</h2>

            <form action="harvester.php" method="get">

                <div class="form-group">
                    <label for="oai">OAI</label>
                    <input type="text" class="form-control" id="oai" aria-describedby="oaiHelp" name="oai"
                        placeholder="Incluir a URL do OAI">
                    <small id="oaiHelp" class="form-text text-muted">Incluir a URL do OAI</small>
                </div>

                <div class="form-group">
                    <label for="set">SET-OAI</label>
                    <input type="text" class="form-control" id="set" aria-describedby="setHelp" name="set"
                        placeholder="Incluir um SET-OAI. Em branco por padrão">
                    <small id="setHelp" class="form-text text-muted">Incluir um SET-OAI. Em branco por padrão</small>
                </div>

                <div class="form-group">
                    <label for="repositoryName">Nome alternativo para a fonte</label>
                    <input type="text" class="form-control" id="repositoryName" aria-describedby="repositoryNameHelp"
                        name="repositoryName" placeholder="Nome alternativo da fonte">
                    <small id="repositoryNameHelp" class="form-text text-muted">Informe o nome alternativo para a fonte.
                        Opcional</small>
                </div>

                <div class="form-group">
                    <label for="area">Grande área</label>
                    <input type="text" class="form-control" id="area" aria-describedby="areaHelp" name="area"
                        placeholder="Grande área">
                    <small id="areaHelp" class="form-text text-muted">Informe a grande área.
                        Opcional</small>
                </div>

                <?php if ($useTematres == true) : ?>
                <input type="hidden" name="useTematres" value="true">
                <?php endif ?>

                <!--
                    <div class="form-group">
                        <label for="areaChild">Área de Conhecimento - Nível 2</label>
                        <input type="text" class="form-control" id="areaChild" aria-describedby="areaChildHelp" name="areaChild" placeholder="Informe a Área de Conhecimento - Nível 2">
                        <small id="areaChildHelp" class="form-text text-muted">Informe a Área de Conhecimento - Nível 2</small>
                    </div>

                    <div class="form-group">
                        <label for="corrente">Corrente / Não corrente</label>
                        <select class="form-control" id="corrente" name="corrente">
                            <option value="corrente">corrente</option>
                            <option value="não corrente">não corrente</option>
                        </select>
                    </div>
                    -->

                <div class="form-group">
                    <label for="metadataFormat">Formato de metadados</label>
                    <select class="form-control" id="metadataFormat" name="metadataFormat">
                        <option value="oai_dc" selected>oai_dc</option>
                        <option value="nlm">nlm</option>
                        <option value="rfc1807">rfc1807</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="typeOfContent">Tipo de conteúdo</label>
                    <select class="form-control" id="typeOfContent" name="typeOfContent">
                        <option value="Artigo">Artigo</option>
                        <option value="Trabalho em evento">Trabalho em evento</option>
                    </select>
                </div>

                <button class="btn btn-primary">Inserir</button>

            </form>

            <h4>Upload de arquivo CSV (E-books)</h4>

            <form class="m-3" action="tools/import_csv.php" method="post" accept-charset="utf-8"
                enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="formFile" class="form-label">Enviar um arquivo CSV (UTF-8, separado por
                        tabulações)</label>
                    <input class="form-control" type="file" id="formFile" name="file">
                </div>
                <button type="submit" class="btn btn-primary mb-3">Upload</button>
            </form>

            <h4>Upload de arquivo CSV Biblioteca de Teses e Dissertações da CAPES</h4>

            <form class="m-3" action="tools/import_csv_btd_capes.php" method="post" accept-charset="utf-8"
                enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="formFile" class="form-label">Enviar um arquivo CSV (UTF-8, separado por
                        tabulações)</label>
                    <input class="form-control" type="file" id="formFile" name="file">

                    <label for="area">Grande área</label>
                    <input type="text" class="form-control" id="area" aria-describedby="areaHelp" name="area"
                        placeholder="Grande área">
                    <small id="areaHelp" class="form-text text-muted">Informe a grande área.
                        Opcional</small>


                </div>
                <button type="submit" class="btn btn-primary mb-3">Upload</button>
            </form>

            <h3>Estatísticas</h3>
            <p>Total de registros: <?php echo Admin::totalRecords(); ?></p>
            <p>Total de registros com doi: <?php echo Admin::harvestStatus("doi"); ?></p>

            <h3>Openalex</h3>
            <p>Registros coletados no <a href="https://openalex.org/" target="_blank">Openalex</a>:
                <?php echo Admin::harvestStatus("openalex"); ?></p>
            <p><a href="tools/openalex.php?size=10">Coletar Openalex</a></p>
            <p><a href="tools/get_doi_from_openalex.php?size=1">Coletar DOI por Título no OpenAlex</a></p>

            <h3>OpenCitations</h3>
            <p>Registros coletados no <a href="https://opencitations.net/" target="_blank">OpenCitations</a>:
                <?php echo Admin::harvestStatus("opencitations"); ?></p>
            <p><a href="tools/opencitations.php?size=10">Coletar citações no OpenCitations</a></p>

            <h3>Fontes coletadas</h3>
            <div class="uk-alert-primary" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><a href="tools/export.php">Exportar todos os registros</a></p>
            </div>
            <?php Admin::sources("source"); ?>
            <?php require 'inc/footer.php' ?>
        </div>

    </main>

</body>

</html>