<?php

$branch = "RPPBCI";
$branch_abrev = "RPPBCI";
$url_base = "http://localhost/rppbci";
$branch_description = "";

$hosts = ['localhost'];
$index = 'rppbci';
$indexAdm = 'rppbciadm';

$tematres_url = "";

/* Background images */
$background_1 = "inc/images/book.jpg";

$debug = true;

/* Load libraries for PHP composer */
require(__DIR__ . '/../vendor/autoload.php');
/* Load Elasticsearch Client */
$client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();