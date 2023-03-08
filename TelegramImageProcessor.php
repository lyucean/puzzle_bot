<?php

class TelegramImageProcessor
{

    public function squareImage($filename)
    {
        // Загружаем исходную картинку
        $source_image = imagecreatefromjpeg($filename);

        // Определяем размер исходной картинки
        $source_width = imagesx($source_image);
        $source_height = imagesy($source_image);

        // Определяем размер квадратной области, в которую будет обрезана картинка
        $target_size = min($source_width, $source_height);

        // Создаем новое изображение с заданными размерами
        $target_image = imagecreatetruecolor($target_size, $target_size);

        // Обрезаем исходное изображение до квадрата
        imagecopy(
          $target_image,      // целевое изображение
          $source_image,      // исходное изображение
          0,                  // координата X копируемой области исходного изображения
          0,                  // координата Y копируемой области исходного изображения
          ($source_width - $target_size) / 2,  // координата X целевой области изображения
          ($source_height - $target_size) / 2, // координата Y целевой области изображения
          $target_size,       // ширина целевой области
          $target_size        // высота целевой области
        );

        // Определяем размер выходного изображения
        $final_width = 1000;
        $final_height = 1000;

        // Создаем новое изображение с заданными размерами
        $final_image = imagecreatetruecolor($final_width, $final_height);

        // Растягиваем квадратное изображение до нужных размеров
        imagecopyresampled(
          $final_image,   // целевое изображение
          $target_image,  // исходное изображение
          0,              // координата X целевой области изображения
          0,              // координата Y целевой области изображения
          0,              // координата X копируемой области исходного изображения
          0,              // координата Y копируемой области исходного изображения
          $final_width,   // ширина целевой области
          $final_height,  // высота целевой области
          $target_size,   // ширина копируемой области исходного изображения
          $target_size    // высота копируемой области исходного изображения
        );

        // Сохраняем измененное изображение в файл
        imagejpeg($final_image, 'image/square.jpg', 100);
    }

    public function cutImagePieces(int $step)
    {
        $this->clearDir('image/path/');

        // Путь к изображению
        $image_path = 'image/square.jpg';

        // массив под кусочки пазла
        $arr_images = [];

        $tile_paths = [];

        // Загружаем изображение с помощью библиотеки GD
        $image = imagecreatefromjpeg($image_path);

        // Определяем ширину и высоту изображения
        $image_width = imagesx($image);
        $image_height = imagesy($image);

        // Определяем размер кусочков
        $tile_width = $image_width / $step;
        $tile_height = $image_height / $step;

        // создаём порядковые номера элементов и перемешаем их
        $array_tile = range(1, $step*$step);
        shuffle($array_tile);

        // победная комбинация
        var_dump(implode(".", $array_tile)); // string(32) "имя,почта,телефон"

        $col = 1;
        $row = 1;

        // Устанавливаем цвет и размер шрифта для текста
        $text_color = imagecolorallocate($image, 255, 255, 255);
        $font = 1; // Идентификатор шрифта по умолчанию


        // Перебираем все кусочки изображения
        for ($y = 0; $y < $image_height; $y += $tile_height) {
            for ($x = 0; $x < $image_width; $x += $tile_width) {
                // Создаем новый кусочек изображения
                $tile = imagecreatetruecolor($tile_width, $tile_height);
                // Копируем соответствующий участок изображения в новый кусочек
                imagecopy($tile, $image, 0, 0, $x, $y, $tile_width, $tile_height);

                $path = "image/path/tile_{$x}_{$y}.jpg";

                $rand_num = array_shift ($array_tile);

                // добавим в массив,
                $tile_paths[] = $path;

                // надо сохранять сб
                //сохраняем в массив
                $arr_images[] = [
                  'correct' => count($tile_paths),
                  'current' => $rand_num,
                  'path' => $path
                ];

                // Рисуем текст на изображении
                $text = count($tile_paths) . ' - ' . $rand_num;
                imagestring($tile, $font, 0, 0, $text, $text_color);

                // Сохраняем кусочек в файл
                imagejpeg($tile, $path);

                // Освобождаем память, занимаемую кусочком
                imagedestroy($tile);
                $col++;
            }
            $row++;
        }

        // Освобождаем память, занимаемую изображением
        imagedestroy($image);

        // нужно, чтоб он собирал по $arr_images
//        $this->glueImage($tile_paths, $step);

        return $arr_images;
    }

    function glueImage($tile_paths, $step)
    {
        // Установка количества строк и столбцов

        $tile_width = 1000 / $step;
        $tile_height = 1000 / $step;

        // Создание нового изображения для склеивания всех изображений
        $merged = imagecreatetruecolor(1000, 1000);

        // Установка цвета фона
        $bg_color = imagecolorallocate($merged, 255, 255, 255);
        imagefill($merged, 0, 0, $bg_color);

        // Определение текущей позиции в новом изображении
        $current_x = 0;
        $current_y = 0;

        // Склеивание изображений
        foreach ($tile_paths as $image_path) {
            // Загрузка изображения
            $image = imagecreatefromjpeg($image_path);

            // Копирование изображения в новое изображение
            imagecopy($merged, $image, $current_x, $current_y, 0, 0, $tile_width, $tile_height);

            // Освобождение памяти, занимаемой изображением
            imagedestroy($image);

            // Обновление текущей позиции
            $current_x += $tile_width;

            // Если текущая позиция превышает ширину нового изображения,
            // переходим на следующую строку
            if ($current_x >= $step * $tile_width) {
                $current_x = 0;
                $current_y += $tile_height;
            }
        }

        // Сохранение результирующего изображения в файл
        imagejpeg($merged, 'image/merged.jpg');

        // Освобождение памяти, занимаемой результирующим изображением
        imagedestroy($merged);
    }

    function clearDir($dir)
    {
        // Получаем список файлов и поддиректорий в директории
        $files = scandir($dir);

        // Проходимся по списку файлов и удаляем каждый
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                // Удаляем файл
                unlink($dir.'/'.$file);
            }
        }
    }
}
