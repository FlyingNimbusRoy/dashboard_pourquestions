<?php

namespace App\Filament\Resources\Questions\Support;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/** Delete callbacks for Filament actions that go through a CRUD service and surface its refusal message. */
class ServiceDelete
{
    /** For DeleteAction::using(): returns false (and notifies) when the service refuses. */
    public static function single(string $service): Closure
    {
        return function (Model $record) use ($service): bool {
            try {
                app($service)->delete($record);

                return true;
            } catch (ValidationException $e) {
                Notification::make()->danger()->title('Not deleted')->body(collect($e->errors())->flatten()->first())->persistent()->send();

                return false;
            }
        };
    }

    /** For DeleteBulkAction::using(): deletes what it can, reports the refusals. */
    public static function bulk(string $service): Closure
    {
        return function (Collection $records) use ($service): void {
            $refused = [];
            foreach ($records as $record) {
                try {
                    app($service)->delete($record);
                } catch (ValidationException $e) {
                    $refused[] = '#'.$record->getKey().': '.collect($e->errors())->flatten()->first();
                }
            }
            if ($refused !== []) {
                Notification::make()->danger()->title(count($refused).' not deleted')->body(implode("\n", $refused))->persistent()->send();
            }
        };
    }
}
