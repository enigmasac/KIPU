<?php

namespace Modules\Inventory\Listeners;

use App\Traits\Modules;
use App\Utilities\Date;
use App\Models\Document\Document;
use App\Events\Report\TotalCalculated;
use App\Events\Report\TotalCalculating;
use App\Abstracts\Listeners\Report as Listeners;

class AddTotalCalculateToReports extends Listeners
{
    use Modules;

    protected $classes = [
        'App\Reports\IncomeSummary',
        'App\Reports\ExpenseSummary',
        'App\Reports\IncomeExpenseSummary',
        'App\Reports\ProfitLoss'
    ];

    /**
     * Handle group showing event.
     *
     * @param  $event
     * @return void
     */
    public function handleTotalCalculating(TotalCalculating $event)
    {
        if (! $this->moduleIsEnabled('inventory') || 
            $this->skipThisClass($event) ||
            $this->skipRowsShowing($event, 'item')
            ) {
            return;
        }

        $event->class->backup_row_values       = $event->class->row_values;
        $event->class->backup_footer_totals    = $event->class->footer_totals;
    }

    /**
     * Handle group showing event.
     *
     * @param  $event
     * @return void
     */
    public function handleTotalCalculated(TotalCalculated $event)
    {
        if (! $this->moduleIsEnabled('inventory') || 
            $this->skipThisClass($event) ||
            $this->skipRowsShowing($event, 'item')
            ) {
            return;
        }

        $event->class->row_values       = $event->class->backup_row_values;
        $event->class->footer_totals    = $event->class->backup_footer_totals;

        foreach ($event->model as $document) {
            if (! $document instanceof Document) {
                continue;
            }   
            // Make groups extensible
            $group_field = $this->getSetting($event, 'group') . '_id';

            $date = $this->getReportFormattedDate($event, Date::parse($document->created_At));

            if (!isset($document->$group_field)) {
                continue;
            }

            $group = $document->$group_field;

            if (
                !isset($event->class->row_values[$event->table][$group])
                || !isset($event->class->row_values[$event->table][$group][$date])
                || !isset($event->class->footer_totals[$event->table][$date])
            ) {
                continue;
            }

            foreach ($document->items as $document_item) {
                $group = $document_item->item_id;

                $amount = $document_item->total;

                $type = ($document->type === Document::INVOICE_TYPE || $document->type === 'income') ? 'income' : 'expense';
    
                if (($event->check_type == false) || ($type == 'income')) {
                    $event->class->row_values[$event->table][$group][$date] += $amount;
    
                    $event->class->footer_totals[$event->table][$date] += $amount;
                } else {
                    $event->class->row_values[$event->table][$group][$date] -= $amount;
    
                    $event->class->footer_totals[$event->table][$date] -= $amount;
                }
            }
        }
    }

    public function getReportFormattedDate($event, $date)
    {
        $formatted_date = null;

        switch ($this->getSetting($event, 'period')) {
            case 'yearly':
                $financial_year = $this->getFinancialYear($event->class->year);

                if ($date->greaterThanOrEqualTo($financial_year->getStartDate()) && $date->lessThanOrEqualTo($financial_year->getEndDate())) {
                    if (setting('localisation.financial_denote') == 'begins') {
                        $formatted_date = $financial_year->getStartDate()->copy()->format($this->getYearlyDateFormat());
                    } else {
                        $formatted_date = $financial_year->getEndDate()->copy()->format($this->getYearlyDateFormat());
                    }
                }

                break;
            case 'quarterly':
                $quarters = $this->getFinancialQuarters($event->class->year);

                foreach ($quarters as $quarter) {
                    if ($date->lessThan($quarter->getStartDate()) || $date->greaterThan($quarter->getEndDate())) {
                        continue;
                    }

                    $start = $quarter->getStartDate()->format($this->getQuarterlyDateFormat($event->class->year));
                    $end = $quarter->getEndDate()->format($this->getQuarterlyDateFormat($event->class->year));

                    $formatted_date = $start . '-' . $end;
                }

                break;
            default:
                $formatted_date = $date->copy()->format($this->getMonthlyDateFormat($event->class->year));

                break;
        }

        return $formatted_date;
    }

    public function getSetting($event, $name, $default = '')
    {
        return $event->class->model->settings->$name ?? $default;
    }
}
