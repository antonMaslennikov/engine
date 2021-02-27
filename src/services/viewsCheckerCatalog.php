<?php

    namespace tomdom\core\services;

    use \tomdom\core\exception\serviceScriptException;
    
    class viewsCheckerCatalog extends viewsChecker 
    {
        function __construct($file) {
                
            parent::__construct($file);
            
            $this->pattern = '\/catalog\/.*\/.*\.html.*';
        }
        
        public function save()
        {
            if (count($this->goods) == 0) {
                return;
            }
			
            $sth1 = \tomdom\core\App::db()->prepare("INSERT INTO `product__visits`
                                       SET
                                            `ip`         = :ip,
                                            `product_id` = :gid,
                                            `date`       = :date");
											
			$sth2 = \tomdom\core\App::db()->prepare("SELECT `id` FROM `product` WHERE `url` = ? LIMIT 1");
           
            foreach ($this->goods as $good => $ips) 
            {
				$sth2->execute([str_replace(['.html'], '', $good)]);
				
				if ($pr = $sth2->fetch())
				{
					foreach ($ips as $ip => $pages) 
					{
						foreach ($pages as $page => $time) 
						{
							$foo = explode('/', trim($page, '/'));
							
							$sth1->execute([
								'ip' => ip2long($ip),
								'gid' => $pr['id'],
								'date' => $time,
							]);
						}
					}
				}
            }
			
			return $this;
        }
    }