<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div class='d-block'>
    <? if ($arParams["DISPLAY_TOP_PAGER"]): ?>
        <?= $arResult["NAV_STRING"] ?><br/>
    <? endif; ?>

    <div class='d-block-header'><?= $arResult['NAME'] ?></div><!-- /d-block-header -->
    <br><br>

    <? foreach ($arResult["ITEMS"] as $arElement): ?>
        <?
        $this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));
        ?>
        <div class='d-block-i' id="<?= $this->GetEditAreaId($arElement['ID']); ?>">
            <strong><a href="<?= $arElement["SECTION"]["SECTION_PAGE_URL"]; ?>"><?= $arElement["SECTION"]["NAME"]; ?></a></strong>
            <h3><?= $arElement["NAME"] ?></h3>
            <div><?= $arElement["PREVIEW_TEXT"]; ?></div>
        </div><!-- /d-block-i -->
        <br><br>
    <? endforeach; // foreach($arResult["ITEMS"] as $arElement):?>

    <? if ($arParams["DISPLAY_BOTTOM_PAGER"]): ?>
        <br/><?= $arResult["NAV_STRING"] ?>
    <? endif; ?>

</div><!-- /d-block -->

<? if ($arParams["SET_TITLE"]) $APPLICATION->SetTitle($arResult["NAME"]); ?>