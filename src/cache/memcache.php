<?php
	
	namespace tomdom\core\cache;
	
	class memcache Extends \Memcache implements cacheInterface
	{
		public function set($name, $var, $exp = null)
		{
			return parent::set($name, $var, false, $exp);
		}
		
		public function add($name, $var, $exp = null)
		{
			return parent::add($name, $var, false, $exp);
		}
		
		public function get($name, &$var2 = null, &$var3 = null)
		{
			return parent::get($name, $var2, $var3);
		}
		
		public function getAllKeys()
		{
			$keys = [];
			
			$slabs = parent::getExtendedStats('slabs');
			
			foreach ($slabs as $serverSlabs) 
			{
				if (count($serverSlabs) > 0)
				{
					foreach ((array) $serverSlabs as $slabId => $slabMeta) 
					{
						try {
							$cacheDump = parent::getExtendedStats('cachedump', (int) $slabId, 1000);
						} catch (Exception $e) {
							continue;
						}
						
						foreach ($cacheDump as $dump) {

							if (!is_array($dump)) {
								continue;
							}
			
							foreach ($dump as $key => $value) {
								//if (strpos($key, 'visitor_') !== false) {
									$keys[] = $key;
								//}
							}
						}
					}
				}
			}
			
			return $keys;
		}
	}