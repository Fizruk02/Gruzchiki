<?php

    $d2 = $_SERVER['DOCUMENT_ROOT']!=''?$_SERVER['DOCUMENT_ROOT']:__DIR__.'/../..';

    include($d2.'/../_data.php');
    
    $mysqli = new mysqli(dbHost, dbUser, dbPass, dbName);
    $mysqli -> query("SET NAMES 'utf8mb4';");
    $mysqli -> query("SET CHARACTER SET 'utf8mb4';"); 
    $mysqli -> query("SET SESSION collation_connection = 'utf8mb4_unicode_ci';");
    
    $pdo = new PDO('mysql:host='.dbHost.';dbname='.dbName, dbUser, dbPass);
    $stmt = $pdo->prepare("SET NAMES 'utf8mb4';");
    $stmt->execute($par);
    $stmt = $pdo->prepare("SET CHARACTER SET 'utf8mb4';");
    $stmt->execute($par);
    $stmt = $pdo->prepare("SET SESSION collation_connection = 'utf8mb4_unicode_ci';");
    $stmt->execute($par);
    $pdo->query("SET wait_timeout=3000;");