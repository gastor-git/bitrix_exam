<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loader::includeModule('tsybayev.exam');

use Tsybayev\Exam\TestTable;

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight('tsybayev.exam');
$isAdmin = $USER->CanDoOperation('edit_php');
if ($POST_RIGHT == 'D') {
    $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

$ID = intval($ID);
if ($ID > 0) {
    $res = TestTable::getlist([
        'select' => ['*'],
        'filter' => ['ID' => $ID]
    ]);
    if ($ob = $res->fetch())
        $arElement = $ob;

}
$sTableID = "tsybayev_exam_testtable";

$aTabs = array(array("DIV" => "tab1", "TAB" => "Деталка", "ICON" => "main_user_edit", "TITLE" => "Деталка"));
$editTab = new CAdminTabControl("editTab", $aTabs);

$APPLICATION->ResetException();
if ($REQUEST_METHOD == "POST" && (strlen($save) > 0 || strlen($apply) > 0) && $isAdmin && check_bitrix_sessid()) {
    $arFields = Array(
        "NAME" => htmlspecialcharsbx($NAME),
    );

    if ($ID > 0) {
        if (!$res = TestTable::Update($ID, $arFields)) {
            $lAdmin->AddGroupError("Ошибка изменения записи: ", $ID);
        }
    } else {
        $arFields['TIMESTAMP_X'] = new DateTime();
        if (!$ID = TestTable::Add($arFields)) {
            $lAdmin->AddGroupError("Ошибка добавления записи: ");
        }
        $res = ($ID > 0);
    }

    if ($res) {
        if (strlen($save) > 0)
            LocalRedirect("/bitrix/admin/tsybayev_exam_admin_page_list.php?lang=" . LANGUAGE_ID);
        elseif (strlen($apply) > 0)
            LocalRedirect("/bitrix/admin/tsybayev_exam_admin_page_create.php?&ID=" . $ID . "&" . $editTab->ActiveTabParam() . '&lang=' . LANGUAGE_ID);
    }
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$message = null;
if ($e = $APPLICATION->GetException()) {
    $message = new CAdminMessage(Loc::getMessage("MAIN_AGENT_ERROR_SAVING"), $e);
    $DB->InitTableVarsForEdit("tsybayev_exam_testtable", "", "a_");
}

if ($message)
    echo $message->Show();
?>
    <form name="save_test" action="<?
    echo $APPLICATION->GetCurPage() ?>?lang=<?= LANG ?>" method="POST">
        <?= bitrix_sessid_post() ?>
        <?
        $editTab->Begin();
        $editTab->BeginNextTab();
        ?>
        <input type="hidden" name="ID" value=<?
        echo $ID ?>>
        <!--<tr>
            <td>ID:</td> <td><? /*=$arElement['ID']*/ ?></td>
        </tr>-->
        <tr>
            <td>NAME:</td>
            <td><input type="text" name="NAME" size="40" value="<?= $arElement['NAME'] ?>"></td>
        </tr>
        <!--<tr>
            <td>DATE:</td><td><?/*=$arElement['TIMESTAMP_X']*/ ?></td>
        </tr>-->
        <?
        $editTab->Buttons(array("disabled" => !$isAdmin, "back_url" => "tsybayev_exam_admin_page_list.php?lang=" . LANGUAGE_ID));
        $editTab->End();
        ?>
    </form>
<?
$editTab->ShowWarnings("save_test", $message);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
