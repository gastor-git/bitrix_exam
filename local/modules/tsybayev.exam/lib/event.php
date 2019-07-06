<?php

namespace Tsybayev\Exam;

use \Bitrix\Main\Entity;

class Event
{
    public function onPanelCreateHandler()
    {
        global $APPLICATION;
        $APPLICATION->AddPanelButton(array(
            "ID" => 'bitrix_exam',
            "HREF" => "/bitrix/admin/tsybayev_exam_admin_page_list.php?lang=ru", // ссылка на кнопке
            "TYPE" => 'BIG', //BIG - большая кнопка, иначе маленькая
            "ICON" => "bx-panel-site-wizard-icon", //название CSS-класса с иконкой кнопки
            //"SRC" => "/bitrix/images/my_module_id/button_image.gif", // картинка на кнопке
            "ALT" => "Кнопка для экзамена", // Старый вариант
            "HINT" => array( //тултип кнопки
                "TITLE" => "Кнопка для экзамена",
                "TEXT" => "По клику переход в админку к кастомному списку" //HTML допускается
            ),
            "MAIN_SORT" => 10000,
            "SORT" => 10,
        ));
    }
}
