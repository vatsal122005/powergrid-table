<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class ProductPolicy
{
    /**
     * Super Admin always allowed
     */
    private function isSuperAdmin(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    /**
     * Admin role check
     */
    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * User role check
     */
    private function isNormalUser(User $user): bool
    {
        return $user->role === 'user';
    }

    /**
     * Guest role check
     */
    private function isGuest(User $user): bool
    {
        return $user->role === 'guest';
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($this->isSuperAdmin($user) || $this->isAdmin($user)) {
            return Response::allow();
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product)
    {
        Log::info('ProductPolicy::view called with user: ' . $user->id . ' and product: ' . $product->id);

        if ($this->isSuperAdmin($user) || $this->isAdmin($user)) {
            return Response::allow();
        }

        if ($this->isNormalUser($user) && $user->id === $product->user_id) {
            return Response::allow();
        }

        return Response::deny(__('messages.unauthorized'));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        Log::info('ProductPolicy::create called with user: ' . $user->id);

        if ($this->isSuperAdmin($user) || $this->isAdmin($user) || $this->isNormalUser($user)) {
            Log::info('ProductPolicy::create allows user: ' . $user->id);

            return Response::allow();
        }

        Log::info('ProductPolicy::create denies user: ' . $user->id);

        return Response::deny(__('messages.unauthorized'));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product)
    {
        Log::info("ProductPolicy::update called with current user id: {$user->id}, product owner user id: {$product->user_id}");

        if ($this->isSuperAdmin($user) || $this->isAdmin($user)) {
            return Response::allow();
        }

        if ($this->isNormalUser($user) && $user->id === $product->user_id) {
            return Response::allow();
        }

        return Response::deny(__('messages.unauthorized'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product)
    {
        Log::info('ProductPolicy::delete called with user: ' . $user->id . ' and product: ' . $product->id);

        if ($this->isSuperAdmin($user)) {
            return Response::allow();
        }

        if ($this->isAdmin($user)) {
            return Response::allow();
        }

        if ($this->isNormalUser($user) && $user->id === $product->user_id) {
            return Response::allow();
        }

        return Response::deny(__('messages.unauthorized'));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product)
    {
        return $this->isSuperAdmin($user)
            ? Response::allow()
            : Response::deny(__('messages.unauthorized'));
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product)
    {
        return $this->isSuperAdmin($user)
            ? Response::allow()
            : Response::deny(__('messages.unauthorized'));
    }
}
