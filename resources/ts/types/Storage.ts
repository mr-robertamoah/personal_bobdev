export default interface Storage {
    type: string,

    instance: any,

    possibleTypes: string[],

    constructor(type: string): void,
    
    setUpStorageBasedOnType(): void,

    setItem(key: string, value: string | {}): boolean,
    
    getItem(key: string): string,
    
    removeItem(key: string): boolean,
    
    clear(): boolean
}