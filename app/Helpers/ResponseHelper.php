<?php

namespace App\Helpers;


class ResponseHelper
{
    /**
     * @return string[]
     */
    public static function stored(): array
    {
        return [
            "message" => "Successfully Created"
        ];
    }

    /**
     * @param $model
     * @return string[]
     */
    public static function updated($model): array
    {
        return [
            "message" => $model->wasChanged() ? "Successfully Updated" : "No changes were made"
        ];
    }
}
