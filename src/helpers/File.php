<?php

    namespace tomdom\core\helpers;

    class File {
        
        static function toTranslit($input)
        {
            $input = mb_strtolower(strip_tags($input), 'UTF-8');

            $input = str_replace(array("й","ц","у","к","е","н","г","ш","щ","з","х","ъ","ф","ы","в","а","п","р","о","л","д","ж","э","я","ч","с","м","и","т","ь","б","ю","ё"), array("y","c","u","k","e","n","g","sh","sh","z","x","_","f","u","v","a","p","r","o","l","d","g","e","ya","ch","s","m","i","t","_","b","yu","e"), $input);
            $input = str_replace(array("Й","Ц","У","К","Е","Н","Г","Ш","Ц","З","Х","Ъ","Ф","Ы","В","А","П","Р","О","Л","Д","Ж","Э","Я","Ч","С","М","И","Т","Ь","Б","Ю","Ё"), array("y","c","u","k","e","n","g","sh","sh","z","x","_","f","u","v","a","p","r","o","l","d","g","e","ya","ch","s","m","i","t","_","b","yu","e"), $input);
            $input = str_replace(array("'",'"',"\\",",","%","&",':',';','*','@','«','»', '=', '’'), '', $input);
            $input = str_replace(array(" ","/","!","?","#","№",'<','>','(',')', '?'), "_", $input);

            return $input;
        }
        
        /**
         * 
         * @param type $file имя поля в массиве _FILES
         * @param type $folder папка куда залить файл
         * @param type $file_name имя файла после заливки
         * @param type $ext допустимые расширения файла
         * @param type $minx
         * @param type $miny
         * @param type $maxx
         * @param type $maxy
         * @return string
         */
        public static function catchFile($file, $folder, $file_name = '', $ext = 'gif,png,jpeg,jpg', $minx = 0, $miny = 0, $maxx = 0, $maxy = 0)
        {
            $result = array();

            //printr($_FILES[$file]);

            if (!empty($_FILES[$file]['tmp_name']) && $_FILES[$file]['tmp_name'] != 'none')
            {
                if (!empty ($_FILES[$file]['error']))
                {
                    switch ($_FILES[$file]['error'])
                    {
                        case '1'   : $error = 'Превышен допустимый размер файла';   break;
                        case '2'   : $error = 'Превышен допустимый размер файла';   break;
                        case '3'   : $error = 'Файл загружен лишь частично'; break;
                        case '4'   : $error = 'Не выбран файл для загрузки'; break;
                        case '6'   : $error = 'Отсутствует временная папка на сервере'; break;
                        case '7'   : $error = 'Ошибка записи на диск'; break;
                        case '8'   : $error = 'File upload stopped by extension'; break;
                        case '999' : default : $error = 'Неизвестная ошибка'; break;
                    }
                }

                if (empty($error))
                {
                    // Проверка на расширение файла
                    $allowed_ext = explode(',', $ext);
                    $foo = explode('.', $_FILES[$file]['name']);
                    $extension   = strtolower(end($foo));

                    if (in_array($extension, $allowed_ext))
                    {
                        // Если не указано конкретное имя, генерируем из исходного
                        if (empty($file_name))
                            $file_name = uniqid() . rand(0, 999) . '_' . self::toTranslit($_FILES[$file]['name']);
                        else
                            $file_name .= '.' . $extension;

                        $uploadFullPath = $folder . $file_name;

                        // Распарсиваем путь загрузки и создаём соответствующие папки если их нет
                        if (!is_dir($folder)) {
                            self::createDir($folder);
                        }
                        
                        if (move_uploaded_file($_FILES[$file]['tmp_name'], $uploadFullPath))
                        {
                            chmod($uploadFullPath, 0777);

                            $file_size = getimagesize($uploadFullPath);

                            // Проверка на совпадение с ограничениями по размерам
                            // если эти ограничения есть
                            if (!empty($minx) || !empty($miny) || !empty($maxx) || !empty($maxy))
                            {
                                // не совпадают оба размера (ограничения снизу)
                                if (!empty($minx) && !empty($miny) && $file_size[0] < $minx && $file_size[1] < $miny)
                                {
                                    $result['status']  = 'error';
                                    $result['message'] = "Мин. размер $minx x $miny px";
                                    unlink($uploadFullPath);
                                }
                                else
                                {
                                    if (!empty($minx) && $file_size[0] < $minx)
                                    {
                                        $result['status']  = 'error';
                                        $result['message'] = "Мин. ширина картинки $minx px";
                                        unlink($uploadFullPath);
                                    }

                                    if (!empty($miny) && $file_size[1] < $miny)
                                    {
                                        $result['status']  = 'error';
                                        $result['message'] = "Мин. высота картинки $miny px";
                                        unlink($uploadFullPath);
                                    }
                                }

                                // не совпадают оба размера (ограничения сверху)
                                if (!empty($maxx) && !empty($maxy) && $file_size[0] > $maxx && $file_size[1] > $maxy)
                                {
                                    $result['status']  = 'error';
                                    $result['message'] = "Макс. размер картинки $maxx x $maxy px";
                                    unlink($uploadFullPath);
                                }
                                else
                                {
                                    if (!empty($maxx) && $file_size[0] > $maxx)
                                    {
                                        $result['status']  = 'error';
                                        $result['message'] = "Макс. ширина картинки $maxx px";
                                        unlink($uploadFullPath);
                                    }

                                    if (!empty($maxy) && $file_size[1] > $maxy)
                                    {
                                        $result['status']  = 'error';
                                        $result['message'] = "Макс. высота картинки $maxy px";
                                        unlink($uploadFullPath);
                                    }
                                }

                                // не совпадают оба размера (строгие ограничения по обеим размерам)
                                if (!empty($minx) && !empty($miny) && !empty($maxx) && !empty($maxy) && $minx == $maxx && $miny == $maxy && $file_size[0] != $minx && $file_size[1] != $miny)
                                {
                                    $result['status']  = 'error';
                                    $result['message'] = "Размер строго $minx x $miny px";
                                    unlink($uploadFullPath);
                                }
                            }

                            if ($result['status'] != 'error')
                            {
                                $result['status'] = 'ok';
                                $result['path']   = $uploadFullPath;
                                $result['file']   = basename($uploadFullPath);
                                $result['sizes']  = $file_size;
                                $result['extension'] = $extension;
                                $result['size']   =  $_FILES[$file]['size'];
                            }
                        }
                        else
                        {
                            $result['status']  = 'error';
                            $result['message'] = 'При перемещении файла произошла ошибка';
                        }
                    }
                    else
                    {
                        $result['status']  = 'error';
                        $result['message'] = 'Недопустимый формат файла - ' . $extension . '. Допустимые: ' . $ext;
                    }
                }
                else
                {
                    $result['status']  = 'error';
                    $result['message'] = $error;
                }
            }
            else
            {
                $result['status']  = 'error';
                $result['message'] = 'Файл "' . $file . '" не выбран';
            }

            return $result;
        }
        
        /**
        * создать папки из пути
        */
       public static function createDir($path)
       {
           if (!is_dir($path))
           {
               $path = explode('/', $path);

               umask(0002);

               foreach($path as $f)
               {
                   if (!empty($f))
                   {
                       $ppath .= $f . '/';

                       if (!is_dir($ppath)) {
                         mkdir($ppath, 0775);  
                       } 
                   }
               }
           }
       }
    }