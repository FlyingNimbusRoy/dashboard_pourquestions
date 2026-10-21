<?php

namespace App\Services\Crud;

use App\Models\GamepackPurchase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GamepackPurchaseService extends CrudService
{
    protected string $model = GamepackPurchase::class;

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (filled($filters['user_id'] ?? null)) {
            $query->where('user_id', $filters['user_id']);
        }
        if (filled($filters['gamepack_id'] ?? null)) {
            $query->where('gamepack_id', $filters['gamepack_id']);
        }
        if (isset($filters['opened']) && $filters['opened'] !== '') {
            $query->where('opened', filter_var($filters['opened'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['access_through_admin_grant']) && $filters['access_through_admin_grant'] !== '') {
            $query->where('access_through_admin_grant', filter_var($filters['access_through_admin_grant'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    protected function beforeSave(Model $model, array $data): array
    {
        $data['access_through_admin_grant'] = true;

        return $data;
    }
}
