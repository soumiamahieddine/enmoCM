import { Injectable } from '@angular/core';
import { FunctionsService } from './functions.service';

interface ListProperties {
    'page': number;
    'pageSize': number;
    'criteria': any[];
    'filters': any[];
    'order': string;
    'orderDir': string;
}

@Injectable()
export class CriteriaSearchService {

    listsProperties: ListProperties = {
        page : 0,
        pageSize : 0,
        order: 'creation_date',
        orderDir: 'DESC',
        criteria: [],
        filters: []
    };

    constructor(
        public functions: FunctionsService
    ) { }

    initListsProperties(userId: number) {

        const crit = JSON.parse(sessionStorage.getItem('criteriaSearch_' + userId));

        if (crit !== null) {
            this.listsProperties = JSON.parse(sessionStorage.getItem('criteriaSearch_' + userId));
        }

        return this.listsProperties;
    }

    updateListsPropertiesPage(page: number) {
        this.listsProperties.page = page;
        this.saveListsProperties();
    }

    updateListsPropertiesPageSize(pageSize: number) {
        this.listsProperties.pageSize = pageSize;
        this.saveListsProperties();
    }

    updateListsPropertiesCriteria(criteria: any) {
        this.listsProperties.criteria = criteria;
        this.saveListsProperties();
    }

    updateListsProperties(listProperties: ListProperties) {
        this.listsProperties = listProperties;
        this.saveListsProperties();
    }

    saveListsProperties() {
        sessionStorage.setItem('criteriaSearch', JSON.stringify(this.listsProperties));
    }

    getCriteria() {
        return this.listsProperties.criteria;
    }

    formatDatas(data: any) {
        console.log(data);

        Object.keys(data).forEach(key => {
            if (['folders', 'tags'].indexOf(key) > -1 || ['select', 'radio', 'checkbox'].indexOf(data[key].type) > -1) {
                data[key].values = data[key].values.map((val: any) => val.id);
            } else if (data[key].type === 'date') {
                data[key].values.start = this.functions.formatSerializedDateToDateString(data[key].values.start);
                data[key].values.end = this.functions.formatSerializedDateToDateString(data[key].values.end);
            }
            console.log(data[key].values);
            // delete data[key].type;
            /*data[key].values = data[key].values.map((item: any) => item.id);*/
        });

        return data;
    }
}
