import { Component, OnInit, Input } from '@angular/core';
import { LANG } from '../../../translate.component';
import { NotificationService } from '../../../notification.service';
import { HttpClient } from '@angular/common/http';

declare function $j(selector: any): any;

@Component({
    selector: 'app-maarch-paraph',
    templateUrl: "maarch-paraph.component.html",
    styleUrls: ['maarch-paraph.component.scss'],
    providers: [NotificationService],
})
export class MaarchParaphComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    currentAccount: any = null;
    usersWorkflowList: any[] = [];
   
    @Input('additionalsInfos') additionalsInfos: any;
    @Input('externalSignatoryBookDatas') externalSignatoryBookDatas: any;

    constructor(public http: HttpClient, private notify: NotificationService) { }

    ngOnInit(): void { }

    checkValidParaph() {
        if (!this.externalSignatoryBookDatas.processingUser || 
            this.additionalsInfos.users.length == 0 || 
            (this.externalSignatoryBookDatas.objectSent == 'attachment' && this.additionalsInfos.attachments.length == 0) || 
            (this.externalSignatoryBookDatas.objectSent == 'mail' && this.additionalsInfos.mails.length == 0)) {
            return true;
        } else {
            return false;
        }
    }

    getRessources() {
        if (this.externalSignatoryBookDatas.objectSent == 'attachment') {
            return this.additionalsInfos.attachments.map((e: any) => { return e.res_id; });
        } else if (this.externalSignatoryBookDatas.objectSent == 'mail') {
            return this.additionalsInfos.mails.map((e: any) => { return e.res_id; });
        }
    }

    getDatas() {
        return this.externalSignatoryBookDatas;
    }
}
