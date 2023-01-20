<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum RelationshipTypeEnum: string
{
    use EnumTrait;
    
    case ward = 'WARD';
    case parent = 'PARENT';
    case companyAdministrator = 'COMPANYADMINISTRATOR';
    case companyMember = 'COMPANYMEMBER';

    const COMPANYMEMBERALIASES = ['member', 'companymember',];
    const COMPANYADMINISTRATORALIASES = ['administrator', 'companyadministrator'];
    const COMPANYRELATIONSHIPALIASES = [
        ...self::COMPANYMEMBERALIASES, ...self::COMPANYADMINISTRATORALIASES
    ];

    public static function companyRelationships(): array
    {
        return [
            self::companyAdministrator->value,
            self::companyMember->value,
        ];
    }

    public static function userRelationships(): array
    {
        return [
            self::parent->value,
            self::ward->value,
        ];
    }

    public static function companyRelationshipFromString(string $type): string
    {
        return match(strtolower($type)) {
            'administrator' => self::companyAdministrator->value,
            'companyadministrator' => self::companyAdministrator->value,
            default => self::companyMember->value
        };
    }

    public static function isValidCompanyRelationship(string $type): bool
    {
        return in_array(
            strtolower($type), self::COMPANYRELATIONSHIPALIASES
        );
    }
}