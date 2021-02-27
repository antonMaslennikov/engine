<?php

namespace tomdom\core;

use \Exception;
use \tomdom\core\exception\appException;
use \PDO;

class Model {

    public $id = 0;
    public $info = array();
    protected static $dbtable;
    protected static $db_table;
    protected $_initial_data = [];
    protected $modified_data = [];

    function __construct($id = null) {
        $this->getDbTableName();

        if ($id) {
            $this->id = (int) $id;
        }

        if (!empty($this->id)) {
            $r = App::db()->prepare("SELECT * FROM `" . self::db() . "` WHERE `id` = ? LIMIT 1");

            $r->execute([$this->id]);

            if ($r->rowCount() == 1) {
                $this->info = $r->fetch();

                $this->_initial_data = $this->info;

                return $this->info;
            } else
                throw new appException(__CLASS__ . ' ' . $this->id . ' not founded');
        }
    }

    public function update() {

        if (count($this->modified_data)) {

            $update_query = [];
            $update_array = [':id' => (int) $this->id];

            foreach ($this->modified_data as $key => $function_cast) {

                if ($this->info[$key] != $this->_initial_data[$key]) {

                    $update_query[] = sprintf("`%s` = :%s", $key, $key);
                    $update_array[':' . $key] = $function_cast ? call_user_func($function_cast, $this->info[$key]) : $this->info[$key];
                }
            }

            if (count($update_query)) {

                $sql = 'update ' . self::db() . ' set ' . implode(', ', $update_query) . ' where id = :id limit 1;';

                // printr($sql);
                // printr($update_array, 1);

                $stmt = App::db()->prepare($sql);

                return $stmt->execute($update_array);
            }

            return true;
        }

        return false;
    }

    public function delete() {

        $sql = 'delete from ' . self::db() . ' where id = :id limit 1;';

        $stmt = App::db()->prepare($sql);

        return $stmt->execute([':id' => (int) $this->id]);
    }

    static function getDbTableName() {

        foreach (get_class_vars(get_called_class()) AS $k => $v) {
            if ($k == 'dbtable') {
                self::$dbtable = $v;
            }
        }

        return self::$dbtable;
    }

    static function db() {
        $class = get_called_class();
        return $class::$dbtable;
    }

    public function __set($name, $value) {
        $this->info[$name] = $value;
    }

    public function __get($name) {
        if (array_key_exists($name, $this->info)) {
            return $this->info[$name];
        }

        $getter = 'get' . $name;

        if (!method_exists($this, $getter)) {
            
        } else {
            return $this->$getter();
        }
    }

    public function __isset($name) {
        return isset($this->info[$name]);
    }

    public function setAttributes(array $attr) {
        $reflection = new \ReflectionClass(get_called_class());

        foreach ($attr as $k => $v) {
            if ($reflection->hasProperty($k) && $k != 'info') {

                $this->$k = $v;
            }

            $this->info[$k] = $v;
        }
    }

    /**
     * Сохранить экземляр класса в базу
     */
    public function save() {
        // ищем у пронаследовавшей модели свойство dbtable, содержащее имя таблицы
        foreach (get_class_vars(get_called_class()) AS $k => $v) {
            if ($k == 'dbtable') {
                $dbtable = $v;
            }
        }

        if (!$dbtable) {
            throw new Exception('Не известна таблица для сохранения данных', 1);
        }

        foreach ($this->info as $k => $v) {
            $rows[$k] = "`$k` = :{$k}";
        }

        // вырезаем все поля которых нет в схеме таблицы
        $r = App::db()->query(sprintf("SHOW COLUMNS FROM `%s`", $dbtable));

        foreach ($r->fetchAll() AS $f) {
            $fields[$f['Field']] = $f['Field'];
        }

        $rows = array_intersect_key($rows, $fields);
        // end вырезаем все поля которых нет в схеме таблицы

        unset($rows['id']);

        // редактирование
        if (!empty($this->id)) {
            $sth = App::db()->prepare("UPDATE `" . $dbtable . "` SET " . implode(', ', $rows) . " WHERE `id` = :primary_key LIMIT 1");
            $sth->bindValue(':primary_key', $this->id, PDO::PARAM_INT);
            // новая запись
        } else {
            $sth = App::db()->prepare("INSERT INTO `" . $dbtable . "` SET " . implode(',', $rows));
        }

        foreach ($rows AS $k => $v) {
            $sth->bindValue(":{$k}", (strip_tags($this->{$k}, '<a><div><p><b><img><i><strong><hr><br><s><h1><h2><h3><h4><h5><ul><li><span>')));
        }

        $sth->execute();

        if (!empty($this->id)) {
            $this->isNew = false;
        } else {
            $this->isNew = true;
            $this->id = App::db()->lastInsertId();
        }
    }

    /**
     * Найти экзепляр класса по айди, не получив исключения если он не найден
     * @param type $id
     * @return \self
     */
    public static function findById($id) {
        if (empty($id)) {
            return false;
        }
        try
        {
            $reflection = new \ReflectionClass(get_called_class());
            return $reflection->newInstance($id);;
        } catch (appException $ex) {
        }
    }
}