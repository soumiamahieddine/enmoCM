import { Injectable } from '@angular/core';
import { LANG } from '../app/translate.component';

@Injectable()
export class FunctionsService {

    lang: any = LANG;

    constructor() { }

    empty(value: string) {
        if (value === null || value === undefined) {
            return true;

        } else if (Array.isArray(value)) {
            if (value.length > 0) {
                return false;
            } else {
                return true;
            }
        } else if (String(value) !== '') {
            return false;
        } else {
            return true;
        }
    }

    formatFrenchDateToTechnicalDate(date: string) {
        if (!this.empty(date)) {
            let arrDate = date.split('-');
            arrDate = arrDate.concat(arrDate[arrDate.length-1].split(' '));
            arrDate.splice(2,1);
    
            const formatDate = `${arrDate[2]}-${arrDate[1]}-${arrDate[0]} ${arrDate[3]}`;
    
            return formatDate;
        } else {
            return date;
        }
    }

}
