<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('kizeo:import')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
