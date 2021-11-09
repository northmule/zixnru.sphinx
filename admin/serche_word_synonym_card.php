<?

require_once($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\DB\SqlQueryException;
use Zixnru\Sphinx\SynonymsPartsNameTable;

Bitrix\Main\Loader::IncludeModule("zixnru.sphinx");

$POST_RIGHT = $APPLICATION->GetGroupRight("zixnru.sphinx");

if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm("Доступ запрещен");
}

$APPLICATION->SetTitle('Добавить новый синоним');

if ($_REQUEST['dontsave'] ?? false) {
    LocalRedirect(
        "/bitrix/admin/zixnru_sphinx_serche_word_synonym_list.php?lang=ru"
    );
}

$id = intval($_REQUEST['ID'] ?? 0);

$action = $_REQUEST['action'] ?? null;

if ($id > 0) {
    
    $form = SynonymsPartsNameTable::getlist([
        'filter' => ['ID' => intval($id)],
        'limit'  => 1
    ])->fetch();
}

$error_message = '';


if ($action === 'new') {
    
    if ($id < 1) {
        
        $newSynonym = SynonymsPartsNameTable::createObject();
        
        $newSynonym->setSynonym(
            filter_var($_REQUEST['SYNONYM'] ?? '', FILTER_SANITIZE_STRING)
        );
        
        $newSynonym->setTarget(
            filter_var($_REQUEST['TARGET'] ?? '', FILTER_SANITIZE_STRING)
        );
        
        try {
            $newSynonym->save();
        } catch (SqlQueryException $ex) {
            $error_message = $ex->getMessage();
        }
        
        
        $id = $newSynonym->getId();
    } else {
        $editSynonym = SynonymsPartsNameTable::getByPrimary($id)->fetchObject();
        
        $editSynonym->setSynonym(
            filter_var($_REQUEST['SYNONYM'] ?? '', FILTER_SANITIZE_STRING)
        );
        
        $editSynonym->setTarget(
            filter_var($_REQUEST['TARGET'] ?? '', FILTER_SANITIZE_STRING)
        );
        
        $editSynonym->save();
        
        $id = $editSynonym->getId();
    }
    
    
    if (!empty($id)) {
        if (isset($_REQUEST['apply'])) {
            LocalRedirect(
                $APPLICATION->GetCurPageParam("ID={$id}&lang=ru", ['ID', 'lang']
                )
            );
        } elseif (isset($_REQUEST['save'])) {
            LocalRedirect(
                "/bitrix/admin/zixnru_sphinx_serche_word_synonym_list.php?lang=ru"
            );
        } elseif (isset($_REQUEST['save_and_add'])) {
            LocalRedirect($APPLICATION->GetCurPageParam("", ['ID', 'lang']));
        }
    }
}


$aTabs = [
    ["DIV" => "edit1", "TAB" => 'Добавить', "ICON" => "statistic_settings",
     "TITLE" => 'Добавить'],
];

$tabControl = new CAdminForm(
    'serche_word_synonym_card', $aTabs
); //Имя формы + _form


require($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/prolog_admin_after.php");


$tabControl->BeginPrologContent();

echo CAdminCalendar::ShowScript();

$tabControl->EndPrologContent();

$tabControl->BeginEpilogContent();
?>

<?= bitrix_sessid_post('SESSID') ?>
    <input type="hidden" name="ID" value=" <?php echo $id; ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="action" value="<?php echo($action ?? 'new'); ?>">

<? $tabControl->EndEpilogContent(); ?>

<?
$tabControl->Begin([
    'FORM_ACTION' => $APPLICATION->GetCurPage() . '?ID=' . IntVal($id)
        . '&lang=' . LANG
]);
?>

<?
$tabControl->BeginNextFormTab();

if (strlen($error_message) > 0) {
    $tabControl->BeginCustomField('ERROR_ADD', '');
    ?>
    <tr>
        <td colspan="2" align="center">
            <?php
            echo BeginNote() . ''
                . " Возникли ошибки при сохранении! {$error_message} <br>"
                . EndNote();
            ?>
        </td>
    </tr>
    <?php
    $tabControl->EndCustomField('ERROR_ADD', '');
}
/**
 * Вывод полей формы
 */
$tabControl->AddViewField('ID', 'ID:', $id, false);

$tabControl->AddEditField(
    'SYNONYM', 'Народное название:', true, ['size' => 60],
    $form['SYNONYM'] ?? ''
);

$tabControl->AddEditField(
    'TARGET', 'Реальное наименование запчасти', true, ['size' => 60],
    $form['TARGET'] ?? ''
);

$disable = false;


ob_start();
?>

    <input type="submit" class="adm-btn-save" name="save" id="save"
           value="Сохранить">
    <input type="submit" class="button" name="apply" id="apply"
           value="Применить">
    <input type="submit" class="button" name="dontsave" id="dontsave"
           value="Отменить">
    <input type="submit" class="adm-btn-add" name="save_and_add"
           id="save_and_add" value="Сохранить и добавить">

<?php
$buttons_add_html = ob_get_contents();
ob_end_clean();
$tabControl->Buttons(false, $buttons_add_html);


$tabControl->Show();
?>


<?php
require($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/epilog_admin.php");

