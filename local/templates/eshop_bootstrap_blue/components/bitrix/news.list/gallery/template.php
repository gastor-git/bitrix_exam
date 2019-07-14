<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);
?>

<div class="news-list" id="db-items">

    <? if ($arParams['IS_AJAX_PAGINATION']) {
        $APPLICATION->RestartBuffer();
    } ?>

    <? foreach ($arResult["ITEMS"] as $arItem):
        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
        ?>
        <div class="news-item" id="<?= $this->GetEditAreaId($arItem['ID']); ?>">
            <span class="feed-com-img-wrap">
                <img class="preview_picture"
                     border="0"
                     src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
                     width="<?= $arItem["PREVIEW_PICTURE"]["WIDTH"] ?>"
                     height="<?= $arItem["PREVIEW_PICTURE"]["HEIGHT"] ?>"
                     alt="<?= $arItem["PREVIEW_PICTURE"]["ALT"] ?>"
                     title="<?= $arItem["PREVIEW_PICTURE"]["TITLE"] ?>"
                     style="float:left"
                     data-bx-viewer="image"
                     onload="this.parentNode.className='feed-com-img-wrap';"
                     data-bx-title="<?= $arItem['NAME']; ?>"
                     data-bx-src="<?= $arItem['DETAIL_PICTURE']['SRC']; ?>"
                     data-bx-download="<?= $arItem['DETAIL_PICTURE']['SRC']; ?>"
                     data-bx-width="548"
                     data-bx-height="346"
                />
            </span>

            <div>
                <b class="css_popup"><? echo $arItem["NAME"] ?></b>
                <div class="hidden">
                    <br><br>
                    <div><?= $arItem['DETAIL_TEXT']; ?></div>
                </div>
            </div>
        </div>

        <div style="clear:both"></div>
    <? endforeach; ?>

    <? if ($arParams["DISPLAY_BOTTOM_PAGER"]): ?>
        <br><br/>
        <div id="pagination">
            <?= $arResult["NAV_STRING"]; ?>
        </div>
    <? endif; ?>

</div>

<script>
    BX.ready(function () {

        function easyShow(node) {
            var easingAppear = new BX.easing({
                duration: 900,
                start: {height: 0, opacity: 0},
                finish: {height: 100, opacity: 100},
                transition: BX.easing.transitions.quart,
                step: function (state) {
                    node.style.height = state.height + "px";
                    node.style.opacity = state.opacity / 100;
                },
            });
            easingAppear.animate();
        }

        function easyHide(node) {
            var easingDelete = new BX.easing({
                duration: 900,
                start: {height: 100, opacity: 100},
                finish: {height: 0, opacity: 0},
                transition: BX.easing.transitions.quart,
                step: function (state) {
                    node.style.height = state.height + "px";
                    node.style.opacity = state.opacity / 100;
                },
            });
            easingDelete.animate();
        }

        // используем ajax для пагинации без перезагрузки страницы
        function DEMOLoad(url) {

            // используем easing для анимации
            easyHide(BX("db-items"));

            BX.ajax.post(
                url,
                {'is_ajax' : 'Y'},
                DEMOResponse
            );
        }

        // обработка ajax ответа
        function DEMOResponse(html) {

            BX("db-items").innerHTML = html;

            // используем easing для анимации
            easyShow(BX("db-items"));
        }

        // вешаем ajax на клик по кнопкам пагинации
        BX.bindDelegate(
            BX('pagination'), 'click', {className: 'page-link', tagName: 'a'},
            function (e) {
                if (!e) {
                    e = window.event;
                }
                // получить ссылку на кнопке пагинации
                var url = BX(e.target).getAttribute('href');
                // ссылка на первую страницу не содержит параметр PAGEN - добавляем его
                if (url.indexOf('?PAGEN_1') === -1) {
                    url = url + '?PAGEN_1=1';
                }
                DEMOLoad(url);
                return BX.PreventDefault(e);
            }
        );
    });
</script>

<? if ($arParams['IS_AJAX_PAGINATION']) {
    die();
} ?>

<script>
    BX.ready(function () {

        // импользуем viewer для отображения детальной кратинки при клике на превью картинку
        var obImageView = BX.viewElementBind(
            'db-items',
            {showTitle: true, lockScroll: false},
            function (node) {
                return BX.type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
            }
        );

        // используем popup для отображение детального текста в popup окне при клике на название
        var oPopup = new BX.PopupWindow('call_feedback', window.body, {
            autoHide: true,
            offsetTop: 1,
            offsetLeft: 0,
            lightShadow: true,
            closeIcon: true,
            closeByEsc: true,
            overlay: {
                backgroundColor: 'lightgrey', opacity: '80'
            }
        });

        BX.bindDelegate(
            document.body, 'click', {className: 'css_popup'},
            BX.proxy(function (e) {
                if (!e) {
                    e = window.event;
                }
                oPopup.setContent(BX.findNextSibling(e.target).innerHTML);
                oPopup.show();
                return BX.PreventDefault(e);
            }, oPopup)
        );
    });
</script>