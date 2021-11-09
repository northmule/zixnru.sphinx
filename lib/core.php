<?php

namespace Zixnru\Sphinx;

use Bitrix\Main\Loader;
use Exception;
use PDO;
use PDOException;
use Zixnru\Logger\Log;

class Core
{
    
    /**
     * Параметры подключения к Sphinx
     *
     * @var type
     */
    protected $connection_string = 'mysql:host=127.0.0.1;port=9306;';
    
    /*
     * Ресурс подключения
     * 
     */
    protected $connection = null;
    
    /**
     * Имя индекса
     */
    protected $index_name = '';
    
    /**
     * Выводить исключения или нет
     */
    protected $debug = false;
    
    public function __construct()
    {
        if (is_null($this->connection)) {
            $this->connectService();
        }
    }
    
    public function getIndexName()
    {
        return $this->index_name;
    }
    
    /**
     *
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Выполнит не подготовленный произвольный запрос без проверки данных
     *
     * @param string $query_string
     *
     * @return array
     */
    public function query(string $query_string): array
    {
        
        if (is_null($this->connection)) {
            return [];
        }
        
        $result = $this->connection->query($query_string);
        
        if ($result === false) {
            return [];
        }
        
        return (array)$result;
    }
    
    public function setLog($message): void
    {
        if (Loader::includeModule('zixnru.logger')
            and class_exists('\Zixnru\Logger\Log')
        ) {
            Log::set('zxinru_sphinx', [
                'file'    => __FILE__,
                'time'    => date('d.m.Y H:i:s'),
                'message' => $message,
                'request' => $_REQUEST
            ]);
        }
    }
    
    /**
     * Подключение к сервису
     *
     * @return bool
     * @throws Exception
     */
    protected function connectService(): bool
    {
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,
        ];
        try {
            
            $this->connection = new PDO(
                $this->connection_string, null, null, $opt
            );
        } catch (PDOException $ex) {
            if ($this->debug) {
                throw new Exception(
                    'Ошибка подключения: ' . $ex->getMessage()
                );
            } else {
                $this->connection = null;
                
                $this->setLog($ex->getMessage());
                
                return false;
            }
        }
        
        return true;
    }
    
}
