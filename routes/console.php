<?php

use Illuminate\Support\Facades\Schedule;

// ===== نسخ احتياطية =====
Schedule::command('backup:database --keep=7')->dailyAt('02:00');
Schedule::command('backup:database --keep=4')->weeklyOn(0, '03:00');

// ===== تنبيهات يومية =====
// إرسال تنبيهات المخزون الناقص والأقساط المتأخرة كل يوم الساعة 8 صباحاً
Schedule::command('alerts:send')->dailyAt('08:00');