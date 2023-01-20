import { isRef, type Ref } from "vue";
import type User from "../../ts/types/User";
import type GeneralObjectInterface from "../types/GeneralObjectInterface";


export default function useHelpers() {

    function pushItem(array: Array<any>, item: any, mapFn?: Function) {
        let newArray = [...array]

        if (mapFn) {
            newArray.map(function(e){
                return mapFn(e)
            })
        }
        if (array.includes(item)) {
            return newArray
        }
        
        array.push(item)

        return array
    }

    function clearObjectProperties(object: GeneralObjectInterface): User|GeneralObjectInterface {
        let newObject = object

        if (isRef(object)) {
            newObject = object.value
        }
        for (const key in newObject) {
            if (Object.prototype.hasOwnProperty.call(newObject, key)) {
                newObject[key] = getDefaultValueBasedOnType(newObject[key]);
            }
        }

        if (isRef(object)) {
            object.value = newObject
        }

        return object
    }

    function setObjectProperties(to: GeneralObjectInterface, from: GeneralObjectInterface) {
        let newFrom = from

        if (isRef(from)) {
            newFrom = from.value
        }

        let toIsRef = isRef(to)

        for (const key in newFrom) {

            if (
                !Object.prototype.hasOwnProperty.call(newFrom, key) ||
                !Object.prototype.hasOwnProperty.call(toIsRef ? to.value : to, key)
            ) {
                continue
            }

            if (toIsRef) {
                to.value[key] = newFrom[key]

                continue
            }

            to[key] = newFrom[key]
        }

        if (toIsRef) {
            return to.value
        }

        return to
    }

    function getDefaultValueBasedOnType(variable) {
        switch (typeof variable) {
            case "boolean":
                return false;
            case "number":
                return 0;
            case "object":
                return null;
            case "string":
                return '';
            default:
                break;
        }
    }

    return {
        clearObjectProperties,
        setObjectProperties,
        pushItem
    }
}