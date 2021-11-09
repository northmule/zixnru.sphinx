<?php

namespace Zixnru\Sphinx;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Запросы пользователей на поиск
 */
class SearcheStringTable extends Entity\DataManager
{
    
    public static function getFilePath()
    {
        return __FILE__;
    }
    
    public static function getTableName()
    {
        return 'zixnru_sphinx_searche_string';
    }
    
    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                    'primary'      => true,
                    'autocomplete' => true,
                ]
            ),
            new Entity\IntegerField('USER_ID', [
                    'required' => false
                ]
            ),
            new Entity\StringField('STRING', [
                    'required' => false
                ]
            ),
            new Entity\TextField('FOUND_ELEMENTS', [
                    'required' => false
                ]
            ),
            new Entity\TextField('QUERY_STRING', [
                    'required' => false
                ]
            ),
            new Entity\DatetimeField('DATE_UPDATE', [
                    'required' => false
                ]
            ),
        ];
    }
    
    public static function OnBeforeAdd(Entity\Event $event)
    {
        
        global $USER;
        
        $result = new Entity\EventResult;
        
        $data = $event->getParameter("fields");
        
        $modifyFieldList = [];
        
        
        if (!isset($data['USER_ID']) and is_object($USER)) {
            $modifyFieldList['USER_ID'] = intval($USER->GetId());
        }
        
        if (is_array($modifyFieldList) && count($modifyFieldList) > 0) {
            $result->modifyFields($modifyFieldList);
        }
        
        
        return $result;
    }
    
}
