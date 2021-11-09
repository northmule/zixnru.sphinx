<?php

namespace Zixnru\Sphinx;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Данные для индексации Sphinx
 */
class IndexEorderTable extends Entity\DataManager
{
    
    public static function getFilePath()
    {
        return __FILE__;
    }
    
    public static function getTableName()
    {
        return 'zixnru_sphinx_eorder_searche_all';
    }
    
    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                    'primary'      => true,
                    'autocomplete' => true,
                ]
            ),
            new Entity\IntegerField('ELEMENT_ID', [
                    'required' => false
                ]
            ),
            new Entity\StringField('XML_ID', [
                    'required' => false
                ]
            ),
            new Entity\StringField('NAME', [
                    'required' => false
                ]
            ),
            new Entity\IntegerField('IBLOCK_ID', [
                    'required' => false
                ]
            ),
            new Entity\TextField('PROP', [
                    'required' => false
                ]
            ),
            new Entity\DatetimeField('DATE_UPDATE', [
                    'required' => false
                ]
            ),
        ];
    }
    
}
