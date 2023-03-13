<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаем библиотеку Dotenv и загружаем переменные окружения из файла .env
require __DIR__.'/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Подключаем класс TelegramDB
require_once 'TelegramDB.php';

// Создаем объект TelegramDB и подключаемся к базе данных
//$telegramDB = new TelegramDB($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], 'telegram_bot_db');

// Подключаем класс работы с картинкой
require_once 'TelegramImageProcessor.php';

$ImageProcessor = new TelegramImageProcessor();

$arr_images = $ImageProcessor->cutImagePieces(10);

//foreach ($arr_images as $arr){
//    $telegramDB->addImage($arr['x'], $arr['y'], $arr['path']);
//}


