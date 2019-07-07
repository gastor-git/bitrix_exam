<?php

namespace Tsybayev\Exam;

use CUserTypeString;
use CUserTypeManager;
use Bitrix\Main\Loader;
use CMedialib;
use CMedialibCollection;

IncludeModuleLangFile(__FILE__);

class CUserTypeMediaLibraryCollection extends CUserTypeString
{
    const USER_TYPE_ID = "media_library_collection";

    public function GetUserTypeDescription()
    {
        return [
            "USER_TYPE_ID" => static::USER_TYPE_ID,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => GetMessage("USER_TYPE_MLC_DESCRIPTION"),
            "BASE_TYPE" => CUserTypeManager::BASE_TYPE_STRING,
            "VIEW_CALLBACK" => [__CLASS__, 'GetPublicView'],
            "EDIT_CALLBACK" => [__CLASS__, 'GetPublicEdit'],
        ];
    }

    public function PrepareSettings($arUserField)
    {
        return [];
    }

    public function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
    {
        return '';
    }

    public function GetEditFormHTML($arUserField, $arHtmlControl) {
        $strReturn = '';

        try {
            if (!empty($collections = self::getMediaCollections())) {
                $strReturn .= '
                <select name="'.$arHtmlControl["NAME"].'">
                    <option value="">'. GetMessage('USER_TYPE_MLC_EMPTY') .'</option>
                ';

                foreach ($collections as $collection) {
                    $selected = ($collection['ID'] === $arHtmlControl["VALUE"])  ? "selected" : "";
                    $strReturn .= '
                    <option value="' . $collection['ID'] . '" ' . $selected . '>' . $collection['NAME'] . '</option>
                    ';
                }

                $strReturn .= '
                </select>
                ';
            }
        } catch (\Exception $e) {
            $strReturn = $e->getMessage();
        }

        return $strReturn;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\LoaderException
     */
    private static function getMediaCollections()
    {
        Loader::includeModule('fileman');

        CMedialib::Init();
        $mediaCollections = CMedialibCollection::GetList([
            'arFilter' => ['ACTIVE' => 'Y'],
            'arOrder' => ['NAME' => 'ASC']
        ]);

        return $mediaCollections;
    }
}
