<?php

namespace Zixnru\Sphinx;

use B2Motor\Init\Helpers;
use Bitrix\Main\Text\Encoding;
use Exception;
use PDO;

/**
 * Запросы из Eorder
 */
class Eorder extends Core
{
    
    protected static $_instance = null;
    
    /**
     * Наименование индекса sphinx из файла sphinx.conf сервера
     *
     * @var type
     */
    protected $index_name = 'rt_bitrix_eorder';
    
    function getIndex_name(): type
    {
        return $this->index_name;
    }
    
    /**
     * Лимит результатов
     */
    protected $default_limit = 50000;
    
    /**
     * Поиск по строке eOrder "всё"
     * Вернёт строку ид-шников через ,
     *
     * @param type $param
     *
     * @return string
     */
    public function search_all($search_string): string
    {
        
        $pdo = parent::getConnection();
        
        static $counter_execute = 0;// Количество вызово рукурсии
        
        if (is_null($pdo)) {
            return '-1';
        }
        
        if (Helpers::isWindowsCharsetSite()) {
            
            $search_string = Encoding::convertEncoding(
                $search_string, SITE_CHARSET, 'UTF-8'
            );
        }
        
        $sql
            = "select * from {$this->index_name} where match (:searche_text) limit {$this->default_limit}";
        
        $result = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]
        );
        
        $result->execute([
            ':searche_text' => $search_string,
        ]);
        
        $ids = '';
        
        foreach ($result->fetchAll() as $row) {
            
            $ids .= "{$row['element_id']},";
        }
        
        $ids = trim($ids, ',');
        
        if (strlen($ids) < 1) {
            $ids = '-1';
        }
        
        if ($ids == '-1'
            && $counter_execute < 2
        ) {//рекурсия и поиск без пробелов
            $counter_execute++;
            $search_string = str_replace(' ', '', $search_string);
            $ids = $this->search_all($search_string);
        }
        
        
        return $ids;
    }
    
    /**
     * Singletone
     *
     * @return Eorder
     */
    public static function getInstance()
    {
        
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    
    public function __clone()
    {
        throw new Exception('Forbiden instance __clone');
    }
    
    public function __wakeup()
    {
        throw new Exception('Forbiden instance __wakeup');
    }
    
    public function __construct()
    {
        parent::__construct();
    }
    
}
