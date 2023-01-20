import type Job from "./Job";
import type Project from "./Project";
import type Skill from "./Skill";

export default interface User {
    skills?: Array<Skill>,
    jobs?: Array<Job>,
    wards?: Array<Skill>,
    parents?: Array<User>,
    projects?: Array<Project>,
}