interface ApiService {
    init(baseURL: string) : void,

    setHeadersPostToFormData() : void,

    setHeadersPostToDefault() : void,

    setHeadersAuthorization() : void,

    removeHeadersAuthorization() : void,

    get(url: string , data?: any) : object | any[],

    post(url:string, data: object, hasMultipart: boolean) : object | any[],

    put(url: string, data: object) : object | any[],

    delete(url: string, data?: object) : object | any[],

    custom(data: object): any,
}

export default ApiService