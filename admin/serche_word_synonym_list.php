<?php

require_once($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Grid\Options as GridOptions;
use Zixnru\Sphinx\SynonymsPartsNameTable;

Bitrix\Main\Loader::IncludeModule("zixnru.sphinx");

$POST_RIGHT = $APPLICATION->GetGroupRight("zixnru.sphinx");

if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm("������ ��������");
}

$APPLICATION->SetTitle('�������� ��������� ���� �������������');


if (!empty($_REQUEST['DELETE'])) {
    
    $id_delete = intval($_REQUEST['ID'] ?? 0);
    
    $deleteSynonym = SynonymsPartsNameTable::getByPrimary(
        $id_delete
    )->fetchObject();
    
    $deleteSynonym->delete();
    
    LocalRedirect('/bitrix/admin/zixnru_sphinx_serche_word_synonym_list.php');
}

$list_id = SynonymsPartsNameTable::getTableName(
); //����������� �������

$grid_options = new GridOptions($list_id);

$sort_key = key($grid_options->getSorting()['sort']);

if (empty(trim($sort_key))) {
    $sort_key = 'ID';
}

$sort_order = $grid_options->getSorting()['sort'][$sort_key];

if (empty(trim($sort_order))) {
    $sort_order = 'DESC';
}

$oSort = new CAdminSorting($list_id, $sort_key, $sort_order);

$lAdmin = new CAdminUiList($list_id, $oSort);


/**
 * ���������
 */
$nav_params = $grid_options->GetNavParams();


$nav = $lAdmin->getPageNavigation($list_id);


$nav->allowAllRecords(true)//�������� ���
->setRecordCount(
    $DB->query("SELECT COUNT(*) as CNT FROM {$list_id}")->fetch()['CNT']
) //��� ������ ������ "�������� ���"
->initFromUri();

$lAdmin->setNavigation($nav, "��������", false);


/**
 * ��������� �������
 */
$sql_where = 'WHERE 1=1';

$sql_joint = '';

$sql_order = "ORDER BY `{$sort_key}` {$sort_order}";

$sql_limit = 'LIMIT ' . $nav->getLimit();

$sql_offset = 'OFFSET ' . $nav->getOffset();

/**
 * ����������
 */
if (($_GET['grid_id'] ?? null) === $list_id) {
    if (isset($_GET['grid_action']) and $_GET['grid_action'] === 'sort') {
        $sql_order
            = "ORDER BY `{$DB->ForSql($_GET['by'])}` {$DB->ForSql($_GET['order'])}";
    }
}

/**
 * ����������
 */
$filterOption = new Bitrix\Main\UI\Filter\Options($list_id);

/**
 * ������ ��������
 */
$filter_list = [
    [
        "id"      => "SYNONYM",
        'type'    => 'text',
        "name"    => '�������� ��������',
        "default" => true
    ],
    [
        "id"      => "TARGET",
        'type'    => 'text',
        "name"    => '�������� � ����� ������������',
        "default" => true
    ]
];

$filterData = $filterOption->getFilter($filter_list);

$filter = [];

$lAdmin->AddFilter($filter_list, $filter);

foreach ($filterData as $key => $value) {
    
    if ($key === 'SYNONYM' && strlen($value) > 0) {
        $sql_where .= " AND {$list_id}.SYNONYM LIKE '%{$value}%'";
    }
    
    
    if ($key === 'TARGET' && strlen($value) > 0) {
        $sql_where .= " AND {$list_id}.TARGET LIKE '%{$value}%'";
    }
}

/**
 * ���� ������
 */
$sql_query = <<<EOT
            SELECT 
            *
            FROM {$list_id}
           {$sql_joint}
           {$sql_where}
           {$sql_order}
           {$sql_limit}
           {$sql_offset}
EOT;

/**
 * ��������� ������� ��� ����������� �������
 */
$rsData = $DB->query($sql_query);

/**
 * ������� �������
 */
$arHeaders = [
    ["id"      => "ID", "content" => 'ID', "sort" => "ID", "align" => "center",
     "default" => true],
    ["id"    => "SYNONYM", "content" => '�������� ��������',
     "sort"  => "SYNONYM", "align" => "center", "default" => true],
    ["id"   => "TARGET", "content" => '�������� � ����� ������������',
     "sort" => "TARGET", "align" => "center", "default" => true],
];


/**
 * ������ �� ������� ���������� �������� �������
 */
$list = [];

while ($row = $rsData->fetch()) {
    
    $action_menu = [];
    
    $params = [];
    
    
    $params = [
        'ID'   => $row['ID'],
        'lang' => LANGUAGE_ID,
        'ID'   => $row['ID'],
    ];
    
    $url_params = http_build_query($params);
    
    $setTableRow = &$lAdmin->addRow(
        $row['ID'], $row,
        "/bitrix/admin/zixnru_sphinx_serche_word_synonym_card.php?{$url_params}"
    );
    
    
    $action_menu[] = [
        'text'    => '��������',
        'default' => true,
        'onclick' => "document.location.href='/bitrix/admin/zixnru_sphinx_serche_word_synonym_card.php?{$url_params}'"
    ];
    
    $params['DELETE'] = 1;
    
    $url_params = http_build_query($params);
    
    $action_menu[] = [
        'text'    => '�������',
        'default' => false,
        'onclick' => "document.location.href='/bitrix/admin/zixnru_sphinx_serche_word_synonym_list.php?{$url_params}'"
    ];
    
    $setTableRow->addActions($action_menu);
}

$lAdmin->addHeaders($arHeaders); //����� �������

$lAdmin->SetVisibleHeaderColumn();

$button[] = [
    'TEXT'    => '�������� �������',
    'ONCLICK' => "document.location.href='/bitrix/admin/zixnru_sphinx_serche_word_synonym_card.php'"
];

$lAdmin->AddAdminContextMenu($button, true); //������� � exel � ������

$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filter_list); //�������� ������

$lAdmin->DisplayList(["SHOW_COUNT_HTML" => false]); //���������� ���������


require($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/epilog_admin.php");

