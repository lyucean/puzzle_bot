<?php

// Подключаем библиотеку Dotenv и загружаем переменные окружения из файла .env
require __DIR__.'/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Устанавливаем токен бота и ссылку для отправки запросов
$botToken = $_ENV['TELEGRAM_BOT_TOKEN'];

// Устанавливаем ссылку для отправки запросов
$website = 'https://api.telegram.org/bot'.$botToken;

// Подключаем класс TelegramDB
require_once 'TelegramDB.php';

// Подключаем класс работы с картинкой
require_once 'TelegramImageProcessor.php';

// Создаем объект TelegramDB и подключаемся к базе данных
$telegramDB = new TelegramDB($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], 'telegram_bot_db');

$ImageProcessor = new TelegramImageProcessor();

// Получаем ID последнего обработанного сообщения
$lastUpdateId = 0;

// Бесконечный цикл для получения новых сообщений
while (true) {
    // Формируем параметры запроса
    $params = [
      'allowed_updates' => ['message', 'channel_post', 'callback_query'],
      'timeout' => 30,
    ];
    if ($lastUpdateId) {
        $params['offset'] = $lastUpdateId + 1;
    }

    echo '1 - '.PHP_EOL;

    // Формируем URL для запроса новых сообщений
    $url = $website.'/getUpdates?'.http_build_query($params);

    // Отправляем запрос на сервер Telegram и получаем ответ
    $response = file_get_contents($url);

    echo '2 - '.PHP_EOL;

    // Если ответ не пустой, то обрабатываем полученные сообщения
    if (!empty($response)) {
        echo '3 - '.PHP_EOL;

        // Преобразуем ответ в массив данных
        $data = json_decode($response, true);

        // Обрабатываем каждое сообщение
        foreach ($data['result'] as $result) {
            // Получаем ID чата и текст сообщения
            if (isset($result['message'])) {
                $chatId = $result['message']['chat']['id'];
                $messageText = isset($result['message']['text']) ? $result['message']['text'] : '';
            } elseif (isset($result['channel_post'])) {
                $chatId = $result['channel_post']['chat']['id'];
                $messageText = isset($result['channel_post']['text']) ? $result['channel_post']['text'] : '';
            } else {
                continue; // Пропускаем обработку, если сообщение не является текстовым
            }

            // Добавляем новое сообщение в базу данных
            $chatId = $result['message']['chat']['id'];
            $messageText = isset($result['message']['text']) ? $result['message']['text'] : '';
            $messageDate = date('Y-m-d H:i:s', $result['message']['date']);
            $telegramDB->addMessage($chatId, $messageText, $messageDate);


            // Обрабатываем команду /start и отправляем ответ
            if (!empty($messageText) && $messageText == '/start') {
                file_get_contents($website.'/sendMessage?chat_id='.$chatId.'&text='.urlencode('Привет'));
            }

            // Получаем информацию о файле с помощью метода getFile
            if (isset($result['message']['document'])) {
                $fileId = $result['message']['document']['file_id'];
                $fileInfo = json_decode(file_get_contents($website.'/getFile?file_id='.$fileId), true);
                $fileUrl = 'https://api.telegram.org/file/bot'.$botToken.'/'.$fileInfo['result']['file_path'];
                $fileSize = $fileInfo['result']['file_size'];
                $fileName = $result['message']['document']['file_name'];

                // Определяем тип файла
                $fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);
                if ($fileExtension == 'jpg' || $fileExtension == 'jpeg') {
                    $fileType = 'image/jpeg';
                } elseif ($fileExtension == 'png') {
                    $fileType = 'image/png';
                } elseif ($fileExtension == 'gif') {
                    $fileType = 'image/gif';
                } else {
                    $fileType = '';
                }

                // Проверяем тип файла
                if ($fileType != '') {
                    // Сохраняем файл без сжатия
                    $source = fopen($fileUrl, 'rb');

                    // Устанавливаем путь к файлу картинки
                    $filename = 'image/raw.jpg';

                    $destination = fopen('image/raw.jpg', 'wb');
                    stream_copy_to_stream($source, $destination);
                    fclose($source);
                    fclose($destination);

                    // сделаем её квадратной
                    $ImageProcessor->squareImage($filename);

                    // Отправляем ответное сообщение о сохранении картинки
                    file_get_contents(
                      $website.'/sendMessage?chat_id='.$chatId.'&text='.urlencode('Картинка сохранена')
                    );
                } else {
                    // Отправляем ответное сообщение о недопустимом chat_id
                    file_get_contents(
                      $website.'/sendMessage?chat_id='.$chatId.'&text='.urlencode('Недопустимый chat_id')
                    );
                }
            }

            // Обновляем ID последнего обработанного сообщения
            $lastUpdateId = $result['update_id'];
        }
    }

    echo $lastUpdateId.PHP_EOL;

    // Задержка в 1 секунду перед отправкой следующего запроса
    sleep(1);
}