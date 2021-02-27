<?php
    
    namespace tomdom\core;

    abstract class Routings {
        
        /**
         * @var Р±Р°Р·РѕРІРѕРµ РїСЂРѕСЃС‚СЂР°РЅСЃС‚РІРѕ РёРјС‘РЅ (С‡С‚РѕР±С‹ РЅРµ РїРµСЂРµРїРёСЃС‹РІР°С‚СЊ РєР°Р¶РґС‹Р№ СЂР°Р· РІ РїСЂР°РІРёР»Р°С…)
         */
        var $classesBase = 'application';
        
        /**
         * @var РјР°СЃСЃРёРІ СЃ РїСЂР°РІРёР»Р°РјРё СЂР°Р·Р±РѕСЂР° СѓСЂР»Р°
         */
        var $data;
        
        public function __construct($base = null) {
            if ($base) {
                $this->classesBase = $base;
            }
        }
        
        /**
         * РїРѕР»СѓС‡РёС‚СЊ РіРѕС‚РѕРІС‹Р№ РјР°СЃСЃРёРІ СЃ РїСЂР°РІРёР»Р°РјРё
         */
        public function get() {
            
            foreach ($this->data AS $k => $row) {
                if ($row['action']) {
                    $this->data[$k]['action'] = $this->classesBase . $row['action'];
                }
            }
            
            return $this->data;
        }
    }