<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
    "NAME" => Loc::getMessage('EXAM_TEST_NAME'),
    "DESCRIPTION" => Loc::getMessage('EXAM_TEST_DESCRIPTION'),
    "ICON" => "",
    "SORT" => 10,
    "CACHE_PATH" => "Y",
    "PATH" => [
        "ID" => Loc::getMessage('TSYBAYEV_EXAM_NAME'),
    ],
    "COMPLEX" => "N",
);
