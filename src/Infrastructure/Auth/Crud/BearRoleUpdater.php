<?php

namespace GuardsmanPanda\Larabear\Infrastructure\Auth\Crud;

use GuardsmanPanda\Larabear\Infrastructure\Database\Service\BearDatabaseService;
use GuardsmanPanda\Larabear\Infrastructure\Auth\Model\BearRole;

class BearRoleUpdater {
    public function __construct(private readonly BearRole $model) {
        BearDatabaseService::mustBeInTransaction();
        BearDatabaseService::mustBeProperHttpMethod(verbs: ['PATCH']);
    }

    public static function fromRoleSlug(string $role_slug): self {
        return new self(model: BearRole::findOrFail(id: $role_slug));
    }


    public function setRoleDescription(string|null $role_description): self {
        $this->model->role_description = $role_description;
        return $this;
    }

    public function update(): BearRole {
        $this->model->save();
        return $this->model;
    }
}
