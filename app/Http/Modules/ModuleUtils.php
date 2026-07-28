<?php

namespace App\Http\Modules;

class ModuleUtils
{
    static public function getAllModules()
    {
        $modules = [
            ModuleBase::class
        ];
        $returnModulesList = [];
        foreach ($modules as $module) {
            $returnModulesList[] = [
                'name' => $module::moduleName,
                'code' => $module::moduleCode,
                'description' => $module::moduleDescription,
                'headFields' => $module::headFields,
                'bodyFields' => $module::bodyFields,
                'class' => $module
            ];
        }
        return $returnModulesList;
    }
    static public function getModules($moduleCode)
    {
        $modules = ModuleUtils::getAllModules();
        $filteredModules = array_filter($modules, function ($module) use ($moduleCode) {
            return $module->moduleCode == $moduleCode;
        });
        if (count($filteredModules) > 0) {
            return $filteredModules[0];
        }
        return null;
    }
}
