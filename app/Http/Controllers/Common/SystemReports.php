<?php

namespace App\Http\Controllers\Common;

use App\Abstracts\Http\Controller;
use App\Http\Requests\Common\ReportShow as ShowRequest;
use App\Utilities\Reports as Utility;

class SystemReports extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-common-reports')->only(['show', 'print', 'pdf', 'export']);
    }

    public function show(string $report, ShowRequest $request)
    {
        $class = $this->resolveReportClass($report);
        $model = Utility::makeSystemReportModel($class);
        $instance = Utility::getClassInstance($model);

        if (empty($instance)) {
            abort(404);
        }

        return $instance->show();
    }

    public function print(string $report, ShowRequest $request)
    {
        $class = $this->resolveReportClass($report);
        $model = Utility::makeSystemReportModel($class);
        $instance = Utility::getClassInstance($model);

        if (empty($instance)) {
            abort(404);
        }

        return $instance->print();
    }

    public function pdf(string $report, ShowRequest $request)
    {
        $class = $this->resolveReportClass($report);
        $model = Utility::makeSystemReportModel($class);
        $instance = Utility::getClassInstance($model);

        if (empty($instance)) {
            abort(404);
        }

        return $instance->pdf();
    }

    public function export(string $report, ShowRequest $request)
    {
        $class = $this->resolveReportClass($report);
        $model = Utility::makeSystemReportModel($class);
        $instance = Utility::getClassInstance($model);

        if (empty($instance)) {
            abort(404);
        }

        return $instance->export();
    }

    protected function resolveReportClass(string $slug): string
    {
        $class = Utility::getSystemClassBySlug($slug, false);

        if (! $class) {
            abort(404);
        }

        if (Utility::cannotShow($class)) {
            abort(403);
        }

        return $class;
    }
}
