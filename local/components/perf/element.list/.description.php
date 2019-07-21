<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("IBLOCK_ELEMENTSL_TEMPLATE_NAME"),// Элементы раздела
	"DESCRIPTION" => GetMessage("IBLOCK_ELEMENTSL_TEMPLATE_DESCRIPTION"), // Выводит элементы раздела с указанным набором свойств, цен и т.д.
	"ICON" => "/images/cat_list.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 30,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => GetMessage("T_IBLOCK_DESC_CATALOG"), // Каталог
			"SORT" => 30,
			"CHILD" => array(
				"ID" => "catalog_cmpx",
			),
		),
	),
);

?>