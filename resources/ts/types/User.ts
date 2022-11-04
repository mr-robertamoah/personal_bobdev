import type UserType from "./UserType";

export default interface User {
    id: string|number,
    firstName?: string,
    surname?: string,
    otherNames?: string,
    name?: string,
    username: string,
    email: string,
    gender: string,
    url?: string,
    userTypes?: Array<UserType>
}

let defaultUser = {
    id: 0,
    firstName: '',
    surname: '',
    otherNames: '',
    name: '',
    username: '',
    email: '',
    gender: '',
    url: '',
    userTypes: []
}

export {defaultUser}