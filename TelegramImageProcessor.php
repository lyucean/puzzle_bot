<?php

class TelegramImageProcessor
{
    public function squareImage($filename): void
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

    public function cutImagePieces(int $step): array
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

        // создаём порядковые номера элементов
        $array_tile = range(1, $step*$step);
        // перемешаем их
        shuffle($array_tile);

        // победная комбинация
        $right_sequence = implode(".", $array_tile);
        var_dump($right_sequence); // string(32)

        //TODO надо $right_sequence куда-то записать

        // Устанавливаем цвет и размер шрифта для текста
        $text_color = imagecolorallocate($image, 255, 255, 255);
        $font = 3; // Идентификатор шрифта по умолчанию
        $count = 0; // Идентификатор шрифта по умолчанию

        // Перебираем все кусочки изображения
        for ($y = 0; $y < $image_height; $y += $tile_height) {
            for ($x = 0; $x < $image_width; $x += $tile_width) {
                // Создаем новый кусочек изображения
                $tile = imagecreatetruecolor($tile_width, $tile_height);
                // Копируем соответствующий участок изображения в новый кусочек
                imagecopy($tile, $image, 0, 0, $x, $y, $tile_width, $tile_height);

                // путь до картинки
                $path = "image/path/tile_{$x}_{$y}.jpg";

                // правильный номер пазла
                $correct_num = $count++;

                // берём первый элемент случайного массива, это будет новое положение пазла
                $current_num = array_shift ($array_tile);

                // добавим в массив,
                $tile_paths[] = $path;

                //сохраняем в массив
                $arr_images[] = [
                  'correct' => $correct_num,
                  'current' => $current_num,
                  'path' => $path
                ];

                // Рисуем текст сетки на порезанном кусочке
                $text = $current_num . ' - ' . $correct_num;
                imagestring($tile, $font, 0, 0, $text, $text_color);

                // Сохраняем кусочек в файл
                imagejpeg($tile, $path, 100);

                // Освобождаем память, занимаемую кусочком
                imagedestroy($tile);
            }
        }

        // Освобождаем память, занимаемую изображением
        imagedestroy($image);

        // TODO сохраняем это в БД

        // склеиваем пазл
        $this->glueImage($arr_images, $step);

        return $arr_images;
    }

    function glueImage($arr_images, $step)
    {
        // Установка количества строк и столбцов

        $tile_width = 1000 / $step;
        $tile_height = 1000 / $step;

        // Создание нового изображения для склеивания всех изображений
        $merged = imagecreatetruecolor(1000, 1000);

        // Установка цвета фона
        imagefill($merged, 0, 0, imagecolorallocate($merged, 255, 255, 255));

        // Определение текущей позиции в новом изображении
        $current_x = 0;
        $current_y = 0;


        //TODO временно для теста
        usort($arr_images, function ($a, $b)
        {
            if ($a['current'] == $b['current']) {
                return 0;
            }
            return ($a['current'] < $b['current']) ? -1 : 1;
        });


        // Склеивание изображений
        foreach ($arr_images as $image) {
            // Загрузка изображения
            $image = imagecreatefromjpeg($image['path']);

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
        imagejpeg($merged, 'image/merged.jpg', 100);

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
