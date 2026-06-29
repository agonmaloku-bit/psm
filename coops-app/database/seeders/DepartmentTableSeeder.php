<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = Company::all();
        foreach ($companies as $company) {
            for ($i = 1; $i <= 4; $i++) {
                Department::create([
                    "name" => "Department {$i}",
                    "company_id" => $company->id
                ]);
            }
        }
    }
}
