<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class UsageReport extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'month_year',
        'total_units_billed',
        'total_electric_usage',
        'total_water_usage',
        'total_others',
        'total_revenue_expected',
    ];

    public function calculateMonthlyStats($month) 
    {
        $invoices = Invoice::where('billing_period_start', 'like', "$month%")->get();

        if ($invoices->isEmpty()) {
            return false;
        }

        $grandTotalRevenue = $invoices->sum('total_amount');
        $unitIds = $invoices->pluck('unit_id')->unique();

        $totalElectricityUsage = \App\Models\MeterReading::whereIn('meter_id', function($query) use ($unitIds) {
                $query->select('id')
                    ->from('utility_meters')
                    ->whereIn('unit_id', $unitIds)
                    ->where('meter_type', 'electricity'); 
            })
            ->where('created_at', 'like', "$month%")
            ->sum('reading_value'); 

        $totalWaterUsage = \App\Models\MeterReading::whereIn('meter_id', function($query) use ($unitIds) {
                $query->select('id')
                    ->from('utility_meters')
                    ->whereIn('unit_id', $unitIds)
                    ->where('meter_type', 'water');
            })
            ->where('created_at', 'like', "$month%")
            ->sum('reading_value');

        $this->month_year = $month;
        $this->total_units_billed = $invoices->count();
        
        $this->total_electric_usage = $totalElectricityUsage; 
        $this->total_water_usage = $totalWaterUsage;
        
        $this->total_revenue_expected = $grandTotalRevenue;

        $this->save();

        return true;
    }
}