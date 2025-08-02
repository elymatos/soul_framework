<?php

namespace App\Auth;

use App\Models\User as UserModel;
use Illuminate\Auth\SessionGuard as BaseSessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Timebox;
use Symfony\Component\HttpFoundation\Request;

class SessionGuard extends BaseSessionGuard
{
    /**
     * Create a new authentication guard.
     */
    public function __construct(
        string $name,
        UserProvider $provider,
        Session $session,
        ?Request $request = null,
        ?Timebox $timebox = null,
        bool $rehashOnLogin = true
    ) {
        parent::__construct($name, $provider, $session, $request, $timebox, $rehashOnLogin);
    }

    /**
     * Get the currently authenticated user.
     */
    public function user(): ?Authenticatable
    {
        if ($this->loggedOut) {
            return null;
        }

        // If we've already resolved the user for the current request, return it
        if (!is_null($this->user)) {
            return $this->user;
        }

        // Check for user in session using our custom session key
        $sessionUser = $this->session->get('user');
        
        if ($sessionUser && isset($sessionUser->idUser)) {
            // Convert repository user to UserModel and set as authenticated
            $this->user = UserModel::fromRepositoryUser($sessionUser);
            
            // Mark as authenticated in parent class
            $this->setUser($this->user);
            
            return $this->user;
        }

        // Fallback to default Laravel session authentication
        $id = $this->session->get($this->getName());

        if (!is_null($id) && $this->user = $this->provider->retrieveById($id)) {
            $this->fireAuthenticatedEvent($this->user);
        }

        return $this->user;
    }

    /**
     * Log a user into the application.
     */
    public function login(Authenticatable $user, $remember = false): void
    {
        // Store in both Laravel session and our custom session
        parent::login($user, $remember);
        
        // Also update our custom session format for backward compatibility
        if ($user instanceof UserModel) {
            $repositoryUser = $user->toRepositoryUser();
            $this->session->put('user', $repositoryUser);
        }
    }

    /**
     * Log the user out of the application.
     */
    public function logout(): void
    {
        try {
            // Try Laravel's logout (handles remember tokens and other cleanup)
            parent::logout();
        } catch (\Exception $e) {
            // Fallback: Manual cleanup if cookie operations fail
            // This can happen in CLI context or when cookies aren't properly initialized
            
            // Clear the current user
            $this->user = null;
            $this->loggedOut = true;
            
            // Clear Laravel auth session data
            $this->session->remove($this->getName());
            
            // Regenerate session for security
            $this->session->regenerate(true);
            
            // Fire logout event manually since parent::logout() failed
            if (isset($this->events)) {
                $this->events->dispatch(new \Illuminate\Auth\Events\Logout($this->name, $this->user));
            }
        }
        
        // Always clear our custom session data
        $this->session->forget([
            'user',
            'idLanguage',
            'userLevel',
            'isAdmin',
            'isMaster',
            'isManager',
            'isAnno'
        ]);
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     */
    public function viaRemember(): bool
    {
        // Check if user exists in custom session (not via remember token)
        $sessionUser = $this->session->get('user');
        if ($sessionUser && isset($sessionUser->idUser)) {
            return false; // Session-based, not remember token
        }
        
        return parent::viaRemember();
    }
}