<?php
use App\Models\Setting;
use Illuminate\Support\Facades\Schedule;

try {
    $freqMin = (int) Setting::get('kizeo_frequence_min', config('vits.kizeo_frequence_min', 60));
} catch (\Throwable) {
    $freqMin = (int) config('vits.kizeo_frequence_min', 60);
}
Schedule::command('kizeo:import')
    ->everyXMinutes($freqMin)
    ->withoutOverlapping($freqMin);
