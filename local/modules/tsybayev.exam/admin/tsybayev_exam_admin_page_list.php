<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Text\HtmlFilter;

Loader::includeModule('tsybayev.exam');
use Tsybayev\Exam\TestTable;

IncludeModuleLangFile(__FILE__);

if ($APPLICATION->GetGroupRight("tsybayev.exam") < "R")
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle("Список элементов");

$sTableID = $entity_id = "tsybayev_exam_testtable";

$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

/*$arFilterFields = Array(
    "find_id",
    "find_name",
);
$USER_FIELD_MANAGER->AdminListAddFilterFields($entity_id, $arFilterFields);

$lAdmin->InitFilter($arFilterFields);*/

/*$arFilter = Array(
    "ID" => $find_id,
    "NAME" => $find_name,
);

$USER_FIELD_MANAGER->AdminListAddFilter($entity_id, $arFilter);*/

$filterFields = array(
    array(
        "id" => "ID",
        "name" => "ID",
        "filterable" => "",
        "default" => true
    ),
    array(
        "id" => "NAME",
        "name" => "NAME",
        "filterable" => "",
        "default" => true
    ),
    array(
        "id" => "TIMESTAMP_X",
        "name" => "TIMESTAMP_X",
        "filterable" => "",
        "default" => true
    ),
);

$USER_FIELD_MANAGER->AdminListAddFilterFieldsV2($entity_id, $filterFields);
$arFilter = array();
$lAdmin->AddFilter($filterFields, $arFilter);

$USER_FIELD_MANAGER->AdminListAddFilterV2($entity_id, $arFilter, $sTableID, $filterFields);

if ($lAdmin->EditAction()) //если пытаемся отредактировать
{
    $editableFields = array(
        "NAME" => 1, "TIMESTAMP_X" => 1
    );

    foreach ($_POST["FIELDS"] as $ID => $arFields) {
        $ID = intval($ID);

        if ($APPLICATION->GetGroupRight("tsybayev.exam") < "W") {
            return;
        }

        if (!$lAdmin->IsUpdated($ID))
            continue;

        foreach ($arFields as $key => $field) {
            if (!isset($editableFields[$key]) && strpos($key, "UF_") !== 0) {
                unset($arFields[$key]);
            }
        }
        $arFields['TIMESTAMP_X'] = new DateTime($arFields['TIMESTAMP_X']);
        $USER_FIELD_MANAGER->AdminListPrepareFields($entity_id, $arFields);

        $DB->StartTransaction();

        $ob = TestTable::update($ID, $arFields);  //обновляем запись

        if (!$ob->isSuccess()) {
            $lAdmin->AddUpdateError(Loc::getMessage("SAVE_ERROR") . $ID . ": " . $ob->LAST_ERROR, $ID);
            $DB->Rollback();
        }

        $DB->Commit();
    }
}


setHeaderColumn($lAdmin);

$nav = new PageNavigation("pages-test-list");
$nav->setPageSize($lAdmin->getNavSize());
$nav->initFromUri();
$userQuery = new Query(TestTable::getEntity());
$listSelectFields = $lAdmin->getVisibleHeaderColumns();

if (!in_array("ID", $listSelectFields))
    $listSelectFields[] = "ID";

$userQuery->setSelect($listSelectFields);
$sortBy = "ID";
$sortOrder = "DESC";
$userQuery->setOrder(array($sortBy => $sortOrder));
$userQuery->countTotal(true);
$userQuery->setOffset($nav->getOffset());

if ($_REQUEST["mode"] !== "excel")
    $userQuery->setLimit($nav->getLimit());

if (isset($arFilter["ID"])) {
    $userQuery->where("ID", "=", $arFilter["ID"]);
}
if (isset($arFilter["NAME"])) {
    $userQuery->whereLike("NAME", $arFilter["NAME"] . "%");
}

$result = $userQuery->exec();

$nav->setRecordCount($result->getCount());
$lAdmin->setNavigation($nav, "Pagination", false);

while ($repairData = $result->fetch()) {
    $repairID = $repairData["ID"];
    $repairEditUrl = "tsybayev_exam_admin_page_create.php?lang=" . LANGUAGE_ID . "&ID=" . $repairID;

    $row =& $lAdmin->addRow($repairID, $repairData, $repairEditUrl);
    $USER_FIELD_MANAGER->addUserFields($entity_id, $repairData, $row);
    $row->addViewField("ID", "<a href='" . $repairEditUrl . "' title='ID'>" . $repairID . "</a>");
    $edit = ($USER->canDoOperation('edit_all'));
    $can_edit = (IntVal($repairID) > 0 && $edit);
    if ($can_edit && $edit) {
        $row->addField("NAME", "<a href='" . $repairEditUrl . "' title='Редактировать'>" . HtmlFilter::encode($repairData["NAME"]) . "</a>", true);
        $row->addViewField("TIMESTAMP_X", TxtToHtml($repairData["TIMESTAMP_X"]));
    } else {
        $row->addViewField("NAME", "<a href='" . $repairEditUrl . "' title='Редактировать'>" . HtmlFilter::encode($repairData["NAME"]) . "</a>");
        $row->addViewField("TIMESTAMP_X", TxtToHtml($repairData["TIMESTAMP_X"]));
    }

    $arActions = array();
    $arActions[] = array(
        "ICON" => $can_edit ? "edit" : "view",
        "TEXT" => Loc::getMessage($can_edit ? "MAIN_ADMIN_MENU_EDIT" : "MAIN_ADMIN_MENU_VIEW"),
        "LINK" => $repairEditUrl, "DEFAULT" => true
    );
    if ($can_edit && $edit) {
        /*$arActions[] = array(
            "ICON" => "copy",
            "TEXT" => "Копировать",
            "LINK" => "zxkill_test_edit.php?lang=".LANGUAGE_ID."&COPY_ID=".$repairID
        );*/
        $arActions[] = array(
            "ICON" => "delete",
            "TEXT" => "Удалить",
            "ACTION" => "if(confirm('Точно удалить?')) " . $lAdmin->actionDoGroup($repairID, "delete")
        );
    }

    $row->addActions($arActions);
}

$aContext = Array();

$ar = Array(
    "edit" => true,
    "delete" => true,
    "for_all" => true,
);

$lAdmin->AddGroupActionTable($ar);

$aContext[] = array(
    "TEXT" => "Добавить",
    "LINK" => "tsybayev_exam_admin_page_create.php?lang=" . LANGUAGE_ID,
    "TITLE" => "Добавить",
    "ICON" => "btn_new"
);
$lAdmin->AddAdminContextMenu($aContext);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");

function setHeaderColumn(CAdminUiList $lAdmin)
{
    $arHeaders = array(
        array("id" => "ID", "content" => "ID", "sort" => "ID"),
        array("id" => "NAME", "content" => "Имя", "sort" => "NAME", "default" => true),
        array("id" => "TIMESTAMP_X", "content" => "Дата", "sort" => "TIMESTAMP_X", "default" => true)
    );
    $lAdmin->addHeaders($arHeaders);
}
