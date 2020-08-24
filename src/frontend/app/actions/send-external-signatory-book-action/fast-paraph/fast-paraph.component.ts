import { Component, OnInit, Input } from '@angular/core';
import { LANG } from '../../../translate.component';
import { TranslateService } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';

@Component({
    selector: 'app-fast-paraph',
    templateUrl: 'fast-paraph.component.html',
    styleUrls: ['fast-paraph.component.scss'],
})
export class FastParaphComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    currentAccount: any = null;
    usersWorkflowList: any[] = [];

    injectDatasParam = {
        resId: 0,
        editable: true
    };

    @Input() additionalsInfos: any;
    @Input() externalSignatoryBookDatas: any;

    constructor(public translate: TranslateService, public http: HttpClient) { }

    ngOnInit(): void {
    }

    isValidParaph() {
        if (this.additionalsInfos.attachments.length === 0) {
            return false;
        } else {
            return true;
        }
    }

    getRessources() {
        return this.additionalsInfos.attachments.map((e: any) => e.res_id);
    }

    getDatas() {
        return this.externalSignatoryBookDatas;
    }
}
