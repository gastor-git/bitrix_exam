<?php

namespace Tsybayev\Exam;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Type;

class TestTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'tsybayev_exam_testtable';
    }

    public static function getMap()
    {
        return [
            //ID
            new Entity\IntegerField(
                'ID',
                ['primary' => true, 'autocomplete' => true]
            ),
            //Название
            new Entity\StringField(
                'NAME',
                ['required' => true]
            ),
            //timestamp
            new Entity\DatetimeField(
                'TIMESTAMP_X',
                ['default_value' => new Type\DateTime]
            ),
        ];
    }
}
