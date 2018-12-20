import { Injectable } from '@angular/core';

interface listProperties {
    'id' : string,
    'groupId' : number,
    'basketId' : number,
    'page' : string,
    'order' : string,
    'orderDir' : string,
    'delayed': boolean,
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
                'order' : '',
                'orderDir' : 'DESC',
                'delayed': false,
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

    getUrlFilters () {
        let filters = '';
        if (this.listsProperties[this.listsPropertiesIndex].delayed) {
            filters += '&delayed=true';
        }
        if (this.listsProperties[this.listsPropertiesIndex].order.length > 0) {
            filters += '&order='+this.listsProperties[this.listsPropertiesIndex].order + ' ' + this.listsProperties[this.listsPropertiesIndex].orderDir;
        }
        if (this.listsProperties[this.listsPropertiesIndex].categories.length > 0) {
            let cat: any[] = [];
            this.listsProperties[this.listsPropertiesIndex].categories.forEach((element: any) => {
                cat.push(element.id);
            });

            filters += '&categories='+cat.join(','); 
        }
        if (this.listsProperties[this.listsPropertiesIndex].priorities.length > 0) {
            let prio: any[] = [];
            this.listsProperties[this.listsPropertiesIndex].priorities.forEach((element: any) => {
                prio.push(element.id);
            });

            filters += '&priorities='+prio.join(','); 
        }
        
        return filters;
    }
    
}
