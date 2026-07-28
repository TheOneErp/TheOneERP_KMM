<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckboxesIn implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($options)
    {
        $this->options = $options;
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
        $status = true;
        foreach($value as $checkedOption){
            if(!in_array($checkedOption,$this->options)){
                $status = false;
            }
        }
        return $status || (is_array($value) && count($value) == 0);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return resolve("validationTranslations")['in'];
    }
}
