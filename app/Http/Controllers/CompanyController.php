<?php

namespace App\Http\Controllers;

use App\Actions\ApiErrorHandlingAction;
use App\DTOs\CompanyDTO;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\GetCompaniesRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyMemberResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\DetailedCompanyResource;
use App\Http\Resources\ProjectResource;
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
    
    public function getCompany(Request $request)
    {
        try {
            $company = CompanyService::new()->getCompany(
                CompanyDTO::new()->fromArray([
                    "user" => $request->user(),
                    "companyId" => $request->company_id,
                ])
            );
            
            return response()->json([
                "status" => true,
                "company" => new DetailedCompanyResource($company)
            ]);
        } catch (\Throwable $th) {
            // throw $th;
            return ApiErrorHandlingAction::make()
                ->execute($th);
        }
    }
    
    public function getCompanies(Request $request)
    {
        try {
            $companies = CompanyService::new()->getCompanies(
                CompanyDTO::new()->fromArray([
                    "name" => $request->name,
                    "like" => $request->like,
                    "ownerId" => $request->owner_id,
                    "officialId" => $request->official_id,
                    "memberId" => $request->member_id,
                    "relationshipType" => $request->relationship_type,
                    "page" => $request->page ?: null,
                ])
            );
            
            return CompanyResource::collection($companies);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
    
    public function getMembers(Request $request)
    {
        try {
            $members = CompanyService::new()->getMembers(
                CompanyDTO::new()->fromArray([
                    "user" => $request->user(),
                    "companyId" => $request->company_id,
                    "type" => $request->type,
                ])
            );
            
            return response()->json([
                "status" => true,
                "members" => CompanyMemberResource::collection($members)
            ]);
        } catch (\Throwable $th) {
            // throw $th;
            return ApiErrorHandlingAction::make()
                ->execute($th);
        }
    }
    
    public function getCompanyProjects(Request $request)
    {
        try {
            $members = CompanyService::new()->getCompanyProjects(
                CompanyDTO::new()->fromArray([
                    "user" => $request->user(),
                    "companyId" => $request->company_id,
                    "type" => $request->type,
                ])
            );
            
            return response()->json([
                "status" => true,
                "projects" => ProjectResource::collection($members)
            ]);
        } catch (\Throwable $th) {
            // throw $th;
            return ApiErrorHandlingAction::make()
                ->execute($th);
        }
    }
}
