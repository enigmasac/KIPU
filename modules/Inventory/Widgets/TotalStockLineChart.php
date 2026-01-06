<?php
namespace Modules\Inventory\Widgets;

use Akaunting\Apexcharts\Chart;
use App\Abstracts\Widget;
use App\Utilities\Date;
use App\Models\Document\DocumentItem;
use App\Traits\Currencies;
use App\Traits\DateTime;

class TotalStockLineChart extends Widget
{
    use Currencies, DateTime;

    public $default_name = 'inventory::widgets.tracked_items_cash_flow';

    public $description = 'widgets.description.cash_flow';

    public $report_class = 'Modules\Inventory\Reports\Item';

    public $start_date;

    public $end_date;

    public $period;

    public function show()
    {
        $this->setFilter();

        $labels = $this->getLabels();

        $income = array_values($this->calculateTotals('invoice'));
        $expense = array_values($this->calculateTotals('bill'));

        $colors = $this->getColors();

        $options = [
            'chart' => [
                'stacked'           => true,
            ],
            'plotOptions' => [
                'bar' => [
                    'columnWidth'   => '40%',
                ],
            ],
            'legend' => [
                'position'          => 'top',
            ],
        ];

        $chart = new Chart();

        $chart->setType('line')
            ->setOptions($options)
            ->setLabels(array_values($labels))
            ->setColors($colors)
            ->setDataset(trans('general.incoming'), 'column', $income)
            ->setDataset(trans('general.outgoing'), 'column', $expense);

        $totals = [
            'incoming'  => array_sum($income),
            'outgoing'  => abs(array_sum($expense)),
        ];

        return $this->view('inventory::widgets.line_chart', [
            'chart' => $chart,
            'totals' => $totals,
        ]);
    }

    public function setFilter(): void
    {
        $financial_start = $this->getFinancialStart()->format('Y-m-d');

        // check and assign year start
        if (($year_start = Date::today()->startOfYear()->format('Y-m-d')) !== $financial_start) {
            $year_start = $financial_start;
        }

        $this->start_date = Date::parse(request('start_date', $year_start));
        $this->end_date = Date::parse(request('end_date', Date::parse($year_start)->addYear(1)->subDays(1)->format('Y-m-d')));
        $this->period = request('period', 'month');
    }

    public function getLabels(): array
    {
        $range = request('range', 'custom');

        $start_month = $this->start_date->month;
        $end_month = $this->end_date->month;

        // Monthly
        $labels = [];

        $s = clone $this->start_date;

        if ($range == 'last_12_months') {
            $end_month   = 12;
            $start_month = 0;
        } elseif ($range == 'custom') {
            $end_month   = $this->end_date->diffInMonths($this->start_date);
            $start_month = 0;
        }

        for ($j = $end_month; $j >= $start_month; $j--) {
            $labels[$end_month - $j] = $s->format('M Y');

            if ($this->period == 'month') {
                $s->addMonth();
            } else {
                $s->addMonths(3);
                $j -= 2;
            }
        }

        return $labels;
    }

    public function getColors(): array
    {
        return [
            '#8bb475',
            '#fb7185',
        ];
    }

    private function calculateTotals($type): array
    {
        $totals = [];

        $date_format = 'Y-m';

        if ($this->period == 'month') {
            $n = 1;
            $start_date = $this->start_date->format($date_format);
            $end_date = $this->end_date->format($date_format);
            $next_date = $start_date;
        } else {
            $n = 3;
            $start_date = $this->start_date->quarter;
            $end_date = $this->end_date->quarter;
            $next_date = $start_date;
        }

        $s = clone $this->start_date;

        // $totals[$start_date] = 0;
        while ($next_date <= $end_date) {
            $totals[$next_date] = 0;

            if ($this->period == 'month') {
                $next_date = $s->addMonths($n)->format($date_format);
            } else {
                if (isset($totals[4])) {
                    break;
                }

                $next_date = $s->addMonths($n)->quarter;
            }
        }

        $document_items = $this->applyFilters(DocumentItem::where('type', $type), ['date_field' => 'created_at'])->get();

        $this->setTotals($totals, $document_items, $date_format);

        return $totals;
    }

    private function setTotals(&$totals, $document_items, $date_format): void
    {
        $precision = currency()->getPrecision();

        foreach ($document_items as $document_item) {
            if ($this->period == 'month') {
                $i = Date::parse($document_item->created_at)->format($date_format);
            } else {
                $i = Date::parse($document_item->created_at)->quarter;
            }

            if (!isset($totals[$i])) {
                continue;
            }

            $totals[$i] += $this->convertToDefault($document_item->total, $document_item->document->currency_code, $document_item->document->currency_rate);
        }
    }
}
