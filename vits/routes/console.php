<?php
use App\Models\Setting;
use Illuminate\Support\Facades\Schedule;

try {
    $freqMin = (int) Setting::get('kizeo_frequence_min', config('vits.kizeo_frequence_min', 60));
} catch (\Throwable) {
    $freqMin = (int) config('vits.kizeo_frequence_min', 60);
}
Schedule::command('kizeo:import')
    ->cron("*/{$freqMin} * * * *")
    ->withoutOverlapping($freqMin);

Schedule::command('kizeo:reimport', [
    now()->subDay()->format('Y-m-d'),
    now()->subDay()->format('Y-m-d'),
])
    ->dailyAt('00:30')
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/kizeo_nuit.log'));
