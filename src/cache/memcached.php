<?php
	
	namespace tomdom\core\cache;
	
	class memcached Extends \Memcached implements cacheInterface
	{
		public function set($name, $var, $exp = null, $udf_flags = null)
		{
			return parent::set($name, $var, $exp, $udf_flags);
		}
		
		public function add($name, $var, $exp = null, $udf_flags = null)
		{
			return parent::add($name, $var, $exp, $udf_flags);
		}
		
		public function get($key, $cache_cb = null, &$cas_token = null, &$udf_flags = null)
		{
			return parent::get($key, $cache_cb, $cas_token, $udf_flags);
		}
		
		public function getAllKeys()
		{
			return parent::getAllKeys();
		}
	}