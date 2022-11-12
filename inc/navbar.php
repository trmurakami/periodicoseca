<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
    <div class="container">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault"
            aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="<?php echo "$url_base/"?>index.php">Início <span
                            class="sr-only">(current)</span></a>
                </li>
            </ul>
            <li class="nav-item navbar-nav">
                <a class="nav-link" href="about.php">Sobre</a>
            </li>
        </div>

        <!-- < ?php if (!isset($_SESSION["login"])) : ?>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#login">
            Login
        </button>
        < ?php endif; ?> -->
    </div>
</nav>