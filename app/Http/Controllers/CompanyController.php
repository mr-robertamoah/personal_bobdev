<?php

namespace App\Http\Controllers;

use App\DTOs\CompanyDTO;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\RequestResource;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function create(CreateCompanyRequest $request)
    {
        DB::beginTransaction();

        $company = (new CompanyService)->createCompany(
            CompanyDTO::new()->fromArray([
                'user' => $request->user(),
                'ownerId' => $request->ownerId,
                'name' => $request->name,
                'alias' => $request->alias,
                'about' => $request->about,
            ])
        );

        DB::commit();

        return response()->json([
            'status' => true,
            'company' => new CompanyResource($company)
        ]);
    }

    public function update(UpdateCompanyRequest $request)
    {
        DB::beginTransaction();

        $company = (new CompanyService)->updateCompany(
            CompanyDTO::new()->fromArray([
                'user' => $request->user(),
                'companyId' => $request->company_id,
                'name' => $request->name,
                'about' => $request->about,
            ])
        );

        DB::commit();

        return response()->json([
            'status' => true,
            'company' => new CompanyResource($company)
        ]);
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();

        $company = (new CompanyService)->deleteCompany(
            CompanyDTO::new()->fromArray([
                'user' => $request->user(),
                'companyId' => $request->company_id,
            ])
        );

        DB::commit();

        return response()->json([
            'status' => true
        ]);
    }

    public function addMembers(Request $request)
    {
        DB::beginTransaction();

        $requests = (new CompanyService)->sendMembershipRequest(
            CompanyDTO::new()->fromArray([
                'user' => $request->user(),
                'companyId' => $request->company_id,
                'memberships' => $request->memberships,
            ])
        );

        DB::commit();

        return response()->json([
            'status' => true,
            'requests' => RequestResource::collection($requests)
        ]);
    }

    public function removeMembers(Request $request)
    {
        DB::beginTransaction();

        $company = (new CompanyService)->removeMembers(
            CompanyDTO::new()->fromArray([
                'user' => $request->user(),
                'companyId' => $request->company_id,
                'memberships' => $request->memberships,
            ])
        );

        DB::commit();

        return response()->json([
            'status' => true,
            'company' => new CompanyResource($company)
        ]);
    }

    public function leave(Request $request)
    {
        DB::beginTransaction();

        $company = (new CompanyService)->leave(
            CompanyDTO::new()->fromArray([
                'user' => $request->user(),
                'companyId' => $request->company_id,
                'relationshipType' => $request->relationshipType,
            ])
        );

        DB::commit();

        return response()->json([
            'status' => true,
            'company' => new CompanyResource($company)
        ]);
    }
}
