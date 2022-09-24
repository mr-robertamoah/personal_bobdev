export default interface AuthErrorInterface {
    firstName?: string|null,
    sirname?: string|null,
    lastName?: string|null,
    otherNames?: string|null,
    username: string|null,
    email: string|null,
    male?:  boolean,
    female?:  boolean,
    password:  string|null,
    passwordConfirmation:  string|null,
    [index: string]: string|null|undefined|boolean
}