<?php declare(strict_types=1);

namespace GuardsmanPanda\Larabear\Infrastructure\Console\Crud;

use Carbon\CarbonInterface;
use GuardsmanPanda\Larabear\Infrastructure\Console\Model\BearConsoleEvent;
use GuardsmanPanda\Larabear\Infrastructure\Database\Service\BearDatabaseService;

final class BearConsoleEventUpdater {
    public function __construct(private readonly BearConsoleEvent $model) {
        BearDatabaseService::mustBeProperHttpMethod(verbs: ['POST', 'PUT', 'PATCH', 'DELETE']);
    }

    public static function fromConsoleEventId(string $consoleEventId): BearConsoleEventUpdater {
        return new BearConsoleEventUpdater(model: BearConsoleEvent::where(column: 'console_id', operator: '=', value: $consoleEventId)->sole());
    }

    public function setConsoleCommand(string $console_command): self {
        $this->model->console_command = $console_command;
        return $this;
    }

    public function setConsoleEventStartedAt(CarbonInterface $console_event_started_at): self {
        if ($console_event_started_at->toIso8601String() === $this->model->console_event_started_at->toIso8601String()) {
            return $this;
        }
        $this->model->console_event_started_at = $console_event_started_at;
        return $this;
    }

    public function setConsoleId(string $console_id): self {
        $this->model->console_id = $console_id;
        return $this;
    }

    public function setCronScheduleExpression(string|null $cron_schedule_expression): self {
        $this->model->cron_schedule_expression = $cron_schedule_expression;
        return $this;
    }

    public function setCronScheduleTimezone(string|null $cron_schedule_timezone): self {
        $this->model->cron_schedule_timezone = $cron_schedule_timezone;
        return $this;
    }

    public function setConsoleEventFinishedAt(CarbonInterface|null $console_event_finished_at): self {
        if ($console_event_finished_at?->toIso8601String() === $this->model->console_event_finished_at?->toIso8601String()) {
            return $this;
        }
        $this->model->console_event_finished_at = $console_event_finished_at;
        return $this;
    }

    public function setConsoleEventFailedAt(CarbonInterface|null $console_event_failed_at): self {
        if ($console_event_failed_at?->toIso8601String() === $this->model->console_event_failed_at?->toIso8601String()) {
            return $this;
        }
        $this->model->console_event_failed_at = $console_event_failed_at;
        return $this;
    }

    public function setExecutionTimeMicroseconds(int|null $execution_time_microseconds): self {
        $this->model->execution_time_microseconds = $execution_time_microseconds;
        return $this;
    }

    public function setConsoleEventOutput(string|null $console_event_output): self {
        $this->model->console_event_output = $console_event_output;
        return $this;
    }

    public function update(): BearConsoleEvent {
        $this->model->save();
        return $this->model;
    }
}