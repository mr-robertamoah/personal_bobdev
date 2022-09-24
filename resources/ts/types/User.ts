import type UserType from "./UserType";

export default interface User {
    // firstName: string,
    // surname: string,
    // otherNames: string,
    name: string,
    username: string,
    email: string,
    gender: string,
    url?: string,
    userTypes?: Array<UserType>
}