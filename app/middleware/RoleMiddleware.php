<?php
namespace App\Middleware;

class RoleMiddleware
{
    /** @var string[] Set by Router before handle() — see note below */
    public static array $roles = [];

    public function handle(): void
    {
        // Roles are injected via static for simplicity in this phase.
        // In later phases we'll refactor the Router to pass params.
        if (!has_role(...(self::$roles ?: ['admin']))) {
            http_response_code(403);
            die('Forbidden');
        }
    }
}