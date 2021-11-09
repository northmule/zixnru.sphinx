<?php

namespace Zixnru\Sphinx;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Синонимы названий автозапчастей
 */
class SynonymsPartsNameTable extends Entity\DataManager
{
    
    public static function getFilePath()
    {
        return __FILE__;
    }
    
    public static function getTableName()
    {
        return 'zixnru_sphinx_synonyms_parts_name';
    }
    
    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                    'primary'      => true,
                    'autocomplete' => true,
                ]
            ),
            new Entity\TextField('SYNONYM', [
                    'required' => false
                ]
            ),
            new Entity\TextField('TARGET', [
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
