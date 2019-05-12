<?php

namespace Tsybayev\Exam;

use \Bitrix\Main\Entity;

class event
{
    public function eventHandler(Entity\Event $event)
    {
        //die();
        $result = new Entity\EventResult;

        echo 'Тело события<br>';

        //$result = 'Сообщение вернул обработчик'; //Не правильно

        $result->modifyFields(array('result' => 'Сообщение вернул обработчик'));

        return $result;
    }
}
