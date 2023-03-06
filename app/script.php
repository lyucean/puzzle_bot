<?php
$status = file_get_contents('https://www.google.com');
$log_message = date('Y-m-d H:i:s') . ' - ' . ($status ? 'Success' : 'Failure') . PHP_EOL;
echo $log_message;
file_put_contents('/var/log/app/log.txt', $log_message, FILE_APPEND);
