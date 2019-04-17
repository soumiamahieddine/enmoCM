import { Component, OnInit, Input } from '@angular/core';
import { LANG } from '../../../translate.component';
import { NotificationService } from '../../../notification.service';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { FormControl } from '@angular/forms';
import { startWith, map } from 'rxjs/operators';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';

declare function $j(selector: any): any;

@Component({
    selector: 'app-x-paraph',
    templateUrl: "x-paraph.component.html",
    styleUrls: ['x-paraph.component.scss'],
    providers: [NotificationService],
})
export class XParaphComponent implements OnInit {

    lang: any = LANG;
    loading: boolean = false;

    currentAccount: any = null;
    usersWorkflowList: any[] = [];
    currentWorkflow: any[] = [];
    contextList = [
        {
            'id': 'FON',
            'label': 'fon'
        },
        {
            'id': 'PER',
            'label': 'per'
        },
        {
            'id': 'SPH',
            'label': 'sph'
        },
        {
            'id': 'DIR',
            'label': 'dir'
        },
        {
            'id': 'DLP',
            'label': 'dlp'
        },
        {
            'id': 'EXE',
            'label': 'exe'
        }
    ];
    hidePassword: boolean = true;

    usersCtrl = new FormControl();
    filteredUsers: Observable<any[]>;

    @Input('additionalsInfos') additionalsInfos: any;

    constructor(public http: HttpClient, private notify: NotificationService) { }

    ngOnInit(): void { }

    init_xParaph() {

    }

    drop(event: CdkDragDrop<string[]>) {
        if (event.previousContainer === event.container) {
            moveItemInArray(event.container.data, event.previousIndex, event.currentIndex);
        }
    }

    selectAccount(account: any) {
        this.loading = false;
        this.currentAccount = account.value;
        this.usersWorkflowList = [];
        this.currentWorkflow = [];
        this.currentAccount.password = '';
    }

    getUsersWorkflowList(account: any) {
        this.loading = true;
        this.filteredUsers = this.usersCtrl.valueChanges
            .pipe(
                startWith(''),
                map(state => state ? this._filterUsers(state) : this.usersWorkflowList.slice())
            );
        this.http.get('../../rest/xParaphWorkflow?login=' + account.login + '&password=' + account.password + '&siret=' + account.siret)
            .subscribe((data: any) => {
                this.usersWorkflowList = data.workflow;
                this.usersWorkflowList.forEach(element => {
                    element.currentRole = element.roles[0];
                    element.currentContext = this.contextList[0].id;
                });
                setTimeout(() => {
                    $j('#availableUsers').focus();
                }, 0);

            }, (err: any) => {
                this.loading = false;
                this.notify.error(err.error.errors[0]);
            });
    }

    changeRole(i: number, role: string) {
        this.currentWorkflow[i].currentRole = role;
    }

    changeContext(i: number, contextId: string) {
        this.currentWorkflow[i].currentContext = contextId;
    }

    addItem(event: any) {
        this.currentWorkflow.push(JSON.parse(JSON.stringify(event.option.value)));
        $j('#availableUsers').blur();
        this.usersCtrl.setValue('');
    }

    deleteItem(index: number) {
        this.currentWorkflow.splice(index, 1);
    }

    private _filterUsers(value: string): any[] {

        if (typeof value === 'string') {
            const filterValue = value.toLowerCase();
            return this.usersWorkflowList.filter(user => user.displayName.toLowerCase().indexOf(filterValue) != -1);
        }
    }

    checkValidParaph() {
        if (this.currentWorkflow.length > 0 && this.currentAccount.login != '' && this.currentAccount.password != '' && this.currentAccount.siret != '') {
            return false;
        } else {
            return true;
        }
    }
}
