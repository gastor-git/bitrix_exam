<?php

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

class ExamTest extends CBitrixComponent
{
    var $test;

    protected function checkModules()
    {
        if (!Loader::includeModule('tsybayev.exam'))
        {
            ShowError(Loc::getMessage('TSYBAYEV_EXAM_MODULE_NOT_INSTALLED'));
            return false;
        }

        return true;
    }

    public function executeComponent()
    {
        $this -> includeComponentLang('class.php');

        if($this -> checkModules())
        {
            /*Ваш код*/

            $this->includeComponentTemplate();
        }
    }
}