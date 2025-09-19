<?php

namespace App\Auth;

use App\Models\User as UserModel;
use App\Repositories\User as UserRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class SessionUserProvider implements UserProvider
{
    /**
     * The Eloquent user model.
     */
    protected string $model;

    /**
     * Create a new session user provider.
     */
    public function __construct(string $model)
    {
        $this->model = $model;
    }

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        // First check if user is in session (for currently authenticated user)
        $sessionUser = session('user');
        if ($sessionUser && isset($sessionUser->idUser) && $sessionUser->idUser == $identifier) {
            return UserModel::fromRepositoryUser($sessionUser);
        }

        // Fallback to repository lookup
        try {
            $repositoryUser = UserRepository::byId($identifier);
            if ($repositoryUser) {
                return UserModel::fromRepositoryUser($repositoryUser);
            }
        } catch (\Exception $e) {
            // User not found
        }

        return null;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        // Not used in session-based authentication
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Not used in session-based authentication
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials) || (! isset($credentials['login']) && ! isset($credentials['email']))) {
            return null;
        }

        // Use existing repository logic to find user
        try {
            $query = [];

            if (isset($credentials['login'])) {
                $query = ['login', '=', $credentials['login']];
            } elseif (isset($credentials['email'])) {
                $query = ['email', '=', $credentials['email']];
            }

            $repositoryUser = \App\Database\Criteria::one('user', $query);
            if ($repositoryUser) {
                $fullUser = UserRepository::byId($repositoryUser->idUser);

                return UserModel::fromRepositoryUser($fullUser);
            }
        } catch (\Exception $e) {
            // User not found
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (! isset($credentials['password'])) {
            return false;
        }

        $password = $credentials['password'];

        // Check if password is already MD5 hashed (for internal auth)
        if (strlen($password) === 32 && ctype_xdigit($password)) {
            // Password is already MD5 hashed
            return $user->getAuthPassword() === $password;
        }

        // Hash the password and compare
        return $user->getAuthPassword() === md5($password);
    }

    /**
     * Rehash the user's password if required and supported.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Not implemented for MD5-based authentication
        // This method is required by Laravel 11 but not needed for our use case
    }

    /**
     * Get the currently authenticated user from session.
     */
    public function getAuthenticatedUser(): ?Authenticatable
    {
        $sessionUser = session('user');
        if ($sessionUser && isset($sessionUser->idUser)) {
            return UserModel::fromRepositoryUser($sessionUser);
        }

        return null;
    }

    /**
     * Set the authenticated user in session and Laravel Auth.
     */
    public function setAuthenticatedUser(object $repositoryUser): void
    {
        // Update session (maintain existing behavior)
        session(['user' => $repositoryUser]);

        // Convert to Eloquent model for Laravel Auth
        $userModel = UserModel::fromRepositoryUser($repositoryUser);

        // Manually set in Laravel Auth
        auth()->setUser($userModel);
    }
}
