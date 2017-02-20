declare module Tops {
    export interface IPeanutClient {
        showServiceMessages(messages:IServiceMessage[]): void;
        hideServiceMessages(): void;
        showError(errorMessage:string): void;
        showMessage(messageText:string): void;
        showWarning(messageText:string): void;
        initialize(successFunction?:() => void);
        showWaiter(message:string) : void;
        hideWaiter() : void;
        showProgress(message: string) : void;
        setProgress(count: number) : void;
        loadComponent(name: string, successFunction?: () => void);
        loadCSS(name: string, successFunction?: () => void);
        loadJS(names: any, successFunction?: () => void);
        loadResources(names: any, successFunction?: () => void);
        loadComponentInstance(name: string, getVmInstance : () => any, finalFunction?: () => void);
        bindComponent(name: string, getVmInstance : () => any, finalFunction?: () => void);
        bindSection(containerName: string, context?: any);
        bindNode(containerName: string, context?: any);
        registerComponent(name: string, vm: any, successFunction?: () => void);
        registerAndBindComponent(name: string, vm: any, successFunction?: () => void);
        showDefaultSection();
        bindDefaultSection();
        peanut: any; // Tops.Peanut;
        viewModel: any;
        serviceUrl: string;
        // applicationPath: string;
    }

    export interface IViewModel {

    }

    export interface IMessageManager {
        addMessage : (message:string, messageType:number) => void;
        setMessage  : (message:string, messageType:number) => void;
        clearMessages : (messageType:number)=> void;
        setServiceMessages : (messages:Tops.IServiceMessage[]) => void;
    }


    export interface IMainViewModel {
        init(successFunction?: () => void);
    }

    export interface IServiceMessage {
        MessageType: number;
        Text: string;
    }

    export interface IServiceResponse {
        Messages: IServiceMessage[];
        Result: number;
        Value: any;
        Data: any;
    }

    export interface INameValuePair {
        Name: string;
        Value: any;
    }

    export interface IKeyValuePair {
        Key: any;
        Value: any;
    }

    export interface ILookupItem {
        Key: any;
        Text: string;
        Description: string;
    }

    export interface IHousingTypeLookupItem extends ILookupItem {
        category: number;
    }

    export interface IListItem {
        Text: string;
        Value: any;
        Description: string;
    }

    export interface IIndexedItem extends IListItem {
        Key: any;
    }

    export interface IInputItem extends IListItem {
        Value: any;
        ErrorMessage: string;
    }

    export interface IIndexedInput extends IInputItem {
        Key: any;
    }

    export interface IEventSubscriber {
        handleEvent : (eventName: string, data?: any) => void;
    }

}