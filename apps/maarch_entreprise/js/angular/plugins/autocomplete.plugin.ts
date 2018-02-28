import { HttpClient } from '@angular/common/http';
import { ActivatedRoute, Router } from '@angular/router';
import { FormControl } from '@angular/forms';
import { Observable } from 'rxjs/Observable';
import { startWith } from 'rxjs/operators/startWith';
import { map } from 'rxjs/operators/map';

declare const angularGlobals: any;

export class AutoCompletePlugin {
    coreUrl: string;
    userCtrl: FormControl;
    statusCtrl: FormControl;
    elementCtrl: FormControl;
    filteredUsers: Observable<any[]>;
    filteredElements: Observable<any[]>;
    filteredStatuses: Observable<any[]>;
    userList: any[] = [];
    elemList: any[] = [];
    statusesList: any[] = [];

    constructor(public http: HttpClient, target: any[]) {
        this.coreUrl = angularGlobals.coreUrl;

        if (target.indexOf('users') != -1) {
            this.userCtrl = new FormControl();
            this.http.get(this.coreUrl + 'rest/users')
                .subscribe((data: any) => {
                    data.users.forEach((user: any) => {
                        if (user.enabled == "Y") {
                            this.userList.push({
                                "type": "user",
                                "id": user.user_id,
                                "idToDisplay": user.firstname + ' ' + user.lastname,
                                "otherInfo": user.user_id
                            });
                        }

                    });
                    this.filteredUsers = this.userCtrl.valueChanges
                        .pipe(
                            startWith(''),
                            map(user => user ? this.autocompleteFilterUser(user) : this.userList.slice())
                        );
                }, () => {
                    location.href = "index.php";
                });
        }
        if (target.indexOf('statuses')  != -1) {
            this.statusCtrl = new FormControl();
            this.http.get(this.coreUrl + 'rest/statuses')
                .subscribe((data: any) => {
                    this.statusesList = data['statuses'];
                    this.filteredStatuses = this.statusCtrl.valueChanges
                        .pipe(
                            startWith(''),
                            map(status => status ? this.autocompleteFilterStatuses(status) : this.statusesList.slice())
                        );
                }, () => {
                    location.href = "index.php";
                });
        }
        if (target.indexOf('usersAndEntities') != -1) {
            this.elementCtrl = new FormControl();
            this.elemList = [];

            this.http.get(this.coreUrl + 'rest/users')
                .subscribe((data: any) => {
                    data.users.forEach((user: any) => {
                        if (user.enabled == "Y") {
                            this.elemList.push({
                                "type": "user",
                                "id": user.user_id,
                                "idToDisplay": user.firstname + ' ' + user.lastname,
                                "otherInfo": user.user_id
                            });
                        }

                    });
                    this.http.get(this.coreUrl + 'rest/entities')
                        .subscribe((data: any) => {
                            data.entities.forEach((entity: any) => {
                                if (entity.allowed == true) {
                                    this.elemList.push({
                                        "type": "entity",
                                        "id": entity.entity_id,
                                        "idToDisplay": entity.entity_label,
                                        "otherInfo": entity.entity_id
                                    });
                                }

                            });
                            this.filteredElements = this.elementCtrl.valueChanges
                                .pipe(
                                    startWith(''),
                                    map(elem => elem ? this.autocompleteFilterElements(elem) : this.elemList.slice())
                                );
                        }, () => {
                            location.href = "index.php";
                        });

                }, () => {
                    location.href = "index.php";
                });

        }
        if (target.indexOf('entities') != -1) {
            this.elementCtrl = new FormControl();
            this.elemList = [];
            this.http.get(this.coreUrl + 'rest/entities')
                .subscribe((data: any) => {
                    data.entities.forEach((entity: any) => {
                        if (entity.allowed == true) {
                            this.elemList.push({
                                "type": "entity",
                                "id": entity.entity_id,
                                "idToDisplay": entity.entity_label,
                                "otherInfo": entity.entity_id
                            });
                        }

                    });
                    this.filteredElements = this.elementCtrl.valueChanges
                        .pipe(
                            startWith(''),
                            map(elem => elem ? this.autocompleteFilterElements(elem) : this.elemList.slice())
                        );
                }, () => {
                    location.href = "index.php";
                });

        } else {

        }

    }
    autocompleteFilterUser(name: string) {
        return this.userList.filter(user =>
            user.idToDisplay.toLowerCase().indexOf(name.toLowerCase()) >= 0);
    }

    autocompleteFilterStatuses(name: string) {
        return this.statusesList.filter(status =>
            status.label_status.toLowerCase().indexOf(name.toLowerCase()) >= 0);
    }

    autocompleteFilterElements(name: string) {
        return this.elemList.filter(elem =>
            elem.idToDisplay.toLowerCase().indexOf(name.toLowerCase()) >= 0);
    }

}