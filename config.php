<?php
// config.php — fichier de configuration central

// Convertir les chemins Windows en chemins URL
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$current  = str_replace('\\', '/', __DIR__);

// Chemin URL de base
$base_url = str_replace($doc_root, '', $current) . '/';