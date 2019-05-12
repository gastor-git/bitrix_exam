<?php

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Loader;
use \Bitrix\Main\IO;

Loc::loadMessages(__FILE__);

class tsybayev_exam extends CModule
{
    //public $exclusionAdminFiles = [];

    function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . "/version.php");

        /*$this->exclusionAdminFiles = [
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php',
        ];*/

        $this->MODULE_ID = 'tsybayev.exam';
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("TSYBAYEV_EXAM_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TSYBAYEV_EXAM_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("TSYBAYEV_EXAM_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("TSYBAYEV_EXAM_PARTNER_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = "Y";
    }

    function DoInstall()
    {

        // Проверка поддержки D7
        if (!$this->isVersionD7()) {
            $GLOBALS['APPLICATION']->ThrowException(Loc::getMessage("TSYBAYEV_EXAM_INSTALL_ERROR_VERSION"));
        }

        // проверка прав согласно условию задания:
        // Устанавливать и удалять модуль может только пользователь с правами полного доступа
        // к главному модулю
        $hasRight = ($GLOBALS['APPLICATION']->GetGroupRight("main") == 'W');
        if (!$hasRight) {
            $GLOBALS['APPLICATION']->ThrowException(Loc::getMessage("TSYBAYEV_EXAM_INSTALL_ERROR_RIGHTS"));
        }

        ModuleManager::registerModule($this->MODULE_ID);

        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();

        $GLOBALS['APPLICATION']->IncludeAdminFile(Loc::getMessage("TSYBAYEV_EXAM_INSTALL_TITLE"), $this->GetPath() . "/install/step.php");
    }

    function DoUninstall()
    {
        // проверка прав согласно условию задания:
        // Устанавливать и удалять модуль может только пользователь с правами полного доступа
        // к главному модулю
        $hasRight = ($GLOBALS['APPLICATION']->GetGroupRight("main") == 'W');
        if (!$hasRight) {
            $GLOBALS['APPLICATION']->ThrowException(Loc::getMessage("TSYBAYEV_EXAM_INSTALL_ERROR_RIGHTS"));
        }

        $request = Application::getInstance()->getContext()->getRequest();

        if ($request["step"] < 2) {

            $GLOBALS['APPLICATION']->IncludeAdminFile(Loc::getMessage("TSYBAYEV_EXAM_UNINSTALL_TITLE"), $this->GetPath() . "/install/unstep1.php");

        } elseif ($request["step"] == 2) {

            $this->UnInstallFiles();
            $this->UnInstallEvents();

            if ($request["savedata"] != "Y") {
                $this->UnInstallDB();
            }

            ModuleManager::unRegisterModule($this->MODULE_ID);

            $GLOBALS['APPLICATION']->IncludeAdminFile(Loc::getMessage("TSYBAYEV_EXAM_UNINSTALL_TITLE"), $this->GetPath() . "/install/unstep2.php");
        }
    }

    function InstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        if (!Application::getConnection(\Tsybayev\Exam\TestTable::getConnectionName())->isTableExists(
            Base::getInstance('\Tsybayev\Exam\TestTable')->getDBTableName())
        ) {
            Base::getInstance('\Tsybayev\Exam\TestTable')->createDbTable();
        }
    }

    function UnInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        Application::getConnection(\Tsybayev\Exam\TestTable::getConnectionName())
            ->queryExecute('drop table if exists '. Base::getInstance('\Tsybayev\Exam\TestTable')->getDBTableName());
    }

    function InstallEvents()
    {
        //EventManager::getInstance()->registerEventHandler($this->MODULE_ID, 'TestEventExam', $this->MODULE_ID, '\Tsybayev\Exam\Event', 'eventHandler');
    }

    function UnInstallEvents()
    {
        //EventManager::getInstance()->unRegisterEventHandler($this->MODULE_ID, 'TestEventExam', $this->MODULE_ID, '\Tsybayev\Exam\Event', 'eventHandler');
    }

    function InstallFiles($arParams = [])
    {
        $path = $this->GetPath() . "/install/components";

        if (IO\Directory::isDirectoryExists($path)) {

            CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);

        } else {
            throw new IO\InvalidPathException($path);
        }

        if (IO\Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {

            CopyDirFiles($this->GetPath() . "/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"); //если есть файлы для копирования

            /*if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->exclusionAdminFiles))
                        continue;
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item,
                        '<' . '? require($_SERVER["DOCUMENT_ROOT"]."' . $this->GetPath(true) . '/admin/' . $item . '");?' . '>');
                }
                closedir($dir);
            }*/
        }

        return true;
    }

    function UnInstallFiles()
    {
        IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/tsybayev/');

        if (IO\Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {

            DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . $this->GetPath() . '/install/admin/', $_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin');

            /*if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->exclusionAdminFiles))
                        continue;
                    \Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item);
                }
                closedir($dir);
            }*/
        }
        return true;
    }

    //Определяем место размещения модуля
    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    //Проверяем что система поддерживает D7
    public function isVersionD7()
    {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }

    function GetModuleRightList()
    {
        return [
            "reference_id" => ["D", "K", "S", "W"],
            "reference" => [
                "[D] " . Loc::getMessage("TSYBAYEV_EXAM_DENIED"),
                "[K] " . Loc::getMessage("TSYBAYEV_EXAM_READ_COMPONENT"),
                "[S] " . Loc::getMessage("TSYBAYEV_EXAM_WRITE_SETTINGS"),
                "[W] " . Loc::getMessage("TSYBAYEV_EXAM_FULL")
            ]
        ];
    }
}
