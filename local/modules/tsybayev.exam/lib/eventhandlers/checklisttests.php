<?php

namespace Tsybayev\Exam\EventHandlers;

IncludeModuleLangFile(__FILE__);

class CheckListTests
{
    public static function onCheckListGet($arCheckList)
    {
        $checkList = array('CATEGORIES' => array(), 'POINTS' => array());

        $checkList['CATEGORIES']['TSYBAYEV_EXAM'] = array(
            'NAME' => 'Мои тесты',
            'LINKS' => ''
        );

        $checkList['POINTS']['TSYBAYEV_EXAM_SITE_SUPPORT'] = array(
            'PARENT' => 'TSYBAYEV_EXAM',
            'REQUIRE' => 'N',
            'AUTO' => 'Y',
            'CLASS_NAME' => __CLASS__,
            'METHOD_NAME' => 'checkSiteSupportFile',
            'NAME' => 'Наличие инофрмации о техподдержке проекта',
            'DESC' => 'Проверка введена ли информация о техподдержке проекта',
            'HOWTO' => 'Производится проверка наличия непустого файла this_site_support.php в папке /bitrix/php_interface/',
            'LINKS' => 'links'
        );

        $checkList['POINTS']['TSYBAYEV_EXAM_PAGE_TITLE'] = array(
            'PARENT' => 'TSYBAYEV_EXAM',
            'REQUIRE' => 'Y',
            'AUTO' => 'N',
            'NAME' => 'Рекомендация формирования заголовка страницы',
            'DESC' => 'Заголовок страницы должен строиться по шаблону #Раздел 1.1#-#Раздел 1#-#Название сайта#',
            'HOWTO' => 'Проверяются загловки страниц на соотвествие шаблону',
        );

        return $checkList;
    }

    public static function checkSiteSupportFile($arParams)
    {
        $arResult = array('STATUS' => 'F');

        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/this_site_support.php';

        $check = (file_exists($filePath) && (filesize($filePath) > 0));

        if ($check === true) {
            $arResult = array(
                'STATUS' => true,
                'MESSAGE' => array(
                    'PREVIEW' => 'Файл /bitrix/php_interface/this_site_support.php найден',
                ),
            );
        } else {
            $arResult = array(
                'STATUS' => false,
                'MESSAGE' => array(
                    'PREVIEW' => 'Файл /bitrix/php_interface/this_site_support.php не найден',
                    'DETAIL' => 'Тест очень старался, но так и не смог найти файл. Ну и чёрт с ним',
                ),
            );
        }

        return $arResult;
    }
}
