<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Экзамен Битрикс задание к уроку 3");
?>

<? $APPLICATION->IncludeComponent(
    "tsybayev.exam:exam.test",
    "",
    [
        'IBLOCK_ID_RESPONDENTS' => 4,
        'IBLOCK_ID_POLLS' => 5,
        'FIELDS' => [
            'NAME',
            'SEX',
            'AGE',
            'SALARY',
        ],
    ],
    false
); ?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>