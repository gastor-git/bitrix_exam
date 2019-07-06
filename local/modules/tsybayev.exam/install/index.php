<?php

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Loader;
use \Bitrix\Main\IO;

Loc::loadMessages(__FILE__);

/**
 * Class tsybayev_exam
 */
class tsybayev_exam extends CModule
{
    /**
     * ID модуля
     *
     * @var string
     */
    public $MODULE_ID = 'tsybayev.exam';

    /**
     * Версия модуля
     *
     * @var string
     */
    public $MODULE_VERSION;

    /**
     * Дата версии модуля
     *
     * @var string
     */
    public $MODULE_VERSION_DATE;

    /**
     * Название модуля
     *
     * @var string
     */
    public $MODULE_NAME;

    /**
     * Описание модуля
     *
     * @var string
     */
    public $MODULE_DESCRIPTION;

    /**
     * Путь до модуля
     *
     * @var string
     */
    public $MODULE_PATH;

    /**
     * Название партнера
     *
     * @var string
     */
    public $PARTNER_NAME;

    /**
     * Ссылка на сайт партнера
     *
     * @var string
     */
    public $PARTNER_URI;

    /**
     * Индекс сортировки модуля
     *
     * @var int
     */
    public $MODULE_SORT = 1;

    /**
     * Флаг доступа к модулю для админов
     *
     * @var string
     */
    public $SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';

    /**
     * Флаг проверки прав доступа к модулю
     *
     * @var string
     */
    public $MODULE_GROUP_RIGHTS = 'Y';

    /**
     * tsybayev_exam constructor.
     */
    public function __construct()
    {
        $this->MODULE_NAME = Loc::getMessage("TSYBAYEV_EXAM_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TSYBAYEV_EXAM_MODULE_DESC");
        $this->PARTNER_NAME = Loc::getMessage("TSYBAYEV_EXAM_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("TSYBAYEV_EXAM_PARTNER_URI");

        $this->MODULE_PATH = $this->getModulePath();

        $arModuleVersion = [];
        include(__DIR__ . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
    }

    /**
     * Все действия по установке модуля
     */
    public function DoInstall()
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

        // Регистрация модуля в системе
        ModuleManager::registerModule($this->MODULE_ID);

        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();

        // Подключение файла с шагом установки (вывод ошибок или сообщения об успешной установке модуля)
        $GLOBALS['APPLICATION']->IncludeAdminFile(Loc::getMessage("TSYBAYEV_EXAM_INSTALL_TITLE"), $this->MODULE_PATH . "/install/step.php");
    }

    /**
     * Все действия по удалению модуля
     */
    public function DoUninstall()
    {
        // проверка прав согласно условию задания:
        // Устанавливать и удалять модуль может только пользователь с правами полного доступа
        // к главному модулю
        $hasRight = ($GLOBALS['APPLICATION']->GetGroupRight("main") == 'W');
        if (!$hasRight) {
            $GLOBALS['APPLICATION']->ThrowException(Loc::getMessage("TSYBAYEV_EXAM_INSTALL_ERROR_RIGHTS"));
        }

        // Удаление модуля в 2 шага
        $request = Application::getInstance()->getContext()->getRequest();
        // если первый шаг
        if ($request["step"] < 2) {
            // подключить файл первого шага удаления модуля (фрма с запросом на удаление модуля с чекбоксом удалять ли таблицы в БД)
            $GLOBALS['APPLICATION']->IncludeAdminFile(Loc::getMessage("TSYBAYEV_EXAM_UNINSTALL_TITLE"), $this->MODULE_PATH . "/install/unstep1.php");

        } elseif ($request["step"] == 2) { // если второй шаг

            $this->UnInstallFiles();
            $this->UnInstallEvents();

            // если выбрано удаление таблиц в БД
            if ($request["savedata"] != "Y") {
                $this->UnInstallDB();
            }

            // отмена регистрации модуля в системе
            ModuleManager::unRegisterModule($this->MODULE_ID);

            // Подключить фал второго шага удаления модуля (вывод ошибок или сообщения об успешном удалении модуля)
            $GLOBALS['APPLICATION']->IncludeAdminFile(Loc::getMessage("TSYBAYEV_EXAM_UNINSTALL_TITLE"), $this->MODULE_PATH . "/install/unstep2.php");
        }
    }

    /**
     * Создание таблиц в БД
     */
    public function InstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        if (!Application::getConnection(\Tsybayev\Exam\TestTable::getConnectionName())->isTableExists(
            Base::getInstance('\Tsybayev\Exam\TestTable')->getDBTableName())
        ) {
            Base::getInstance('\Tsybayev\Exam\TestTable')->createDbTable();
        }
    }

    /**
     * Удаление таблиц в БД
     */
    public function UnInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        Application::getConnection(\Tsybayev\Exam\TestTable::getConnectionName())
            ->queryExecute('drop table if exists '. Base::getInstance('\Tsybayev\Exam\TestTable')->getDBTableName());
    }

    /**
     * Регистрация обработчиков событий
     */
    public function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler('main', 'OnPanelCreate', $this->MODULE_ID, '\Tsybayev\Exam\Event', 'onPanelCreateHandler');
    }

    /**
     * Удаление регистрации обработчиков событий
     */
    public function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnPanelCreate', $this->MODULE_ID, '\Tsybayev\Exam\Event', 'onPanelCreateHandler');
    }

    /**
     * Копирование файлов
     *
     * @param array $arParams
     * @return bool
     */
    public function InstallFiles()
    {
        // Копируем компоненты
        $folderFromCopy = $this->MODULE_PATH . "/install/components";

        if (IO\Directory::isDirectoryExists($folderFromCopy)) {
            if ($folderToCopy = $this->getComponentsPath()) {

                CopyDirFiles($folderFromCopy, $folderToCopy, true, true);

            }
        }

        // Копируем файлы для админки
        $folderFromCopy =  $this->MODULE_PATH . "/install/admin";
        $folderToCopy = Application::getDocumentRoot() . "/bitrix/admin";

        if (IO\Directory::isDirectoryExists($folderFromCopy)) {

            CopyDirFiles($this->MODULE_PATH . "/install/admin", $folderToCopy);

        }

        return true;
    }

    /**
     * Удаление файлов
     *
     * @return bool
     */
    public function UnInstallFiles()
    {
        // Удаляем компоненты
        $folderToDelete = $this->getComponentsPath() . '/' . $this->MODULE_ID;

        if (IO\Directory::isDirectoryExists($folderToDelete)) {

            IO\Directory::deleteDirectory($folderToDelete);

        }

        // Удаляем файлы для админики
        $folderToCompare = $this->MODULE_PATH . '/install/admin';
        $folderToDelete = Application::getDocumentRoot() . "/bitrix/admin";

        if (IO\Directory::isDirectoryExists($folderToCompare)) {

            DeleteDirFiles($folderToCompare, $folderToDelete);

        }

        return true;
    }

    /**
     * Возвращает путь к папке модуля
     *
     * @return string
     */
    protected function getModulePath()
    {
        $modulePath = explode('/', __FILE__);
        $modulePath = array_slice($modulePath, 0, array_search($this->MODULE_ID, $modulePath) + 1);

        return implode('/', $modulePath);
    }

    /**
     * Возвращает путь где должны быть расположены компоненты модуля
     * в зависимости от расположения модуля (в local/components/ или в bitrix/components/)
     *
     * @param bool $absolute
     * @return string
     */
    protected function getComponentsPath($absolute = true)
    {
        $documentRoot = Application::getDocumentRoot();
        if (strpos($this->MODULE_PATH, 'local/modules') !== false) {
            $componentsPath = '/local/components';
        } else {
            $componentsPath = '/bitrix/components';
        }

        if ($absolute) {
            $componentsPath = sprintf('%s%s', $documentRoot, $componentsPath);
        }

        return $componentsPath;
    }

    /**
     * Поверяет что система поддерживает D7 (версия главного модуля не ниже 14.00.00)
     *
     * @return mixed
     */
    protected function isVersionD7()
    {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }

}
