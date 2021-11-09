<?php

namespace Zixnru\Sphinx;

use Bitrix\Main\Text\Encoding;
use Exception;

/**
 * Встроенный в Bitrix
 */
class Native extends Core
{
    
    protected static $_instance = null;
    
    /**
     * Наименование индекса sphinx из файла sphinx.conf сервера
     *
     * @var type
     */
    protected $index_name = 'bitrix_native';
    
    function getIndex_name(): type
    {
        return $this->index_name;
    }
    
    /**
     * Перестройка индекса
     */
    public function Reconfigure(): void
    {
        
        $pdo = parent::getConnection();
        
        if (is_null($pdo)) {
            return;
        }
        
        $pdo->query("ALTER RTINDEX {$this->index_name} RECONFIGURE");
    }
    
    /**
     * Singletone
     *
     * @return Native
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
