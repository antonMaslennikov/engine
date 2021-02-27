<?php

namespace tomdom\core;

/**
 * Управление информацией о странице
 */
class Page {

    const FLASH_ERROR = 'alertError';
    const FLASH_DANGER = 'alertDanger';
    const FLASH_WARNING = 'alertWarning';
    const FLASH_SUCCESS = 'alertSuccess';
    const FLASH_INFO = 'alertInfo';
    const FLASH_DEFAULT = 'default';

    public $flashList = [self::FLASH_ERROR, self::FLASH_DANGER, self::FLASH_WARNING, self::FLASH_SUCCESS, self::FLASH_ERROR, self::FLASH_DEFAULT];

    /**
     * @var string url запроса с образанными GET параметрами 
     */
    public $url;
    public $info = array();

    /**
     * @var array разобранный по / this->url 
     */
    public $reqUrl;

    /**
     * @var string подключаемый модуль
     */
    public $module;

    /**
     * @var string путь до файла с урлами страниц
     */
    public $pmfile = 'application/views/pagemeta.xml';

    /**
     * @var string page title for module (тайтл для всего модуля)
     */
    public $title = '';

    /**
     * @var string page title unique for this url (тайтл для разных вариантов модуля)
     */
    public $utitle = '';
    public $udescription = '';
    public $ogPAGE_TITLE;

    /**
     * @var string описание страницы для шаринга в соцсетях
     */
    public $ogPAGE_DESCRIPTION;

    /**
     * @var string ссылка на картинку для шаринга страницы в соцсетях
     */
    public $ogImage;
    public $ogUrl;
    public $seo = '';

    /**
     * Не индерксировать страницу
     */
    public $noindex = false;

    /**
     * @var string meta keywords
     */
    public $keywords = '';

    /**
     * @var string meta description
     */
    public $description = '';

    /**
     * @var string хлебная крошка
     */
    public $breadcrump = array();

    /**
     * @var string язык страницы
     */
    public $lang = 'ru';

    /**
     * @var string путь до папки с файлами переводов
     */
    protected $lang_folder = 'application/views/translation/';

    /**
     * @var array массив с перводами
     */
    public $translate = array();

    /**
     * @var singleton
     */
    private static $_app;

    /**
     * @var объект в который будем сваливать все подключаемые модули
     */
    private static $_imports = array();

    /**
     * @var импортируемые на страницу файлы javascript 
     */
    public $js = array();

    /**
     * @var импортируемые на страницу файлы css 
     */
    public $css = array();

    /**
     * @var 
     */
    public $isAjax = false;

    function __construct($url = null) {
        // разбираем урл на составляющие
        if (!$url = @parse_url($_SERVER['REQUEST_URI']))
            $url['path'] = $_SERVER['REQUEST_URI'];

        $this->REQUEST_URI = $_SERVER['REQUEST_URI'];
        $this->url = $url['path'];
        $this->reqUrl = explode('/', $url['path']);

        // удаляем пустые элементы
        foreach ($this->reqUrl as $k => $v) {
            if ($v === '') {
                if ($k == 0 || $k == count($this->reqUrl)) {
                    unset($this->reqUrl[$k]);
                } else {
                    if (strlen($this->url) > 1) {
                        $this->go('/404/', 301);
                    }
                }
            }
        }

        if (strpos($_SERVER['HTTP_HOST'], 'dev.') !== false) {
            $this->noindex = true;
        }

        // сбрасывам ключи массива
        $this->reqUrl = array_values($this->reqUrl);

        if (!empty($this->reqUrl[0])) {
            $this->module = $this->reqUrl[0];
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            $this->isAjax = true;
        }
    }

    function go($href, $v = 0) {
        if ($v == 301)
            header("HTTP/1.1 301 Moved Permanently");

        header('location: ' . $href);
        exit();
    }

    function refresh() {
        header('location: ' . ($_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '/'));
        exit();
    }
    
    function goback() {
        header('location: ' . ($_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '/'));
        exit();
    }

    /**
     * подключить на страницу внешний файл (javascript, css)
     */
    public function import($paths) {
        foreach ((array) $paths AS $path) {
            if (!file_exists(ROOTDIR . $path)) {
                
            }

            $file = pathinfo($path);

            if (in_array($file['extension'], array('js', 'css'))) {
                if (!in_array($path, $this->$file['extension'])) {
                    array_push($this->$file['extension'], $path);
                }
            }
        }
    }

    public function setFlashMessage($message) {
        $this->setFlashAlert('default', $message);
    }

    public function getFlashMessage() {
        return $this->getFlashAlert('default');
    }
    
    public function setFlashAlert($key, $message) {
        if (!empty($message)) {
            $_SESSION['flash-message'][$key] = $message;
        }
    }

    public function getFlashAlert($key) {
        if (!empty($_SESSION['flash-message'][$key]) && !$this->isAjax) {
            $flash = $_SESSION['flash-message'][$key];
            unset($_SESSION['flash-message'][$key]);
            return $flash;
        }

        return null;
    }

    public function setFlashError($message) {
        $this->setFlashAlert(self::FLASH_ERROR, $message);
    }
    
    public function getFlashError() {
        return $this->getFlashAlert(self::FLASH_ERROR);
    }

    public function setFlashDanger($message) {
        $this->setFlashAlert(self::FLASH_DANGER, $message);
    }

    public function setFlashWarning($message) {
        $this->setFlashAlert(self::FLASH_WARNING, $message);
    }

    public function setFlashSuccess($message) {
        $this->setFlashAlert(self::FLASH_SUCCESS, $message);
    }
    
    public function getFlashSuccess() {
        return $this->getFlashAlert(self::FLASH_SUCCESS);
    }

    public function setFlashInfo($message) {
        $this->setFlashAlert(self::FLASH_INFO, $message);
    }

    public function page404() {
        
    }

    /**
     * Добавить пункт в хлебные крошки
     */
    public function addBreadCrump($caption, $link = null) {
        if (!$caption) {
            return;
        }

        $this->breadcrump[] = array(
            'link' => $link,
            'caption' => $caption,
        );
    }

}
