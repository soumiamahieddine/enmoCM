import { Injectable } from '@angular/core';

interface privilege {
    'id': string, // identifier
    'label': string, // title
    'comment': string, // description
    'type' : 'menu' | 'admin' | 'use', // menu => show in menu, admin => can be managed in administration, if type use => only use in code (no interface)
    'route': string, // navigate to interface (if type admin or menu)
    'style': string, //icone used interface (if type admin or menu)
    'angular': boolean //to navigate in V1 <=>V2
}

@Injectable()
export class PrivilegeService {

    private privileges: privilege[] = [];

    constructor() { }

    getPrivileges() {
        return this.privileges;
    }
}
