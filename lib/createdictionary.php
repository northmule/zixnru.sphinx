<?php

namespace Zixnru\Sphinx;

use B2Motor\Init\Helpers;
use Bitrix\Main\Text\Encoding;

/**
 * Создаёт словари синонимов для Sphinx
 *
 * @author djo
 */
class CreateDictionary
{
    
    protected static $dic_parts_name = 'parts_name.txt';
    
    /**
     * Словарь синонимов названий автозапчастей
     */
    public static function create_parts_name(string $patch_folder): void
    {
        
        $file = $patch_folder . '/' . self::$dic_parts_name;
        
        if (file_put_contents($file, '') === false) {
            return;
        }
        
        $buffer = '';
        
        $dbQuery = SynonymsPartsNameTable::getlist([
            'select' => ['SYNONYM', 'TARGET'],
            'order'  => ['SYNONYM' => 'ASC'],
        ]);
        
        while ($tmp = $dbQuery->fetch()) {
            
            if (empty(trim($tmp['SYNONYM'])) || empty(trim($tmp['TARGET']))) {
                continue;
            }
            
            if (Helpers::isWindowsCharsetSite()) {
                $tmp = Encoding::convertEncoding($tmp, SITE_CHARSET, 'UTF-8');
            }
            
            $buffer .= "~{$tmp['SYNONYM']}=>{$tmp['TARGET']}" . PHP_EOL;
        }
        
        $buffer = trim($buffer, PHP_EOL);
        
        if (!empty($buffer)) {
            file_put_contents($file, $buffer);
        }
        
        $sphinx = Eorder::getInstance();
        
        $sphinx_index = $sphinx->getIndexName();
        
        $pdo = $sphinx->getConnection();
        
        $pdo->query("ALTER RTINDEX {$sphinx_index} RECONFIGURE");
        
        return;
    }
    
}
