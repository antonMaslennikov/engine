<?php

namespace tomdom\core\services;

use \tomdom\core\exception\serviceScriptException;

class viewsCheckerAdvices extends viewsChecker {

    function __construct($file) {

        parent::__construct($file);

        $this->pattern = '\/catalog\/advices\/.*\/';
    }

    public function save() {
        if (count($this->goods) == 0) {
            return;
        }

        $sth1 = \tomdom\core\App::db()->prepare("UPDATE `seo_pages` SET `visits` = `visits` + :v WHERE `id` = :id LIMIT 1");

        $sth2 = \tomdom\core\App::db()->prepare("SELECT `id` FROM `seo_pages` WHERE `url` = ? LIMIT 1");

        foreach ($this->goods as $good => $ips) {
            $sth2->execute([$good]);

            if ($pr = $sth2->fetch()) {
                $sth1->execute([
                    'v' => count($ips),
                    'id' => $pr['id'],
                ]);
            }
        }

        return $this;
    }

}
