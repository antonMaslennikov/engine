<?php

	//namespace carrotquest;

	use \PDO;
	
	class carrotquestApi
	{
		/**
		 * @var string класс настройками подключения к бд
		 */
		protected $dbsettings;
		
		/**
		 * @var string путь до файла с данными состояния
		 */
		protected static $metadatafile = __DIR__ . '/data.txt';
		
		/**
		 * @var string массив в данными состояния
		 */
		protected static $data;
		
		/**
		 * @var string клюк авторизации
		 */
		protected static $token = 'app.12409.8b5deb8f024312d5a856a2be59417eb79fe5bfa6581edeec';
		
		/**
		 * @var string url api
		 */
		protected static $apiUrl = 'https://api.carrotquest.io/v1';
		
		public function __construct(DB_Settings $settings) {
			$this->dbsettings = $settings;
		}
		
		/**
		 * Получить "следующего" менеджера для закрепления за диалогом
		 */
		public function getManager()
		{
			self::getMetaData();
			
			$event = json_decode($_POST['event']);
			
			$conversationId = $event->{'props'}->{'$conversation_id'}; // Из свойства события достаем conversationId
		    
			$db = new PDO("mysql:host=" . ($this->dbsettings->host) . ";dbname=" . $this->dbsettings->database, $this->dbsettings->user, $this->dbsettings->password, array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", 
                        PDO::ATTR_PERSISTENT => true,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			
			// вычисляем id оператора которому мы хотим назначить диалог
			$sth = 
				$db->prepare("SELECT `carrotquest_id`
							  FROM `system_admin`
							  WHERE `carrotquest_id` > '0' AND `active` = '1' AND `last_login` >= ?
							  ORDER BY `id`");
			$sth->execute([date('Y-m-d 05:00:00')]);
			$operators = $sth->fetchAll();
			
			foreach ($operators AS $k => $o) {
				if (self::$data->lastOperatorId == $o['carrotquest_id']) {
					$operator = $operators[$k == count($operators) - 1 ? 0 : $k + 1]; 
					break;
				}
				
				if (!self::$data->lastOperatorId || ($k == count($operators) - 1  && !$operator)) {
					$operator = $o; 
					break;
				}
			}
			
		    $url = self::$apiUrl . '/conversations/'.$conversationId.'/assign?auth_token='.self::$token;
			
			$result = file_get_contents($url, false, stream_context_create(array(
				'http' => array(
				  'method'  => 'POST',
				  'header'  => 'Content-type: application/json',
				  'content' => '{"admin": "'.$operator['carrotquest_id'].'"}'
				)
			)));
			
			self::setMetaData('lastOperatorId', $operator['carrotquest_id']);
			
			//exit((string) $operator['carrotquest_id']);
		}
		
		protected static function setMetaData($k, $v) {
			
			if (empty($k) || empty($v)) {
				return false;
			}
			
			if (!self::$data) {
				self::$data = self::getMetaData();
			}
			
			self::$data->$k = $v;
			
			$f = fopen(self::$metadatafile, 'w+');
			fwrite($f, json_encode(self::$data));
			fclose($f);
		}
		
		protected static function getMetaData() {
			self::$data = json_decode(file_get_contents(self::$metadatafile));
			
			if (!self::$data) {
				return new stdClass;
			}
		}
	}