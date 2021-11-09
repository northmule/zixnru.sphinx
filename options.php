<?

require_once($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/prolog_admin_before.php");

$module_id = 'zixnru.sphinx';

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$MODULE_RIGHT = $APPLICATION->GetGroupRight("zixnru.logger");

if (!($MODULE_RIGHT >= "R")) {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$APPLICATION->SetTitle(Loc::getMessage("ZIXNRU_SPHINX_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/prolog_admin_after.php");


$aTabs = [];
$aTabs = [
    ["DIV"   => "edit1", "TAB" => Loc::getMessage("ZIXNRU_SPHINX_TAB"),
     "ICON"  => "vote_settings",
     "TITLE" => Loc::getMessage("ZIXNRU_SPHINX_TAB_TITLE")
    ],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$tabControl->Begin();
?>
<form method="POST"
      action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx(
          $module_id
      ) ?>&lang=<?= LANGUAGE_ID ?>&mid_menu=1" id="FORMACTION">
    <?
    $tabControl->BeginNextTab();
    require_once($_SERVER["DOCUMENT_ROOT"]
        . "/bitrix/modules/main/admin/group_rights.php");
    ?>
    
    <?php
    $tabControl->BeginNextTab();
    ?>
    
    <?
    $tabControl->Buttons();
    ?>
    <input <? if ($MODULE_RIGHT < "W") echo "disabled" ?> type="submit"
                                                          class="adm-btn-green"
                                                          name="Update"
                                                          value="Сохранить"/>
    <input type="hidden" name="Update" value="Y"/>
    <? echo bitrix_sessid_post(); ?>
    <? $tabControl->End();
    ?>

</form>


<?
require($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/epilog_admin.php");
?> 