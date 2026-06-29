<?php

namespace App\Repositories;

use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{
    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }

    public function getAll()
    {
        $query = $this->model->orderByDesc('id');

        if (request()->has('search_text') && request()->input('search_text') !== 'null' && request()->input('search_text') !== '') {
            $searchText = request()->input('search_text');
            $query->where(function ($q) use ($searchText) {
                $q->where('name', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('bussines_no', 'LIKE', '%' . $searchText . '%');
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
            ->whereId($id)
            ->first();
    }
}
