<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Application;

$this->setFrameMode(true);

$get = Application::getInstance()->getContext()->getRequest()->getQueryList()->toArray();

$sendResult = (empty($get['send_result']) && empty($get['show_result'])) || $get['send_result'] == 'Y';
$showResult = ($get['show_result'] == 'Y');
?>

<a class="<?= ($sendResult) ? 'hidden' :''; ?>"
   href="<?= $APPLICATION->GetCurPageParam('send_result=Y', ['send_result', 'show_result'], false); ?>">
    Сбор результатов
</a>

<a class="<?= ($showResult) ? 'hidden' :''; ?>"
   href="<?= $APPLICATION->GetCurPageParam('show_result=Y', ['show_result', 'send_result'], false); ?>">
    Вывод результатов
</a>


<? if ($showResult && $arResult['SUCCESS']) { ?>
    <br>
    <div class="result">
        <h4>Кол-во записей, найденное в результате фильтрации: <?= $arResult['RESULT_CNT']; ?></h4>
    </div>
<? } ?>

<br><br>
<form action="<?= POST_FORM_ACTION_URI; ?>" method="post" name="send_results">
    <input type="hidden" name="apply" value="Y">
    <? if (!empty($arResult['ERRORS'])) {
        foreach ($arResult['ERRORS'] as $error) { ?>
            <div class="error">
                <?= $error; ?>
            </div>
            <br>
        <? }
    } ?>
    <? if ($arResult['SUCCESS'] && $sendResult) { ?>
        <div class="success">Данные успешно сохранены</div><br>
    <? } ?>
    <? foreach ($arResult['FIELDS'] as $fieldName => $filedParams) { ?>
        <div class="form-group">
            <label for="<?= $fieldName; ?>"><?= $fieldName; ?></label>
            <? if ($filedParams['TYPE'] !== 'list') { ?>

                <input id="<?= $fieldName; ?>" class="form-control" type="<?= $filedParams['TYPE']; ?>" name="<?= $fieldName; ?>" value="<?= $filedParams['VALUE']; ?>">

            <? } else { ?>

                <select name="<?= $fieldName; ?>" class="form-control" id="<?= $fieldName; ?>">
                    <option <?= (!$filedParams['VALUE']) ? 'selected' :''; ?>>Выберите пол</option>
                    <? foreach ($filedParams['ITEMS'] as $itemId => $itemName) { ?>
                        <option value="<?= $itemId?>" <?= ((int)$itemId === (int)$filedParams['VALUE']) ? 'selected' : ''; ?>><?= $itemName?></option>
                    <? } ?>
                </select>

            <? } ?>
        </div>
    <? } ?>

    <button type="submit" class="btn btn-primary">Send</button>

</form>