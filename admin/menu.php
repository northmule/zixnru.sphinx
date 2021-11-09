<?php

Bitrix\Main\Loader::IncludeModule("zixnru.sphinx");

$POST_RIGHT = $APPLICATION->GetGroupRight("zixnru.sphinx");


$items = [];

$aMenu = [];

$generalMenu = [];

if ($POST_RIGHT !== "D") {
    $items[] = [
        "text"     => 'Синонимы поисковых фраз',
        "url"      => "zixnru_sphinx_serche_word_synonym_list.php?lang="
            . LANGUAGE_ID,
        "more_url" => [],
        "title"    => 'Синонимы поисковых фраз',
        "more_url" => [
            "zixnru_sphinx_serche_word_synonym_card.php",
        ]
    ];
    $items[] = [
        "text"     => 'Запчасти которые искали',
        "url"      => "zixnru_sphinx_serche_user_input_list.php?lang="
            . LANGUAGE_ID,
        "more_url" => [],
        "title"    => 'Запчасти которые искали',
        "more_url" => [
            "zixnru_sphinx_serche_user_input_list.php",
        ]
    ];

//Рассылки
    $generalMenu = [
        "parent_menu" => "global_menu_optlk",
        "section"     => "sphinx_searche",
        "sort"        => 300,
        "text"        => 'Управление Sphinx поиск',
        "title"       => 'Управление Sphinx поиск',
        "url"         => "zixnru_sphinx_serche_word_synonym_list.php?lang="
            . LANGUAGE_ID,
        "icon"        => "statistic_icon_searchers",
        "page_icon"   => "fileman_sticker_icon_sections",
        "items_id"    => "menu_stickers",
        "more_url"    => [
            "zixnru_sphinx_serche_word_synonym_list.php"
        ],
        "items"       => $items
    ];
}


$aMenu = [
    $generalMenu
];


return $aMenu;
