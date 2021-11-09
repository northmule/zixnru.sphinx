<?php

require_once($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Grid\Options as GridOptions;
use Zixnru\Sphinx\SearcheStringTable;

Bitrix\Main\Loader::IncludeModule("zixnru.sphinx");

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("zixnru.sphinx");

if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm("������ ��������");
}

$APPLICATION->SetTitle('����� ������� ������ ������� � �����');

$list_id = SearcheStringTable::getTableName(
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
        "id"      => "STRING",
        'type'    => 'text',
        "name"    => '��������� �����',
        "default" => true
    ],
    [
        "id"      => "NOT_FOUND",
        'type'    => 'list',
        "name"    => '������� ������ ���������',
        "default" => false,
        'items'   => ['Y' => '��������'],
    ],
];

$filterData = $filterOption->getFilter($filter_list);

$filter = [];

$lAdmin->AddFilter($filter_list, $filter);

foreach ($filterData as $key => $value) {
    
    if ($key === 'STRING' && strlen($value) > 0) {
        $sql_where .= " AND {$list_id}.STRING LIKE '%{$value}%'";
    }
    
    if ($key === 'NOT_FOUND' && strlen($value) > 0) {
        $sql_where .= " AND {$list_id}.FOUND_ELEMENTS ='-1'";
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
    ["id"    => "STRING", "content" => '��������� �����', "sort" => "STRING",
     "align" => "center", "default" => true],
    ["id"   => "FOUND_ELEMENTS", "content" => '���������',
     "sort" => "FOUND_ELEMENTS", "align" => "center", "default" => true],
    ["id"    => "DATE_UPDATE", "content" => '����', "sort" => "DATE_UPDATE",
     "align" => "center", "default" => true],
];


/**
 * ������ �� ������� ���������� �������� �������
 */
$list = [];

while ($row = $rsData->fetch()) {
    
    $url_params = http_build_query(
        [
            'ID'   => $row['ID'],
            'lang' => LANGUAGE_ID,
            'ID'   => $row['ID'],
        ]
    );
    
    if ($row['FOUND_ELEMENTS'] == '-1') {
        $row['FOUND_ELEMENTS'] = '�� �����';
    } else {
        $row['FOUND_ELEMENTS'] = TruncateText($row['FOUND_ELEMENTS'], 40);
    }
    
    
    $setTableRow = &$lAdmin->addRow(
        $row['ID'], $row,
        "/bitrix/admin/innst_optlk_partners_action_racing_card.php?{$url_params}"
    );
}

$lAdmin->addHeaders($arHeaders); //����� �������

$lAdmin->SetVisibleHeaderColumn();

$lAdmin->AddAdminContextMenu(); //������� � exel � ������

$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filter_list); //�������� ������

$lAdmin->DisplayList(["SHOW_COUNT_HTML" => false]); //���������� ���������


require($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/epilog_admin.php");

