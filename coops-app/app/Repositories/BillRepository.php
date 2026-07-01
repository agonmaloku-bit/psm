<?php

namespace App\Repositories;

use App\Models\Bill;
use Carbon\Carbon;
use App\Enums\Roles;
use App\Models\User;
use App\Repositories\Contracts\BillRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class BillRepository extends BaseRepository implements BillRepositoryInterface
{
    private $userRepository;
    public function __construct(
        Bill $model,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct($model);
        $this->userRepository = $userRepository;
    }

    /**
     * Apply department-based visibility scope.
     * A user can see a bill if:
     * 1. They created it
     * 2. They are in the same department as the creator
     * 3. The bill is assigned to their department
     */
    private function applyDepartmentVisibility($query, $user)
    {
        $userDeptId = $user->department_id;
        $userId = $user->id;

        $query->where(function ($q) use ($userId, $userDeptId) {
            // 1. User created the bill
            $q->where('created_by', $userId)
              // 2. Bill is assigned to user's department
              ->orWhere('assigned_dep_id', $userDeptId)
              // 3. Bill creator is in the same department as user
              ->orWhereIn('created_by', function ($sub) use ($userDeptId) {
                  $sub->select('id')
                      ->from('users')
                      ->where('department_id', $userDeptId);
              });
        });

        return $query;
    }

    public function getAll($request)
    {
        $query = $this->model->withTables()->orderByDesc('id');

        if (request()->has('search_text') && request()->input('search_text') !== "null") {
            $searchText = request()->input('search_text');

            $query->where(function ($q) use ($searchText) {
                $q->where('id', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('type', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('value', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('bill_no', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('supplier', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('created_by', 'LIKE', '%' . $searchText . '%');
            });
        }

        if (request()->has('search_status') && request()->input('search_status') !== null) {
            if (request()->input('search_status') == 0) {

                if (request()->has('start_date') && request()->input('start_date') !== null) {
                    $query->where('created_at', '>=', request()->input('start_date'));
                }

                if (request()->has('end_date') && request()->input('end_date') !== null) {
                    $query->where('created_at', '<=', request()->input('end_date'));
                }
                if (request()->has('suppliers') && request()->input('suppliers') !== null) {
                    $query->where('supplier', request()->input('suppliers'));
                }
                if ($request->has('page')) {
                    return $query->paginate(15);
                } else {
                    return $query->get();
                }
            } else {
                $query->where('status', request()->input('search_status'));
            }
        }

        if (request()->has('start_date') && request()->input('start_date') !== null) {
            $query->where('created_at', '>=', request()->input('start_date'));
        }

        if (request()->has('end_date') && request()->input('end_date') !== null) {
            $query->where('created_at', '<=', request()->input('end_date'));
        }

        if (request()->has('suppliers') && request()->input('suppliers') !== null) {
            $query->where('supplier', request()->input('suppliers'));
        }

        if ($request->has('page')) {
            return $query->paginate(15);
        } else {
            return $query->get();
        }
    }


    // public function getAllByResponnsiblePersonId($id)
    // {
    //     $user = $this->userRepository->findById(auth()->user()->id);

    //     $query = $this->model
    //         ->withTables()
    //         ->orderByDesc('id')
    //         ->with('createdBy')
    //         ->where('created_by', $user->id)
    //         ->orWhere('assigned_dep_id', $user->department_id);


    //     if (request()->has('search_text')) {
    //         if (request()->input('search_text') != "null") {
    //             $query->where(function ($q) use ($user) {
    //                 $q->where('created_by', $user->id);
    //                 $q->where('id', 'LIKE', '%' . request()->input('search_text') . '%');
    //                 $q->orWhere('name', 'LIKE', '%' . request()->input('search_text') . '%');
    //                 $q->orWhere('type', 'LIKE', '%' . request()->input('search_text') . '%');
    //                 $q->orWhere('value', 'LIKE', '%' . request()->input('search_text') . '%');
    //                 $q->orWhere('bill_no', 'LIKE', '%' . request()->input('search_text') . '%');
    //                 $q->orWhere('supplier', 'LIKE', '%' . request()->input('search_text') . '%');
    //                 $q->orWhere('description', 'LIKE', '%' . request()->input('search_text') . '%');
    //                 $q->orWhere('created_at', 'LIKE', '%' . request()->input('search_text') . '%');
    //             });
    //         }
    //     }

    //     if (request()->has('page')) {
    //         return $query->paginate(10);
    //     } else {
    //         return $query->get();
    //     }

    //     return $this->model
    //         ->withTables()
    //         ->orderByDesc('id')
    //         ->get();
    // }

    public function getAllByResponsiblePersonId($id)
    {
        $user = $this->userRepository->findById(auth()->user()->id);
        $query = $this->model
            ->withTables()
            ->with('createdBy')
            ->orderByDesc('id');

        $this->applyDepartmentVisibility($query, $user);

        if (request()->has('search_status') && request()->input('search_status') !== null) {
            if (request()->input('search_status') != 0) {
                $query->where('status', request()->input('search_status'));
            }
        }

        if (request()->has('page')) {
            return $query->paginate(15);
        } else {
            return $query->get();
        }
    }

    public function getAllByProcurementOfficerId($id, $request)
    {
        $user = $this->userRepository->findById(auth()->user()->id);
        $query = $this->model
            ->withTables()
            ->with('createdBy')
            ->orderByDesc('id');

        $this->applyDepartmentVisibility($query, $user);

        if (request()->has('search_text') && request()->input('search_text') !== "null") {
            $searchText = request()->input('search_text');
            $query->where(function ($q) use ($searchText) {
                $q->where('id', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('type', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('value', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('bill_no', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('supplier', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('created_by', 'LIKE', '%' . $searchText . '%');
            });
        }
        if (request()->has('search_status') && request()->input('search_status') !== null) {
            if (request()->input('search_status') != 0) {
                $query->where('status', request()->input('search_status'));
            }
        }
        if ($request->has('start_date') && $request->input('start_date') !== null) {
            $query->where('created_at', '>=', $request->input('start_date'));
        }
        if ($request->has('end_date') && $request->input('end_date') !== null) {
            $query->where('created_at', '<=', $request->input('end_date'));
        }
        if (request()->has('page')) {
            return $query->paginate(15);
        } else {
            return $query->get();
        }
    }
    public function getAllByDirectorDepartmentId($id, $request)
    {
        $user = $this->userRepository->findById(auth()->user()->id);
        $query = $this->model
            ->withTables()
            ->with('createdBy')
            ->orderByDesc('id');

        $this->applyDepartmentVisibility($query, $user);

        if ($request->has('search_text') && $request->input('search_text') !== "null") {
            $searchText = $request->input('search_text');
            $query->where(function ($q) use ($searchText) {
                $q->where('id', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('type', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('value', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('bill_no', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('supplier', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('created_by', 'LIKE', '%' . $searchText . '%');
            });
        }
        if (request()->has('search_status') && request()->input('search_status') !== null) {
            if (request()->input('search_status') != 0) {
                $query->where('status', request()->input('search_status'));
            }
        }
        if ($request->has('start_date') && $request->input('start_date') !== null) {
            $query->where('created_at', '>=', $request->input('start_date'));
        }
        if ($request->has('end_date') && $request->input('end_date') !== null) {
            $query->where('created_at', '<=', $request->input('end_date'));
        }
        if (request()->has('suppliers') && request()->input('suppliers') !== null) {
            $query->where('supplier', request()->input('suppliers'));
        }
        if ($request->has('page')) {
            return $query->paginate(15);
        } else {
            return $query->get();
        }
    }
    public function getAllByExecutiveDirector($id, $request)
    {
        $user = $this->userRepository->findById(auth()->user()->id);
        $userDeptId = $user->department_id;
        $userId = $user->id;

        $query = $this->model
            ->withTables()
            ->with('createdBy')
            ->where(function ($q) use ($userId, $userDeptId) {
                // Department visibility (same as other roles)
                $q->where('created_by', $userId)
                  ->orWhere('assigned_dep_id', $userDeptId)
                  ->orWhereIn('created_by', function ($sub) use ($userDeptId) {
                      $sub->select('id')
                          ->from('users')
                          ->where('department_id', $userDeptId);
                  })
                  // Executive Director also sees bills awaiting CEO approval
                  ->orWhereIn('status', [2, 3]);
            })
            ->orderByDesc('id');

        if (request()->has('search_text') && request()->input('search_text') !== "null") {
            $searchText = request()->input('search_text');

        $query->where(function ($q) use ($searchText) {
            $q->where('id', 'LIKE', '%' . $searchText . '%')
                ->orWhere('type', 'LIKE', '%' . $searchText . '%')
                ->orWhere('value', 'LIKE', '%' . $searchText . '%')
                ->orWhere('bill_no', 'LIKE', '%' . $searchText . '%')
                ->orWhere('supplier', 'LIKE', '%' . $searchText . '%')
                ->orWhere('description', 'LIKE', '%' . $searchText . '%')
                ->orWhere('created_at', 'LIKE', '%' . $searchText . '%')
                ->orWhere('created_by', 'LIKE', '%' . $searchText . '%');
        });
        }

        if (request()->has('search_status') && request()->input('search_status') !== null) {
            if (request()->input('search_status') != 0) {
                $query->where('status', request()->input('search_status'));
            }
        }

        if (request()->has('suppliers') && request()->input('suppliers') !== null) {
            $query->where('supplier', request()->input('suppliers'));
        }

        if (request()->has('start_date') && request()->input('start_date') !== null) {
            $query->whereDate('created_at', '>=', request()->input('start_date'));
        }

        if (request()->has('end_date') && request()->input('end_date') !== null) {
            $query->whereDate('created_at', '<=', request()->input('end_date'));
        }

        if ($request->has('page')) {
            return $query->paginate(15);
        } else {
            return $query->get();
        }
    }
// Zyra Ligjore / LegalOffice
    public function getAllByLegalOffice($id, $request)
    {
        $user = $this->userRepository->findById(auth()->user()->id);
        $query = $this->model
            ->withTables()
            ->with('createdBy')
            ->orderByDesc('id');

        $this->applyDepartmentVisibility($query, $user);
        if (request()->has('search_text') && request()->input('search_text') !== "null") {
            $searchText = request()->input('search_text');

            $query->where(function ($q) use ($searchText) {
                $q->where('id', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('type', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('value', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('bill_no', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('supplier', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('created_by', 'LIKE', '%' . $searchText . '%');
            });
        }

        if (request()->has('search_status') && request()->input('search_status') !== null) {
            if (request()->input('search_status') == 0) {

                if (request()->has('start_date') && request()->input('start_date') !== null) {
                    $query->where('created_at', '>=', request()->input('start_date'));
                }

                if (request()->has('end_date') && request()->input('end_date') !== null) {
                    $query->where('created_at', '<=', request()->input('end_date'));
                }
                if (request()->has('suppliers') && request()->input('suppliers') !== null) {
                    $query->where('supplier', request()->input('suppliers'));
                }
                if ($request->has('page')) {
                    return $query->paginate(15);
                } else {
                    return $query->get();
                }
            } else {
                $query->where('status', request()->input('search_status'));
            }
        }

        if (request()->has('start_date') && request()->input('start_date') !== null) {
            $query->where('created_at', '>=', request()->input('start_date'));
        }

        if (request()->has('end_date') && request()->input('end_date') !== null) {
            $query->where('created_at', '<=', request()->input('end_date'));
        }

        if (request()->has('suppliers') && request()->input('suppliers') !== null) {
            $query->where('supplier', request()->input('suppliers'));
        }

        if ($request->has('page')) {
            return $query->paginate(15);
        } else {
            return $query->get();
        }
    }
    public function findById($id)
    {
        return $this->model
            // ->with('departments')
            // ->whereId($id)
            //    ->first();
            ->withTables()
            ->find($id);
    }
    public function getAllBills($userId)
    {
        $user = Auth::user();
        if ($user->hasRole([Roles::SUPER_ADMIN, Roles::ADMIN])) {
            return $this->model->count();
        }
        $userDeptId = $user->department_id;
        return $this->model
            ->where(function ($q) use ($userId, $userDeptId) {
                $q->where('created_by', $userId)
                  ->orWhere('assigned_dep_id', $userDeptId)
                  ->orWhereIn('created_by', function ($sub) use ($userDeptId) {
                      $sub->select('id')->from('users')->where('department_id', $userDeptId);
                  });
            })
            ->count();
    }
    public function getAllActiveBills($userId)
    {
        $user = Auth::user();
        if ($user->hasRole([Roles::SUPER_ADMIN, Roles::ADMIN])) {
            return $this->model->where('status', 3)->count();
        }
        $userDeptId = $user->department_id;
        return $this->model
            ->where('status', 3)
            ->where(function ($q) use ($userId, $userDeptId) {
                $q->where('created_by', $userId)
                  ->orWhere('assigned_dep_id', $userDeptId)
                  ->orWhereIn('created_by', function ($sub) use ($userDeptId) {
                      $sub->select('id')->from('users')->where('department_id', $userDeptId);
                  });
            })
            ->count();
    }
    public function findByIdAndSupplier($id, $supplied_id)
    {
        return $this->model->where('bill_no', $id)->where('supplier', $supplied_id)->count();
    }
}
