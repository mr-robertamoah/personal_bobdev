
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
    age?: string|number,
    userTypes?: Array<string>
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