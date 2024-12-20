<?php
require_once '../cfg.php';  // Twój plik konfiguracyjny bazy danych
require_once 'shop_class.php';  // Główna klasa sklepu

$shop = new Shop($link);
$shop->displayShop();