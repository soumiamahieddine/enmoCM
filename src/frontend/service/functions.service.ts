import { Injectable } from '@angular/core';
import { LANG } from '../app/translate.component';

@Injectable()
export class FunctionsService {

    lang: any = LANG;

    constructor() { }

    empty(value: any) {
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

            if (this.empty(arrDate[3])) {
                arrDate[3] = '00:00:00';
            }
     
            const formatDate = `${arrDate[2]}-${arrDate[1]}-${arrDate[0]} ${arrDate[3]}`;
    
            return formatDate;
        } else {
            return date;
        }
    }

    formatDateObjectToFrenchDateString(date: Date, limitMode: boolean = false) {
        if (date !== null) {
            let day = date.getDate();
            let month = date.getMonth() + 1;
            let year = date.getFullYear();
            let limit = '';
            if (limitMode) {
                limit = ' 23:59:59';
            }
            return `${('00' + day).slice(-2)}-${('00' + month).slice(-2)}-${year}${limit}`;
        } else {
            return date;
        }
    }
}
