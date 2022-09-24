import type User from "./User";

export default interface Company {
    // firstName: string,
    // surname: string,
    // otherNames: string,
    name: string,
    ownerName: string,
    email: string,
    url?: string,
    members?: Array<User>
}