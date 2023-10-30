<?php

namespace App\Models;

use App\Enums\RelationshipTypeEnum;
use App\Interfaces\Request;
use App\Traits\CanAddImagesTrait;
use App\Traits\CanSendAndReceiveRequestsTrait;
use App\Traits\HasAdministratorTrait;
use App\Traits\HasProfileTrait;
use App\Traits\HasProjectParticipantTrait;
use App\Traits\HasProjectAddedByTrait;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements Request
{
    use HasFactory, 
        Notifiable,
        HasProfileTrait,
        HasAdministratorTrait,
        CanAddImagesTrait,
        HasProjectAddedByTrait,
        CanSendAndReceiveRequestsTrait,
        HasProjectParticipantTrait;
    
    const MALE = 'MALE';
    const FEMALE = 'FEMALE';
    const ADULTAGE = 18;

    protected $fillable = [
        'first_name',
        'surname',
        'other_names',
        'username',
        'gender',
        'email',
        'password',
        'dob',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // start of attributes
    public function name() : Attribute
    {
        return new Attribute(
            get: function ($value, $attributes) {

                $name = "{$attributes['surname']} {$attributes['first_name']}";
                
                if (array_key_exists('other_names', $attributes) && !is_null($attributes['other_names'])) {
                    $name = $name . " {$attributes['other_names']}";
                }
                
                return $name;
            }
        );
    }
    
    public function wards() : Attribute
    {
        return new Attribute(
            get: function () {
                return User::query()
                    ->whereHas(
                        'addedToRelations',
                        function($query) {
                            $query
                                ->where('relationship_type', RelationshipTypeEnum::parent->value)
                                ->whereIsBy($this);
                        }
                    )
                    ->orWhereHas(
                        'addedByRelations',
                        function($query) {
                            $query
                                ->where('relationship_type', RelationshipTypeEnum::ward->value)
                                ->whereIsTo($this);
                        }
                    )->get();
            }
        );
    }
    
    public function parents() : Attribute
    {
        return new Attribute(
            get: function () {
                return User::query()
                    ->whereHas(
                        'addedToRelations', 
                        function($query) {
                            $query
                                ->where('relationship_type', RelationshipTypeEnum::ward->value)
                                ->whereIsBy($this);
                        }
                    )
                    ->orWhereHas(
                        'addedByRelations',
                        function($query) {
                            $query
                                ->where('relationship_type', RelationshipTypeEnum::parent->value)
                                ->whereIsTo($this);
                        }
                    )->get();
            }
        );
    }

    public function age(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes){
            
                if (in_array('dob', array_keys($attributes))) {
                    return now()->diffInYears(Carbon::parse($attributes['dob']));
                }
                
                return null;
            }
        );
    }

    public function allUserTypes() : Attribute
    {
        return new Attribute(
            get: function($value, $attributes){
                return $this->getAllUserTypes();
            }
        );
    }
    // end of attributes

    // start of relationships
    public function addedUserTypes() 
    {
        return $this->hasMany(
            related: UserType::class, 
            foreignKey: 'user_id'
        );
    }

    public function addedProjectSessions() 
    {
        return $this->hasMany(ProjectSession::class);
    }

    public function userTypes()
    {
        return $this->belongsToMany(
            related: UserType::class,
            table: 'user_user_type'
        )->withTimestamps();
    }

    public function skillTypes()
    {
        return $this->hasMany(SkillType::class);
    }

    public function addedSkills()
    {
        return $this->hasMany(Skill::class, 'user_id');
    }

    public function addedLevels()
    {
        return $this->hasMany(Level::class, 'user_id');
    }

    public function addedLevelCollections()
    {
        return $this->hasMany(LevelCollection::class, 'user_id');
    }

    public function addedCompanies()
    {
        return $this->hasMany(Company::class, 'user_id');
    }

    public function addedJobs()
    {
        return $this->hasMany(Job::class, 'user_id');
    }

    public function jobUsers()
    {
        return $this->hasMany(JobUser::class);
    }

    public function jobUserFromJobID($jobId)
    {
        return $this->jobUsers()->where('job_id', $jobId)->first();
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'performedby');
    }

    public function addedByRelations()
    {
        return $this->morphMany(Relation::class, 'by');
    }

    public function addedToRelations()
    {
        return $this->morphMany(Relation::class, 'to');
    }

    public function addedRoles()
    {
        return $this->hasMany(Role::class, 'user_id');
    }

    public function addedPermissions()
    {
        return $this->hasMany(Permission::class, 'user_id');
    }

    public function authorizations()
    {
        return $this->hasMany(Authorization::class);
    }

    public function authorized()
    {
        return $this->morphMany(Authorization::class, "authorized");
    }

    public function permissionsAuthorized()
    {
        return $this->morphedByMany(Permission::class, "authorization", "authorizations");
    }

    public function rolesAuthorized()
    {
        return $this->morphedByMany(Role::class, "authorization", "authorizations");
    }

    public function companyAuthorizables()
    {
        return $this->morphedByMany(Company::class, "authorizable", "authorizations");
    }

    public function projectAuthorizables()
    {
        return $this->morphedByMany(Project::class, "authorizable", "authorizations");
    }

    public function isAuthorizedFor(
        ?Model $authorizable = null,
        ?Model  $authorization = null,
        ?string $name = null,
        ?array $names = null,
    ) {
        $query = $this->authorized();

        if ($authorizable) $query->whereAuthorizable($authorizable);
        if ($authorization) $query->whereAuthorization($authorization);
        if ($name) $query->whereAuthorizationName($name);
        if ($names) $query->whereAuthorizationNames($names);
        
        return $query->exists();
    }

    public function isNotAuthorizedFor(
        ?Model $authorizable = null,
        ?Model  $authorization = null,
        ?string $name = null,
    ) {
        return !$this->isAuthorizedFor($authorizable, $authorization, $name);
    }

    public function hasAuthorizationWithName(string $name)
    {
        return $this->authorizations()
            ->whereAuthorizedName($name)
            ->exists();
    }
    // end of relationships

    // start of methods
    public function isAdult(): bool
    {
        return $this->age >= self::ADULTAGE;
    }

    public function isAdmin() : bool
    {
        return $this->userTypes()
            ->whereIn('name', [UserType::ADMIN, UserType::SUPERADMIN])
            ->exists();
    }

    public function isFacilitator() : bool
    {
        return $this->userTypes()
            ->where('name', UserType::FACILITATOR)
            ->exists();
    }

    public function isLearner()
    {
        return $this->isStudent();
    }

    public function isSponsor()
    {
        return $this->isDonor();
    }

    public function isStudent() : bool
    {
        return $this->userTypes()
            ->where('name', UserType::STUDENT)
            ->exists();
    }

    public function isDonor() : bool
    {
        return $this->userTypes()
            ->where('name', UserType::DONOR)
            ->exists();
    }

    public function isParent() : bool
    {
        return $this
            ->whereParent()
            ->exists();
    }

    public function isWard() : bool
    {
        return $this
            ->whereWard()
            ->exists();
    }

    public function isSuperAdmin() : bool
    {
        return $this->userTypes()
            ->where('name',UserType::SUPERADMIN)
            ->exists();
    }

    public function normalUserTypes()
    {
        return $this->userTypes()
            ->where('name', '<>', UserType::SUPERADMIN)
            ->get();
    }

    public function getAllUserTypes() : array
    {
        return $this->userTypes()
            ->pluck('name')
            ->map(fn($name)=> UserType::USABLETYPES[$name])
            ->toArray();
    }

    public function isUserType($name)
    {
        $name = strtoupper($name);

        if (!in_array($name, UserType::TYPES)) {
            return false;
        }
        
        return $this->userTypes()
            ->where('name', $name)
            ->exists();
    }

    public function hasUserTypes(array $userTypes): bool
    {
        return $this
            ->userTypes()->whereIn('name', $userTypes)
            ->exists();
    }

    public function canMakeRequestFor(): array
    {
        return [
            Project::class,
            Company::class
        ];
    }

    public function ownsCompany() : bool
    {
        return $this->addedCompanies()->exists();
    }

    public function ownsProject() : bool
    {
        return $this->addedProjects()->exists();
    }

    public function isPermittedTo(string $permit) : bool
    {
        return $this->hasAuthorizationWithName($permit);
    }

    public function memberingCompanies()
    {
        return Company::query()
            ->whereIsMember($this)
            ->get();
    }

    public function administeringCompanies()
    {
        return Company::query()
            ->whereIsOfficial($this)
            ->get();
    }

    public function companyProjectsQuery()
    {
        return Project::query()
            ->whereMemberOrAdministratorOfAddedby($this);
    }

    public function companyProjects()
    {
        return $this->companyProjectsQuery()->get();
    }
    // end of methods

    // start of scopes
    public function scopeWhereUserType($query, $name)
    {
        return $query->whereHas('userTypes', function($query) use ($name) {
            $query->where('name', $name);
        });
    }
    
    public function scopeWhereUserTypes($query, $names)
    {
        return $query->whereHas('userTypes', function($query) use ($names) {
            $query->whereIn('name', $names);
        });
    }
    
    public function scopeWhereParent($query)
    {
        return $query
            ->whereHas(
                'addedByRelations', 
                function($query) {
                    $query->where('relationship_type', RelationshipTypeEnum::parent->value);
                }
            )
            ->orWhereHas(
                'addedToRelations',
                function($query) {
                    $query->where('relationship_type', RelationshipTypeEnum::ward->value);
                }
            );
    }
    
    public function scopeWhereWard($query)
    {
        return $query
            ->whereHas(
                'addedToRelations',
                function($query) {
                    $query->where('relationship_type', RelationshipTypeEnum::parent->value);
                }
            )
            ->orWhereHas(
                'addedByRelations',
                function($query) {
                    $query->where('relationship_type', RelationshipTypeEnum::ward->value);
                }
            );
    }
    // end of scopes

    // TODO: make it possible for facilitators and admins to create users
    // for facilitators, it is possible to create them and send the project requests to them
}
