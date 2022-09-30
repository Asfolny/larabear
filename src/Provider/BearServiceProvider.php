<?php

namespace GuardsmanPanda\Larabear\Provider;

use Carbon\CarbonImmutable;
use GuardsmanPanda\Larabear\Enum\BearSeverityEnum;
use GuardsmanPanda\Larabear\Infrastructure\App\Command\BearValidateConfigurationCommand;
use GuardsmanPanda\Larabear\Infrastructure\App\Service\BearGlobalStateService;
use GuardsmanPanda\Larabear\Infrastructure\Console\Crud\BearLogConsoleEventCreator;
use GuardsmanPanda\Larabear\Infrastructure\Console\Crud\BearLogConsoleEventUpdater;
use GuardsmanPanda\Larabear\Infrastructure\Database\Command\BearCheckForeignKeysOnSoftDeletesCommand;
use GuardsmanPanda\Larabear\Infrastructure\Error\Crud\BearLogErrorCreator;
use GuardsmanPanda\Larabear\Infrastructure\Http\Command\BearGenerateSessionKeyCommand;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use stdClass;
use Throwable;

class BearServiceProvider extends ServiceProvider {
    private array $ignoreCommands = [
        '',
        'about',
        'migrate',
        'optimize',
        'package:discover'
    ];


    public function boot(): void {
        if ($this->app->runningInConsole()) {
            $this->addEventListeners();

            $this->commands(commands: [
                BearGenerateSessionKeyCommand::class,
                BearValidateConfigurationCommand::class,
                BearCheckForeignKeysOnSoftDeletesCommand::class,
            ]);

            $this->publishes(paths: [__DIR__ . '/../../config/config.php' => $this->app->configPath(path: 'bear.php'),], groups: 'bear');
            $this->loadMigrationsFrom(paths: [__DIR__ . '/../Migration']);
        }
    }


    private function addEventListeners(): void {
        Event::listen(events: CommandStarting::class, listener: function ($event) {
            if (in_array($event->command, $this->ignoreCommands, true)) {
                return;
            }
            BearGlobalStateService::setConsoleId(consoleId: Str::uuid()->toString());
            try {
                DB::beginTransaction();
                BearLogConsoleEventCreator::create(
                    console_event_type: 'command',
                    console_command: $event->input->__toString(),
                    console_input_parameters: new stdClass(),
                    console_event_id: BearGlobalStateService::getConsoleId(),
                );
                DB::commit();
            } catch (Throwable $t) {
                DB::rollBack();
                BearLogErrorCreator::create(message: $t->getMessage(), namespace: 'larabear', key: 'log-console-command-starting', severity: BearSeverityEnum::MEDIUM, exception: $t);
            }
        });


        Event::listen(events: ScheduledTaskStarting::class, listener: static function ($event) {
                BearGlobalStateService::setConsoleId(consoleId: Str::uuid()->toString());
            try {
                DB::beginTransaction();
                BearLogConsoleEventCreator::create(
                    console_event_type: 'scheduled_task',
                    console_command: $event->command,
                    console_input_parameters: new stdClass(),
                    console_event_id: BearGlobalStateService::getConsoleId(),
                    cron_schedule_expression: $event->expression,
                    cron_schedule_timezone: $event->timezone,
                );
                DB::commit();
            } catch (Throwable $t) {
                DB::rollBack();
                BearLogErrorCreator::create(message: $t->getMessage(), namespace: 'larabear', key: 'log-console-scheduled-task-starting', severity: BearSeverityEnum::MEDIUM, exception: $t);
            }
        });


        Event::listen(events: CommandFinished::class, listener: function ($event) {
            if (in_array($event->command, $this->ignoreCommands, true)) {
                return;
            }
            try {
                DB::beginTransaction();
                $updater = BearLogConsoleEventUpdater::fromConsoleEventId(consoleEventId: BearGlobalStateService::getConsoleId());
                if ($event->exitCode === 0) {
                    $updater->setConsoleEventFinishedAt(CarbonImmutable::now());
                } else {
                    $updater->setConsoleEventFailedAt(CarbonImmutable::now());
                }
                $updater->setExecutionTimeMicroseconds((int)((microtime(as_float: true) - get_defined_constants()['LARAVEL_START']) * 1000));
                $updater->save();
                DB::commit();
            } catch (Throwable $t) {
                DB::rollBack();
                BearLogErrorCreator::create(message: $t->getMessage(), namespace: 'larabear', key: 'log-console-command-finished', severity: BearSeverityEnum::MEDIUM, exception: $t);
            }
        });


        Event::listen(events: ScheduledTaskFinished::class, listener: function ($event) {
            //dd($event);
        });
    }
}
