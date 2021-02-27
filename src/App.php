<?php

namespace tomdom\core;

use \PDO;
use \Routing\Router;
use \Routing\MatchedRoute;

class App
{
    public static $db;
    public static $dbsettings;
    public static $mail;
    public static $url;
    public static $sms;
    public static $page;
    public static $memcache;
    public static $user;

    private function __construct()
    {
    }

    public static function run(Routings $rounting)
    {
        $router = new Router(GET_HTTP_HOST());

        foreach ($rounting->get() as $k => $r) {
            if ($r['pattern'] && $r['action']) {
                $router->add($k, $r['pattern'], $r['action'], $r['schemas']);
            } else {
                throw new exception\appException('РќРµРєРѕСЂСЂРµРєС‚РЅРѕРµ РїСЂР°РІРёР»Рѕ СЂР°Р·РѕР±СЂР° url: ' . implode(' ', $r));
            }
        }

        $route = $router->match(GET_METHOD(), GET_PATH_INFO());

        if (null == $route) {
            $route = new MatchedRoute($rounting->classesBase . 'Controller_404:action_index');
        }

        list($class, $action) = explode(':', $route->getController(), 2);

        call_user_func_array(array(new $class($router), $action), $route->getParameters());
    }


    public static function db()
    {
        if (self::$db == NULL) {
            self::$dbsettings = new \DB_Settings;

            try {
                /*
                self::$db = new PDO("mysql:host=" . (self::$dbsettings->host) . ";dbname=" . self::$dbsettings->database, self::$dbsettings->user, self::$dbsettings->password, array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES cp1251",
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                */

                self::$db = new db\Database(self::$dbsettings);
            } catch (\PDOException $e) {
                self::page503($e);
            }
        }

        return self::$db;
    }

    public static function mail()
    {
        if (self::$mail == NULL) {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/system/phpmailer/class.phpmailer.php';

            self::$mail = new \PHPMailer();
            self::$mail->CharSet = 'UTF-8';
            self::$mail->IsSMTP(); // telling the class to use SMTP
            self::$mail->Host = "smtp.majordomo.ru"; // SMTP server
            self::$mail->SMTPDebug = 0;               // enables SMTP debug information (for testing)
            // 1 = errors and messages
            // 2 = messages only
            self::$mail->SMTPAuth = true;            // enable SMTP authentication
            self::$mail->Host = "smtp.majordomo.ru"; // sets the SMTP server
            self::$mail->Port = 25;               // set the SMTP port for the GMAIL server
            self::$mail->Username = "feed@tomdom.ru"; // SMTP account username
            self::$mail->Password = "y35w5rdapnewlw"; // SMTP account password
        }

        return self::$mail;
    }

    public static function sms()
    {
        if (self::$sms == null) {
            self::$sms = new \sms(SMSuser, SMSpassword, SMSsender);
        }

        return self::$sms;
    }

    public static function memcache()
    {
        if (self::$memcache == null) {

            //self::$memcache = new cache\memcached;
            if (class_exists('\Memcached'))
                self::$memcache = new \Memcached();
            else
                self::$memcache = new cache\memcache;

            //self::$memcache->pconnect('unix:///tmp/memcached.sock',0);
            self::$memcache->addServer('localhost', 11211);
        }

        return self::$memcache;
    }

    public static function url()
    {
        if (self::$url == NULL) {
            if (!$url = @parse_url($_SERVER['REQUEST_URI']))
                $url['path'] = $_SERVER['REQUEST_URI'];

            self::$url = new stdClass;

            foreach (array_slice(explode('/', $url['path']), 1) as $k => $v) {
                self::$url->{$k} = $v;
            }
        }

        return self::$url;
    }

    public static function page()
    {
        if (self::$page == NULL) {
            self::$page = new Page();
        }

        return self::$page;
    }

    public static function user()
    {
        if (self::$user == null) {
            self::$user = new User();
        }

        return self::$user;
    }


    /**
     * РџРѕРєР°Р· СЃС‚СЂР°РЅРёС†С‹-Р·Р°РіР»СѓС€РєРё "СЃР°Р№С‚ РЅРµ РґРѕСЃС‚СѓРїРµРЅ"
     * @param Exception $e РёСЃРєР»СЋС‡РµРЅРёРµ, РєРѕС‚РѕСЂРѕРµ РїСЂРёРІРµР»Рѕ Рє РЅРµРґРѕСЃС‚СѓРїРЅРѕСЃС‚Рё СЃР°Р№С‚Р°
     */
    public static function page503(\Exception $e = null)
    {
        /*
        $router = new Router(GET_HTTP_HOST());
        $route = new MatchedRoute('Controller_503:action_index', ['e' => $e]);

        list($class, $action) = explode(':', $route->getController(), 2);

        $class = 'application\controllers\\' . $class;

        call_user_func_array(array(new $class($router), $action), $route->getParameters());
        */
    }

    /**
     * РџРѕРєР°Р· СЃС‚СЂР°РЅРёС†С‹-Р·Р°РіР»СѓС€РєРё "СЃС‚СЂР°РЅРёС†Р° РЅРµ РЅР°Р№РґРµРЅР°"
     */
    public static function page404()
    {
        $router = new Router(GET_HTTP_HOST());
        $route = new MatchedRoute('Controller_404:action_index');

        list($class, $action) = explode(':', $route->getController(), 2);

        $class = 'application\controllers\\' . $class;

        call_user_func_array(array(new $class($router), $action), $route->getParameters());
    }
}
