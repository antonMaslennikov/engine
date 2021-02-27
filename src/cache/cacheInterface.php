<?php 

	namespace tomdom\core\cache;
	
	interface cacheInterface
	{
		public function set($name, $var, $exp = null);
		public function add($name, $var, $exp = null);
		//public function get($name);
		public function getAllKeys();
	}
	