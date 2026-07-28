<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Utils\FileUtil;

class FileCheck implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($dataset, $field)
    {
        $this->dataset = $dataset;
        $this->field = $field;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $file = FileUtil::getFileFromSaveRequest($this->dataset['tmpID'], $this->field['field_id']);
        if ($file == null) return true;
        return FileUtil::checkUploadFile($this->dataset,$this->field);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return resolve("validationTranslations")['file'];
    }
}
