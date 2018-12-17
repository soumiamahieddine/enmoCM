import { Injectable } from '@angular/core';

interface listProperties {
    'id' : string,
    'groupId' : number,
    'basketId' : number,
    'page' : string,
    'onlyProcesLimit': boolean,
    'onlyNewRes': boolean,
    'withPj': boolean,
    'withNote': boolean,
    'categories' : string[],
    'priorities' : string[],
    'entities' : string[]
}

@Injectable()
export class FiltersListService {

    listsProperties: any[] = [];
    listsPropertiesIndex: number = 0;

    constructor() {
        this.listsProperties = JSON.parse(sessionStorage.getItem('propertyList'));
    }

    initListsProperties(userId: string, groupId: number, basketId: number) {
        this.listsPropertiesIndex = 0;
        let listProperties: listProperties;


        if (this.listsProperties != null) {
            this.listsProperties.forEach((element, index) => {
                if (element.id == userId && element.groupId == groupId && element.basketId == basketId) {
                    this.listsPropertiesIndex = index;
                    listProperties = element;
                }
            });
        } else {
            this.listsProperties = []; 
        }

        if (!listProperties) {
            listProperties = {
                'id' : userId,
                'groupId' : groupId,
                'basketId' : basketId,
                'page' : '0',
                'onlyProcesLimit': false,
                'onlyNewRes': false,
                'withPj': false,
                'withNote': false,
                'categories' : [],
                'priorities' : [],
                'entities' : [],
            };
            this.listsProperties.push(listProperties);
            this.saveListsProperties();
        }
        return listProperties;
    }

    updateListsPropertiesPage(page : number) {
        this.listsProperties[this.listsPropertiesIndex].page = page;
        this.saveListsProperties();
    }
    
    updateListsProperties(listProperties : any) {
        this.listsProperties[this.listsPropertiesIndex] = listProperties;
        this.saveListsProperties();
    }

    saveListsProperties() {
        sessionStorage.setItem('propertyList', JSON.stringify(this.listsProperties));
    }
    
}
