<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);


$arParams["PAGE_ELEMENT_COUNT"] = intval($arParams["PAGE_ELEMENT_COUNT"]);
if ($arParams["PAGE_ELEMENT_COUNT"] <= 0)
    $arParams["PAGE_ELEMENT_COUNT"] = 20;


$arParams["DISPLAY_TOP_PAGER"] = $arParams["DISPLAY_TOP_PAGER"] == "Y";
$arParams["DISPLAY_BOTTOM_PAGER"] = $arParams["DISPLAY_BOTTOM_PAGER"] != "N";
$arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"]);
$arParams["PAGER_SHOW_ALWAYS"] = $arParams["PAGER_SHOW_ALWAYS"] != "N";
$arParams["PAGER_TEMPLATE"] = trim($arParams["PAGER_TEMPLATE"]);
$arParams["PAGER_DESC_NUMBERING"] = $arParams["PAGER_DESC_NUMBERING"] == "Y";
$arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] = intval($arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]);
$arParams["PAGER_SHOW_ALL"] = $arParams["PAGER_SHOW_ALL"] !== "N";

$arNavParams = array(
    "nPageSize" => $arParams["PAGE_ELEMENT_COUNT"],
    "bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"],
    "bShowAll" => $arParams["PAGER_SHOW_ALL"],
);
$arNavigation = CDBResult::GetNavParams($arNavParams);


if (!CModule::IncludeModule("iblock")) {
    $this->AbortResultCache();
    ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
    return;
}

if (is_numeric($arParams["IBLOCK_ID"])) {
    $rsIBlock = CIBlock::GetList(array(), array(
        "ACTIVE" => "Y",
        "ID" => $arParams["IBLOCK_ID"],
    ));
} else {
    $rsIBlock = CIBlock::GetList(array(), array(
        "ACTIVE" => "Y",
        "CODE" => $arParams["IBLOCK_ID"],
        "SITE_ID" => SITE_ID,
    ));
}

if ($arResult = $rsIBlock->GetNext()) {

    if ($this->startResultCache()) {

        // сначала собрать все ID разделов
        $arSectionFilter = [
            'IBLOCK_ID' => $arParams["IBLOCK_ID"],
            'ACTIVE' => 'Y',
            'GLOBAL_ACTIVE' => 'Y'
        ];
        $arSectionSelect = array(
            "ID",
            "NAME",
            "DESCRIPTION",
            "IBLOCK_ID",
            "SECTION_PAGE_URL"
        );
        $ob = CIBlockSection::GetList(
            [],
            $arSectionFilter,
            false,
            $arSectionSelect
        );
        $sections = [];
        while ($res = $ob->GetNext()) {
            $sections[$res['ID']] = $res;
        }

        // потом товары
        $arSelect = array(
            "ID",
            "NAME",
            "IBLOCK_ID",
            "IBLOCK_SECTION_ID",
            "DETAIL_PAGE_URL",
            "PREVIEW_TEXT",
        );

        $arFilter = array(
            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
            "ACTIVE" => "Y",
        );

        $arSort = ['IBLOCK_SECTION_ID' => 'ASC', 'ID' => 'DESC'];

        $rsElements = CIBlockElement::GetList(
            $arSort,
            $arFilter,
            false,
            $arNavParams,
            $arSelect
        );

        $arResult["ITEMS"] = [];
        while ($obElement = $rsElements->GetNextElement()) {
            $arItem = $obElement->GetFields();

            $arButtons = CIBlock::GetPanelButtons(
                $arItem["IBLOCK_ID"],
                $arItem["ID"],
                $arResult["ID"],
                array("SECTION_BUTTONS" => false, "SESSID" => false)
            );
            $arItem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
            $arItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

            $arResult["ITEMS"][] = $arItem;
            $arResult["ELEMENTS"][] = $arItem["ID"];
        }

        foreach ($arResult["ITEMS"] as $key => &$arItem) {
            $arItem['SECTION'] = $sections[$arItem['IBLOCK_SECTION_ID']];
        }

        $arResult["NAV_STRING"] = $rsElements->GetPageNavStringEx($navComponentObject, $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"], $arParams["PAGER_SHOW_ALWAYS"]);
        $arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
        $arResult["NAV_RESULT"] = $rsElements;

        $this->IncludeComponentTemplate();

    }
}
