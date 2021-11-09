<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class zixnru_sphinx extends CModule
{
    
    public $MODULE_ID = 'zixnru.sphinx';
    
    function __construct()
    {
        
        $arModuleVersion = [];
        
        include(__DIR__ . "/version.php");
        
        
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('ZIXNRU_SPHINX_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage(
            'ZIXNRU_SPHINX_MODULE_DESCRIPTION'
        );
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = "Djo";
        $this->PARTNER_URI = "http://zixn.ru";
        
        return true;
    }
    
    function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        return true;
    }
    
    function DoUninstall()
    {
        ModuleManager::unregisterModule($this->MODULE_ID);
        return true;
    }
    
    function InstallEvents()
    {
        
        return true;
    }
    
    function UnInstallEvents()
    {
        return true;
    }
    
    function InstallFiles()
    {
        return true;
    }
    
    function UnInstallFiles()
    {
        return true;
    }
    
    function InstallDB()
    {
        return true;
    }
    
    function UnInstallDB()
    {
        return true;
    }
    
}

?>