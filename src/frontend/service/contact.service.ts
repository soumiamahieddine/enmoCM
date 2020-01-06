import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../app/translate.component';
import { tap, catchError, map } from 'rxjs/operators';
import { of } from 'rxjs';

@Injectable()
export class ContactService {

    lang: any = LANG;

    constructor(public http: HttpClient) { }

    getFillingColor(thresholdLevel: 'first' | 'second' | 'third') {
        if (thresholdLevel === 'first') {
            return '#E81C2B';
        } else if (thresholdLevel === 'second') {
            return '#F4891E';
        } else if (thresholdLevel === 'third') {
            return '#0AA34F';
        } else {
            return '';
        }
    }

    formatCivilityObject(civility: any) {
        if (!this.empty(civility)) {
            return civility
        } else {
            return {
                label:'',
                abbreviation:''
            }
        }
    }

    formatFillingObject(filling: any) {
        if (!this.empty(filling)) {
            return {
                rate: filling.rate,
                color:  this.getFillingColor(filling.thresholdLevel)
            }
        } else {
            return {
                rate:'',
                color:''
            }
        }
    }

    empty(value: any) {
        if (value !== null && value !== '' && value !== undefined) {
            return false;
        } else {
            return true;
        }
    }
}
