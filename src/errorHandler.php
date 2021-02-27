<?php
    namespace tomdom\core;

    /**
     * Обработчик ошибок и исключений PHP
     */
    class errorHandler {

        protected $log_path = __DIR__ . '/erors_log.txt';

        public function register() {

            ini_set('display_errors', true);

            set_error_handler([$this, 'errorHandler'], E_ALL & ~E_NOTICE);
            register_shutdown_function([$this, 'fatalErrorHandler']);
            set_exception_handler([$this, 'exceptionHandler']);
        }

        /**
         * метод обработчик ошибки
         * @param int $errno номер ошибки
         * @param string $errstr текст ошибки
         * @param string $errfile файл содержащий ошибку
         * @param int $errline номер строки
         */
        public function errorHandler($errno, $errstr, $errfile, $errline)
        {
            // игнорируем ворнинги и нотисы
            if (E_WARNING == $errno || E_NOTICE == $errno || E_DEPRECATED == $errno)
                return;

            $this->showError($errno, $errstr, $errfile, $errline);

            // если не вернуть false штатный обработчик ошибок не будет обрабатывать ошибку
            //return false;
        }

        /**
         * Метод для перехвата фатальных ошибок
         */
        public function fatalErrorHandler()
        {
            $error = error_get_last();

            if (in_array($error['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR])) {
                ob_get_clean();
                $this->showError($error['type'], $error['message'], $error['file'], $error['line']);
            }

            // если не вернуть false штатный обработчик ошибок не будет обрабатывать ошибку
            //return false;
        }

        /**
         * Метод для перехвата необработанных исключений
         */
        public function exceptionHandler(\Throwable $e)
        {
            $this->showError(get_class($e) . ' (code: ' . $e->getCode() . ')', $e->getMessage() . '<hr>' . $e->getTraceAsString() . '<hr>' . json_encode($e->getTrace()), $e->getFile(), $e->getLine());
            // если не вернуть false штатный обработчик ошибок не будет обрабатывать ошибку
            //return false;
        }

        /**
         * Отобразить ошибку
         */
        public function showError($errno, $errstr, $errfile, $errline, $status = 500)
        {
            // статус вернуть не получится так как смарти к этому моменту уэе начнёт вывод
            //header('HTTP/1.1 ' . $status);

            if (appMode == 'dev' || strpos($_SERVER['HTTP_HOST'], '.loc') != false) {
                print_r('<div style="border:1px solid #ccc;margin-bottom:20px;padding:10px;">Catch error: ' . $errstr . '<hr> Номер ошибки: ' . $errno . '<hr> Файл: ' . $errfile . ' (строка ' . $errline . ')' . "</div>");
            } else {

                if (file_get_contents($this->log_path) != md5($errno)) {
                    $message  = '<div style="border:1px solid #ccc;margin-bottom:20px;padding:10px;">';
                    $message .= 'Catch error: ' . $errstr . '<hr> Номер ошибки: ' . $errno
                             . '<hr> Файл: ' . $errfile . ' (строка ' . $errline . ')'
                             . '<hr>Адрес: ' . $_SERVER['REQUEST_URI']
                             . '<hr>Реферер: ' . $_SERVER['HTTP_REFERER']
                         . '</div>';


                    require_once $_SERVER['DOCUMENT_ROOT'] . '/system/phpmailer/class.phpmailer.php';

                    $mail             = new \PHPMailer();

                    $mail->CharSet    = 'UTF-8';
                    $body             = '';
                    $mail->IsSMTP(); // telling the class to use SMTP
                    $mail->Host       = "smtp.majordomo.ru"; // SMTP server
                    $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
                                                               // 1 = errors and messages
                                                               // 2 = messages only
                    $mail->SMTPAuth   = true;                  // enable SMTP authentication
                    $mail->Host       = "smtp.majordomo.ru"; // sets the SMTP server
                    //$mail->SMTPSecure = "ssl";
                    $mail->Port       = 25;                    // set the SMTP port for the GMAIL server
                    $mail->Username   = "zakaz@tomdom.ru"; // SMTP account username
                    $mail->Password   = "7YbBn5Hy7JDfQUUD";        // SMTP account password

                    $mail->SetFrom('zakaz@tomdom.ru', 'TomDom.ru');
                    $mail->Subject = 'Критическая ошибка на сайте ' . $_SERVER['HTTP_HOST'];
                    $mail->MsgHTML($message);

                    $mail->AddAddress('anton.maslennikov@gmail.com');
                    $mail->AddAddress('svpnet@gmail.com');
                    $mail->AddAddress('v@tomdom.ru');

                    if(!$mail->Send()) {

                    }

                    //print_r('Показать 403ю наверное если фатал еррор');

                    $f = fopen($this->log_path, 'c+');
                    fwrite($f, md5($errno));
                    fclose($f);
                }
            }
        }
    }
