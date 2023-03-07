<?php

// Устанавливаем токен бота и ссылку для отправки запросов
$botToken = '';
$website = 'https://api.telegram.org/bot' . $botToken;

// Получаем ID последнего обработанного сообщения
$lastUpdateId = 0;

// Бесконечный цикл для получения новых сообщений
while (true) {
    // Формируем URL для запроса новых сообщений
    $url = $website . '/getUpdates' . '?offset=' . ($lastUpdateId + 1) . '&timeout=30';

    // Отправляем запрос на сервер Telegram и получаем ответ
    $response = file_get_contents($url);

    // Если ответ не пустой, то обрабатываем полученные сообщения
    if (!empty($response)) {
        // Преобразуем ответ в массив данных
        $data = json_decode($response, true);

        // Обрабатываем каждое сообщение
        foreach ($data['result'] as $result) {
            // Получаем ID чата и текст сообщения
            $chatId = $result['message']['chat']['id'];
            $messageText = $result['message']['text'];

            // Обрабатываем команду /start и отправляем ответ
            if ($messageText == '/start') {
                file_get_contents($website . '/sendMessage?chat_id=' . $chatId . '&text=' . urlencode('Привет'));
            }

            // Обновляем ID последнего обработанного сообщения
            $lastUpdateId = $result['update_id'];
        }
    }

    echo $i++;

    // Задержка в 1 секунду перед отправкой следующего запроса
    sleep(1);
}