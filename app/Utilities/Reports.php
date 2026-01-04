<?php

namespace App\Utilities;

use App\Events\Report\ClassesCreated as ReportClassesCreated;
use App\Models\Common\Report;
use App\Models\Module\Module;
use App\Traits\Modules;
use Illuminate\Support\Str;

class Reports
{
    use Modules;

    public static function getClasses($check_permission = true)
    {
        $classes = [];

        $list = [
            'App\Reports\IncomeSummary',
            'App\Reports\ExpenseSummary',
            'App\Reports\IncomeExpenseSummary',
            'App\Reports\TaxSummary',
            'App\Reports\ProfitLoss',
            'App\Reports\DiscountSummary',
        ];

        Module::enabled()->each(function ($module) use (&$list) {
            $m = module($module->alias);

            if (! $m || $m->disabled() || empty($m->get('reports'))) {
                return;
            }

            $list = array_merge($list, (array) $m->get('reports'));
        });

        // Added New Event
        $report_classes = collect($list);

        event(new ReportClassesCreated($report_classes));

        $list = $report_classes->all();

        foreach ($list as $class) {
            if (! class_exists($class) || ($check_permission && static::cannotRead($class))) {
                continue;
            }

            $classes[$class] = static::getDefaultName($class);
        }

        return $classes;
    }

    public static function getClassInstance($model, $load_data = true)
    {
        if (is_string($model)) {
            $model = Report::where('class', $model)->first();
        }

        if ((! $model instanceof Report) || ! class_exists($model->class)) {
            return false;
        }

        if (! empty($model) && ($model->alias != 'core') && (new static)->moduleIsDisabled($model->alias)) {
            return false;
        }

        $class = $model->class;

        return new $class($model, $load_data);
    }

    public static function canShow($class)
    {
        return (static::isModuleEnabled($class) && static::canRead($class));
    }

    public static function cannotShow($class)
    {
        return ! static::canShow($class);
    }

    public static function canRead($class)
    {
        return user()->can(static::getPermission($class));
    }

    public static function cannotRead($class)
    {
        return ! static::canRead($class);
    }

    public static function getPermission($class)
    {
        $arr = explode('\\', $class);

        $prefix = 'read-';

        // Add module
        if ($alias = static::getModuleAlias($arr)) {
            $prefix .= $alias . '-';
        }

        $prefix .= 'reports-';

        $class_name = end($arr);

        $permission = $prefix . Str::kebab($class_name);

        return str_replace('--', '-', $permission);
    }

    public static function getDefaultName($class)
    {
        return (new $class())->getDefaultName();
    }

    public static function getSystemSlug($class): string
    {
        $base = Str::kebab(class_basename($class));
        $alias = static::getModuleAlias($class);

        if (! empty($alias)) {
            return Str::kebab($alias) . '-' . $base;
        }

        return $base;
    }

    public static function getSystemReports($check_permission = true): array
    {
        $reports = [];

        foreach (static::getClasses($check_permission) as $class => $name) {
            $reports[static::getSystemSlug($class)] = [
                'class' => $class,
                'name' => $name,
            ];
        }

        return $reports;
    }

    public static function getSystemClassBySlug(string $slug, bool $check_permission = true): ?string
    {
        $reports = static::getSystemReports($check_permission);

        return $reports[$slug]['class'] ?? null;
    }

    public static function makeSystemReportModel(string $class): Report
    {
        $report = new Report();

        $report->class = $class;
        $report->name = static::getDefaultName($class);
        $report->description = '';
        $report->settings = static::getSystemSettings($class);

        $report->setAttribute('system_slug', static::getSystemSlug($class));

        return $report;
    }

    protected static function getSystemSettings(string $class): object
    {
        $instance = new $class();
        $group = $instance->group ?: 'category';

        return (object) [
            'group' => $group ?: 'category',
            'period' => 'quarterly',
            'basis' => 'accrual',
            'withholding' => 'no',
        ];
    }

    public static function isModuleEnabled($class)
    {
        if (! $alias = static::getModuleAlias($class)) {
            return true;
        }

        if (module_is_enabled($alias)) {
            return true;
        }

        return false;
    }

    public static function isModuleDisabled($class)
    {
        return ! static::isModuleEnabled($class);
    }

    public static function isModule($class)
    {
        $arr = is_array($class) ? $class : explode('\\', $class);

        return (strtolower($arr[0]) == 'modules');
    }

    public static function isNotModule($class)
    {
        return ! static::isModule($class);
    }

    public static function getModuleAlias($class)
    {
        if (static::isNotModule($class)) {
            return false;
        }

        $arr = is_array($class) ? $class : explode('\\', $class);

        return Str::kebab($arr[1]);
    }
}
