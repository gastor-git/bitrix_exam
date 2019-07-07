<?php

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Type\RandomSequence;

/**
 * Class ExamTest
 */
class ExamTest extends CBitrixComponent
{
    private $errors = [];
    private $success = false;
    private $seq;
    protected $request = [];

    /**
     * ExamTest constructor.
     * @param null $component
     * @throws \Bitrix\Main\LoaderException
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
        Loader::includeModule('tsybayev.exam');
        Loc::loadMessages(__FILE__);
        $this->seq = new RandomSequence();
    }

    /**
     * Вся обработка параметров здесь
     *
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    /**
     * Вся обработка request здесь
     *
     * @throws \Bitrix\Main\SystemException
     */
    private function processRequest()
    {
        $request = $this->clearTags(Application::getInstance()->getContext()->getRequest()->toArray());

        if (!empty($request)) {

            // проверить значения полей из request и сохранить в инфоблок
            if ($request['apply'] === 'Y' && $request['show_result'] !== 'Y' && $this->checkFields($request)) {

                $el = new CIBlockElement();

                // создаем новый элемент в инфоблоке Респонденты. В случае успеха получаем ID созданного элемента
                $name = $request['NAME'];
                $respondentsNewElId = $el->Add([
                    'MODIFIED_BY' => $GLOBALS['USER']->GetID(),
                    'IBLOCK_ID' => $this->arParams['IBLOCK_ID_RESPONDENTS'],
                    'ACTIVE' => 'Y',
                    'NAME' => $name,
                ]);

                if (!$respondentsNewElId) {
                    throw new Exception($el->LAST_ERROR);
                }

                // создаем новый элемент в инфоблоке Результаты опроса
                $pollsResultNewElId = $el->Add([
                    'MODIFIED_BY' => $GLOBALS['USER']->GetID(),
                    'IBLOCK_ID' => $this->arParams['IBLOCK_ID_POLLS'],
                    'ACTIVE' => 'Y',
                    'NAME' => $this->seq->randString(6),
                    'PROPERTY_VALUES'=> [
                        'SEX' => (int)$request['SEX'],
                        'AGE' => (int)$request['AGE'],
                        'CML2_RESPONDENT' => (int)$respondentsNewElId,
                        'SALARY' => (int)$request['SALARY'],
                    ],
                ]);

                if (!$pollsResultNewElId) {
                    throw new Exception($el->LAST_ERROR);
                }

                // данные записаны в оба инфоблока
                $this->success = true;
                $request = [];
            }

            // на странице вывода результатов
            if ($request['show_result'] === 'Y') {
                // числовые поля преобразуем в поля диапазона от и до
                unset($this->arParams['FIELDS'][array_search('AGE', $this->arParams['FIELDS'])]);
                unset($this->arParams['FIELDS'][array_search('SALARY', $this->arParams['FIELDS'])]);
                $this->arParams['FIELDS'] = array_merge($this->arParams['FIELDS'], ['AGE_from', 'AGE_to', 'SALARY_from', 'SALARY_to']);

                // применен фильтр
                if ($request['apply'] === 'Y') {
                    $arFilter = [];

                    // собираем фильтр для основного запроса
                    $arFilter['IBLOCK_ID'] = $this->arParams['IBLOCK_ID_RESPONDENTS'];
                    $arFilter['ACTIVE'] = 'Y';

                    if ($name = $request['NAME']) {
                        $arFilter['%NAME'] = $name;
                    }

                    // собираем фильтр для подзапроса
                    $arFilterSub = [];
                    if ($salaryFrom = $request['SALARY_from']) {
                        $arFilterSub['>=PROPERTY_SALARY'] = $salaryFrom;
                    }

                    if ($salaryTo = $request['SALARY_to']) {
                        $arFilterSub['<=PROPERTY_SALARY'] = $salaryTo;
                    }

                    if ($ageFrom = $request['AGE_from']) {
                        $arFilterSub['>=PROPERTY_AGE'] = $ageFrom;
                    }

                    if ($ageTo = $request['AGE_to']) {
                        $arFilterSub['<=PROPERTY_AGE'] = $ageTo;
                    }

                    if ((int)$request['SEX'] > 0) {
                        $arFilterSub['=PROPERTY_SEX'] = $request['SEX'];
                    }

                    // объединяем фильтры
                    if (!empty($arFilterSub)) {
                        $arFilterSub['IBLOCK_ID'] = $this->arParams['IBLOCK_ID_POLLS'];
                        $arFilterSub['ACTIVE'] = 'Y';

                        $arFilter['ID'] = CIBlockElement::SubQuery(
                            "PROPERTY_CML2_RESPONDENT",
                            $arFilterSub
                        );
                    }

                    // кол-во найденных записей
                    $cnt = CIBlockElement::GetList(
                        [],
                        $arFilter,
                        [],
                        false,
                        ['ID']
                    );

                    $this->arResult['RESULT_CNT'] = $cnt;
                    $this->success = true;
                }
            }
        }

        $this->request = $request;
    }

    /**
     * Формирование данных для шаблона здесь
     */
    private function makeResult()
    {
        // сформировать массив для вывода полей формы
        $arFields = [];
        foreach ($this->arParams['FIELDS'] as $fieldName) {

            switch ($fieldName) {
                case 'SEX':
                    $arFields[$fieldName] = [
                        'TYPE' => 'list',
                        'ITEMS' => $this->getListItems($fieldName),
                    ];
                    break;
                case 'NAME':
                    $arFields[$fieldName]['TYPE'] = 'text';
                    break;
                default:
                    $arFields[$fieldName]['TYPE'] = 'number';
            }

            $arFields[$fieldName]['VALUE'] = $this->request[$fieldName];
        }

        $this->arResult['FIELDS'] = $arFields;
        $this->arResult['SUCCESS'] = $this->success;

    }

    /**
     * Жизненный цикл компонента здесь
     *
     * @return mixed|void
     */
    public function executeComponent()
    {
        if ($this->startResultCache()) {

            try {
                $this->processRequest();
                $this->makeResult();

            } catch (Exception $e) {
                $this->errors[$e->getCode()] = $e->getMessage();
            }

            $this->formatErrors();

            $this->includeComponentTemplate();
        }
    }

    /**
     * Проверка полей на заполнение
     *
     * @param $request
     * @return bool
     */
    private function checkFields($request)
    {
        $flag = true;

        foreach ($this->arParams['FIELDS'] as $fieldName) {
            if (!$request[$fieldName]) {
                $this->errors[] = 'Не заполнено поле ' . $fieldName;
                $flag = false;
            }
        }

        return $flag;
    }

    /**
     * Сбор значений св-в типа список
     *
     * @param $fieldName
     * @return array
     */
    private function getListItems($fieldName)
    {
        $ob = CIBlockPropertyEnum::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => $this->arParams['IBLOCK_ID_POLLS'],'ACTIVE' => 'Y', 'CODE' => $fieldName]
        );
        $arProp = [];
        while ($res = $ob->GetNext()) {
            $arProp[$res['ID']] = $res['VALUE'];
        }

        return $arProp;
    }

    /**
     * Добавление ошибок в результирующий массив
     */
    private function formatErrors()
    {
        if (!empty($errors = $this->errors)) {
            $this->arResult['ERRORS'] = $errors;
        }
    }

    /**
     * Очистка от html тегов
     *
     * @param $array
     * @return mixed
     */
    private function clearTags($array)
    {
        foreach ($array as &$item) {
            $item = trim(strip_tags($item));
        }

        return $array;
    }
}
