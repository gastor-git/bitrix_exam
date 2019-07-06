<?php

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$menu = [
    [
        'parent_menu' => 'global_menu_services',
        'section' => 'tsybayev_exam',
        'sort' => 10,
        'text' => Loc::getMessage('TSYBAYEV_EXAM_MENU_TITLE'),
        'title' => Loc::getMessage('TSYBAYEV_EXAM_MENU_TITLE'),
        "module_id" => "tsybayev_exam",
        'items_id' => 'menu_tsybayev_exam',
        "icon" => "util_menu_icon",
        "page_icon" => "util_page_icon",
        "items" => [
            [
                'text' => Loc::getMessage('TSYBAYEV_EXAM_MENU_TITLE_LIST'),
                'url' => 'tsybayev_exam_admin_page_list.php?lang=' . LANGUAGE_ID,
            ],
            [
                'text' => Loc::getMessage('TSYBAYEV_EXAM_MENU_TITLE_CREATE'),
                'url' => 'tsybayev_exam_admin_page_create.php?lang=' . LANGUAGE_ID,
            ],
        ],
    ],
];

return (!empty($menu)) ? $menu : [];
