<?php

    namespace tomdom\core\services;

    use \tomdom\core\exception\serviceScriptException;

    /**
     * РђРЅР°Р»РёР·Р°С‚РѕСЂ Р»РѕРіРѕРІ СЃ С„РёРєСЃР°С†РёРµР№ РїСЂРѕСЃРјРѕС‚СЂРѕРІ РѕР±СЉРµРєС‚РѕРІ
     */
    abstract class viewsChecker {
        
        /**
         * @var string РїСѓС‚СЊ РґРѕ С„Р°Р№Р»Р° СЃ Р»РѕРіР°РјРё РїРѕСЃРµС‰РµРЅРёР№
         */
        protected $logfile = '';
        
        /**
         * @var string СЂРµРіСѓР»СЏСЂРЅРѕРµ РІС‹СЂР°Р¶РµРЅРёРµ РґР»СЏ РїРѕРёСЃРєР° СЃС‚СЂР°РЅРёС†
         */
        protected $pattern = '';
        
        /**
         * @var array РјР°СЃСЃРёРІ РґР°РЅРЅС‹РјРё РїРѕ РїРѕСЃРµС‰РµРЅРёСЏРјРё
         */
        protected $data = [];
        
        /**
         * @var array Р±СѓС„РµСЂ СЃ РґР°РЅРЅС‹РјРё РїРѕ РЅРѕСЃРёС‚РµР»СЏРј
         */
        protected static $styles = [];
        
        
        protected function __construct($file) {
            if (!is_file($file)) {
                throw new serviceScriptException('Р¤Р°Р№Р» СЃ Р»РѕРіР°РјРё РЅРµ РѕР±РЅР°СЂСѓР¶РµРЅ', 1);
            }
            
            $this->logfile = $file;
        }
        
        /**
         * РџСЂРѕР°РЅР°Р»РёР·РёСЂРѕРІР°С‚СЊ С„Р°Р№Р» Р»РѕРіРѕРІ 
         * Рё СЃС„РѕСЂРјРёСЂРѕРІР°С‚СЊ СѓРЅРёРІРµСЂСЃР°Р»СЊРЅС‹Р№ РјР°СЃСЃРёРІ РґР»СЏ СЃРѕС…СЂР°РЅРµРЅРёСЏ РІ Р±Р°Р·Сѓ РґР°РЅРЅС‹С…
         */
        public function parse()
        {
            if (!$this->pattern) {
                throw new serviceScriptException('Р РµРіСѓР»СЏСЂРЅРѕРµ РІС‹СЂР°Р¶РµРЅРёРµ РґР»СЏ РїРѕРёСЃРєР° РЅРµ Р·Р°РґР°РЅРѕ', 1);
            }
            
            $now = time();
            
            $visits = $this->log_parser(0, 100000, '', '', '', '', '/GET ' . $this->pattern . '/i');
            
            //printr($visits);
            
            foreach ($visits AS $v)
            {
                if (strtotime($v['time']) >= $now - 3600) 
                {
					$v['url'] = parse_url(trim(str_replace(['GET', 'HTTP/1.0', 'HTTP/1.1'], '', $v['request'])));
                    $v['url']['parts'] = explode('/', trim($v['url']['path'], '/'));

                    if (!$goods[$v['url']['parts'][2]][$v['ip']][$v['url']['path']]) {
                        $this->goods[$v['url']['parts'][2]][$v['ip']][$v['url']['path']] = $v['time'];
                    }
                }
            }
            
            return $this;
        }
		
		public function log_parser($time=3, $max=1000, $ip=false, $bot=false, $admin=false, $error=false, $search = null)
		{
			ini_set('memory_limit', '30048M');
			ini_set('max_execution_time', 0);
			
			if (is_file($this->logfile))
			{
				if($file=file_get_contents($this->logfile))
				{
					$admin_IP=$_SERVER['REMOTE_ADDR'];//IP Р°РґРјРёРЅР°
					$time=$time*3600;//СЂР°Р·РЅРёС†Р° С‡Р°СЃРѕРІ РІРѕ РІСЂРµРјРµРЅРё СЃ СЃРµСЂРІРµСЂРѕРј (UTC)
					$result=[];
					$file=preg_replace( "#\r\n|\r|\n#",PHP_EOL,$file);//СѓРЅРёС„РёРєР°С†РёСЏ РґРµР»РёС‚РµР»СЏ РґР»СЏ СЂР°Р·РЅС‹С… РћРЎ
					$file=explode(PHP_EOL,$file);
					$file=array_reverse($file);
		
					$max++;
					
					foreach($file as $i=>$val)
					{
						if($i==$max)
							break;
		
						if($val!=='')
						{
							preg_match_all('~"(.*?)(?:"|$)|([^"]+)~',$val,$m,PREG_SET_ORDER);
							$temp=[];
							$break=false;//РЅРµ Р±С‹Р»Рѕ РѕС‚РјРµРЅС‹ РїР°СЂСЃР° СЃС‚СЂРѕРєРё
							
							foreach($m as $ii=>$val2)
							{
								$val2[0]=trim($val2[0]);
								if ($val2[0]=='')
									continue;
								
								if($ii==0)//IP Рё РґР°С‚Р°
								{
									$temp2=explode(' - - ',$val2[0]);
									$temp2[0]=trim($temp2[0]);
									
									if(($ip) && $ip!==$temp2[0])
									{
										$max++;
										$break=true;
										break;
									}
									
									$temp['ip']= $temp2[0];
									$DATE=str_replace(['[',']'],'',$temp2[1]);
									$DATE=explode(':',$DATE);
									$temp['time']=date('Y-m-d H:i:s',strtotime(str_replace('/',' ',$DATE[0]).' '.$DATE[1].':'.$DATE[2].':'.$DATE[3])+$time);//РґР°С‚Р°+ time С‡Р°СЃРѕРІ
								}
								else
								{
									if($ii==1)//Р—Р°РїСЂРѕСЃ
									{
										if(!$admin && strpos($val2[0], 'index_admin.php')) {
											$max++;
											$break=true;
											break;
										}
										
										if( strstr($val2[0],'%'))
											$val2[0]=urldecode($val2[0]);
										
										$temp['request']=trim($val2[0],'"');
		
										if ($search && !preg_match($search, $val2[0])) {
											$max++;
											$break=true;
											break;
										}
									}
									else
									{
										if ($ii==2)//РљРѕРґ РѕС‚РІРµС‚Р°
										{
											$temp['code'] = (int) $val2[0];
											
											if($temp['code'] < 300)
											{
												if($error)//РёСЃРєР»СЋС‡Р°РµРј РїРѕРєР°Р·С‹ 2-XX
												{
													$max++;
													$break=true;
													break;
												}
											}
											else
											{
												if($temp['code'] < 400)
												{
													if($error)//РёСЃРєР»СЋС‡Р°РµРј РїРѕРєР°Р·С‹ 3-XX
													{
														$max++;
														$break=true;
														break;
													}
													
												}
											}
										}
										else
										{
											if($ii==5)//Р±СЂР°СѓР·РµСЂ
											{
												$val2[0]=trim($val2[0],'"');
												
												if(SpiderDetect($val2[0]))
												{
													if(!$bot)//РёСЃРєР»СЋС‡Р°РµРј РїРѕРєР°Р·С‹ Р±РѕС‚РѕРІ
													{
														$max++;
														$break=true;
														break;
													}
													$temp['bot']=true;
												}
												$temp['browser']=$val2[0];
											}
											else
											{
												//$temp['browser']=trim($val2[0],'"');
											}
										}
									}
								}
							}
		
							if(!$break) {
								$result[] = $temp;
							}
						}
						else 
							$max++;
					}
				}
				else 
					throw new Exception('Р¤Р°Р№Р» РЅРµ С‡РёС‚Р°РµС‚СЃСЏ РёР»Рё РїСѓСЃС‚', 1);
			}
			else 
				throw new Exception('РќРµ РЅР°Р№РґРµРЅ С„Р°Р№Р» Р»РѕРіРѕРІ', 2);
			
			return $result;
		}
        
		public function emptyLog() {
			if ($_SERVER['HTTP_HOST'] != 'tomdom.loc') {
				file_put_contents($this->logfile, '');
			}
		}
		
        public abstract function save();
    }
    