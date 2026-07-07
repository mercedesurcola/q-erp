<?php
require_once __DIR__ . '/../includes/funciones.php';

$_SESSION = [];
session_destroy();

header('Location: ' . '../login/index.php');
exit;
