<?php

namespace Zixnru\Sphinx;

use B2Motor\Init\Helpers;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\Encoding;
use CIBlockElement;

Loader::includeModule('zixnru.sphinx');
/**
 * ПОдсказаньки
 * Для перестройки индекса ALTER RTINDEX rt_bitrix_eorder RECONFIGURE;
 */

/**
 * Пересобирает индексы для Eorder поиск товаров
 */
class CronIndexEorder
{
    
    /**
     * Полная или частичная пересборка индекса
     *
     *
     * @param boolean $full_reindex - true - обновит промежуточную таблицу
     *
     * @global type   $DB
     */
    public static function rt_index($full_reindex = true)
    {
        
        global $DB;
        
        $sphinx = Eorder::getInstance();
        
        $bx_index_name = IndexEorderTable::getTableName();
        
        $sphinx_index = $sphinx->getIndexName();
        
        //$sphinx_index = 'rt_bitrix_eorder2';
        
        $pdo = $sphinx->getConnection();
        
        
        if ($full_reindex) {
            
            $DB->query(
                "TRUNCATE {$bx_index_name}", false, __FILE__ . ":" . __LINE__
            );
            
            // self::set_index_table_sql();
            
            self::set_index_table_nativ();
        }
//
//Запрос к промежуточной таблице
        $query_string = <<<EOT
                SELECT * FROM {$bx_index_name}
EOT;
        
        $dbQuery = $DB->query($query_string);
        
        $primary_key = 1;
        
        $pdo->query("TRUNCATE RTINDEX {$sphinx_index}");
        
        $convert_char = null;
        
        if (Helpers::isWindowsCharsetSite()) {
            $convert_char = true;
        }
        
        while ($tmp = $dbQuery->fetch()) {
            
            if ($convert_char) {
                $tmp = Encoding::convertEncoding($tmp, SITE_CHARSET, 'UTF-8');
            }
            
            $query_sphinx = <<<EOT
                    INSERT INTO `{$sphinx_index}` 
                        (`id`, `element_id`, `xml_id`, `prop`) 
                            VALUES 
                        (:primary_key,:element_id, :xml_id,:prop)
EOT;
            
            $prepare_query = $pdo->prepare($query_sphinx);
            
            $prepare_query->execute([
                ':primary_key' => $primary_key,
                ':element_id'  => $tmp['ELEMENT_ID'],
                ':xml_id'      => $tmp['XML_ID'],
                //':name' => $tmp['NAME'],
                ':prop'        => $tmp['PROP'],
            ]);
            
            $primary_key++;
        }
    }
    
    /**
     * Промежуточный индекс через api Bitrix
     * Выберет данные из инфоблока и заполнит промежуточную таблицу без очистки, только INSERT
     */
    public static function set_index_table_nativ(): void
    {
        
        global $DB;
        
        Loader::includeModule('iblock');
        
        $arSelect = ["ID", "IBLOCK_ID", "NAME", "XML_ID", "PROPERTY_*"];
        
        $arFilter = ["IBLOCK_ID" => 4, "ACTIVE" => "Y"];
        
        $dbQuery = CIBlockElement::GetList(
            [], $arFilter, false, false, $arSelect
        );
        
        $replace = [
            ',',
            '\\',
            '"',
            '\'',
            '`',
            ':',
            '\'',
            ',',
            '<',
            '>',
            '(',
            ')',
            '[',
            ']',
            '*',
            '+',
            '=',
            '&',
            '?',
            '$',
            '#',
            '@'
        ];
        
        $fields = [];
        
        // $primary_key = 1;
        
        while ($ob = $dbQuery->GetNextElement()) {
            
            $string_key = '';
            
            $arFields = $ob->GetFields();
            
            $string_key .= "{$arFields['NAME']} {$arFields['XML_ID']} ";
            
            $props = $ob->GetProperties();
            
            foreach ($props as $values) {
                
                $clear = trim($values['VALUE']);
                
                if (empty($clear)) {
                    continue;
                }
                
                if (strlen($clear) == 1) {
                    continue;
                }
                
                $string_key .= " {$clear} ";
            }
            $string_key = str_replace($replace, ' ', $string_key);
            
            $string_key = str_replace('  ', ' ', $string_key);
            
            $string_key = ToLower($string_key);
            
            $fields[] = [
                //    'PRIMARY_KEY' => $primary_key,
                'ELEMENT_ID' => $arFields['ID'],
                'XML_ID'     => $arFields['XML_ID'],
                'NAME'       => $arFields['NAME'],
                'IBLOCK_ID'  => 4,
                'PROP'       => $string_key,
            ];
            
            // $primary_key++;
        }
        
        $field_chunk = array_chunk($fields, 1000);
        
        $bx_index_name = IndexEorderTable::getTableName();
        
        foreach ($field_chunk as $chunks) {
            
            $insert_value = (function () use ($chunks, $DB): string {
                
                $result = '';
                foreach ($chunks as $data) {
                    $result .= "({$DB->ForSql($data['ELEMENT_ID'])},"
                        . "'{$DB->ForSql($data['XML_ID'])}',"
                        . "'{$DB->ForSql($data['NAME'])}',"
                        . "{$DB->ForSql($data['IBLOCK_ID'])},"
                        . "'{$DB->ForSql($data['PROP'])}'),";
                }
                
                $result = trim($result, ',');
                
                return $result;
            })();
            
            $DB->query(
                "INSERT INTO {$bx_index_name} (`element_id`,`xml_id`,`name`,`iblock_id`,`prop`) VALUES {$insert_value}"
            );
            //die();
        }
        
        return;
    }
    
    /**
     * Промежуточный индекс через запрос sql к инфоблокам Битрикс
     * Есть не точности
     */
    protected static function set_index_table_sql()
    {
        
        $bx_index_name = IndexEorderTable::getTableName();
        
        $query_string = <<<EOT
            
                      INSERT INTO {$bx_index_name} (
                     `element_id`,
                     `xml_id`,
                     `name`,
                     `iblock_id`,
                     `prop`)  
                     SELECT 
                     element.ID AS product_id,
                     element.XML_ID AS product_xml_id,
                     element.`NAME` AS product_name,
                     4,
                     CONCAT (
                      element.`NAME`, ' ',
                      element.XML_ID, ' ',
                       GROUP_CONCAT(DISTINCT
                       CONVERT(
                       (SELECT prop_enum.`VALUE` WHERE type_prop.PROPERTY_TYPE='L')
                                     USING UTF8) SEPARATOR ' '),' ',
                       GROUP_CONCAT(DISTINCT
                       CONVERT(
                       (SELECT property.`VALUE` WHERE type_prop.PROPERTY_TYPE='S')
                          USING UTF8) SEPARATOR ' '),' '

                     ) 
                     AS product_prop_values_string 
                     FROM b_iblock_element AS element 
                     LEFT JOIN b_iblock_element_property AS property ON property.IBLOCK_ELEMENT_ID=element.ID 
                     LEFT JOIN b_iblock_property AS type_prop ON type_prop.IBLOCK_ID=4
                     LEFT JOIN b_iblock_property_enum AS prop_enum ON prop_enum.id=property.VALUE_ENUM
                     WHERE element.IBLOCK_ID=4 AND element.ACTIVE='Y' 
                     GROUP BY element.ID
                
EOT;
        
        $DB->query(
            $query_string, false, __FILE__ . ":" . __LINE__
        ); //промежуточные данные по индексу
    }
    
}
