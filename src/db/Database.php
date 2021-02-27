<?php

    namespace tomdom\core\db;

    use \PDO;

    class Database {

        private $db;

        public $debug = false;

        protected $dblog = [];

        public function __construct(\DB_Settings $dbsettings) {
            $this->db = new PDO("mysql:host=" . ($dbsettings->host) . ";dbname=" . $dbsettings->database, $dbsettings->user, $dbsettings->password, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", 
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

            if ($this->debug) {
                $this->log('db connected');
            }
        }

        public function query($query) {
            if ($this->debug) {
                $this->log('query', $query);
            }
            return $this->db->query($query);
        }

        public function prepare($query) {
            if ($this->debug) {
                $this->log('query', $query);
            }
            return $this->db->prepare($query);
        }

        public function lastInsertId() {
            return $this->db->lastInsertId();
        }

        protected function log($action, $query) {
            $this->dblog[] = [
                'time' => microtime(),
                'action' => $action,
                'query' => $query,
            ];
        }

        public function printLog()
        {
            printr($this->dblog);
        }
    }
