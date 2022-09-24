interface UserType {
    id?: number;
    name: string;
    usableName?: string;
    description?: string|null;
}

export default UserType

class UserTypeClass {
    public static readonly admin = 'admin'
    public static readonly superAdmin = 'super admin'
    public static readonly parent = 'parent'
    public static readonly student = 'student'
    public static readonly facilitator = 'facilitator'
    public static readonly donor = 'donor'

    public static readonly types = ['admin', 'super admin', 'parent', 'student', 'facilitator, donor']
    
}

export {UserTypeClass}