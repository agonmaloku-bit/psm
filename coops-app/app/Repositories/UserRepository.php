<?php

namespace App\Repositories;

use App\Enums\Roles;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Spatie\Permission\Models\Role;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getAll()
    {
        $query = $this->model
            ->with('roles', 'department', 'department.company')
            ->orderByDesc('id');

        if (request()->has('search_text') && request()->input('search_text') !== 'null' && request()->input('search_text') !== '') {
            $searchText = request()->input('search_text');
            $query->where(function ($q) use ($searchText) {
                $q->where('name', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('email', 'LIKE', '%' . $searchText . '%');
            });
        }

        if (request()->has('page')) {
            return $query->paginate(10);
        }

        return $query->get();
    }

    public function findById($id)
    {
        return $this->model
            ->with(
                'roles',
                'department',
                'company',
                'department.company',
                'roles.permissions',
                'permissions',
                'appRoles.businessApp',
                'appRoles.role',
                'appRoles.department'
            )
            ->whereId($id)
            ->first();
    }

    public function getAllUsersByRoles($roles)
    {
        return $this->model
            ->role($roles)
            ->with('roles', 'department', 'roles.permissions', 'permissions')
            ->orderByDesc('created_at')
            ->paginate(10);
    }
    // public function getAllUsersOfDepartartment($id)
    // {
    //     return $this->model
    //         ->departament($id)
    //         ->with('department')
    //         // ->orderByDesc('created_at')
    //         ->paginate(10);
    // }
    public function getAllUsersByDepartmentIdAndRole($id, $role)
    {
        return $this->model
            ->where('department_id', $id)
            ->role($role)
            ->get();
    }

    public function findUserByIdAndRole($id, $roles)
    {
        return $this->model
            ->whereId($id)
            ->role($roles)
            ->first();
    }
}