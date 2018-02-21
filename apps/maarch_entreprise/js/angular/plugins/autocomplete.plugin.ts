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

  constructor(public http: HttpClient, target: any) {
    this.coreUrl = angularGlobals.coreUrl;

    if (target == 'users') {
      this.userCtrl = new FormControl();
      this.http.get(this.coreUrl + 'rest/users/autocompleter')
        .subscribe((data: any) => {
          this.userList = data;
          this.filteredUsers = this.userCtrl.valueChanges
            .pipe(
              startWith(''),
              map(user => user ? this.autocompleteFilterUser(user) : this.userList.slice())
            );
        }, () => {
          location.href = "index.php";
        });
    } else if (target == 'statuses') {
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
    } else if (target == 'usersAndEntities') {
      this.elementCtrl = new FormControl();
      this.elemList = [{
        "type": "user",
        "id": "bbain",
        "idToDisplay": "Barbara BAIN",
        "otherInfo": "Pôle jeunesse et sport"
      },
      {
        "type": "entity",
        "id": "DGS",
        "idToDisplay": "Direction générale des services",
        "otherInfo": ""
      }];
      this.filteredElements = this.elementCtrl.valueChanges
        .pipe(
          startWith(''),
          map(elem => elem ? this.autocompleteFilterElements(elem) : this.elemList.slice())
        );
    } else {

    }

  }
  autocompleteFilterUser(name: string) {
    return this.userList.filter(user =>
      user.formattedUser.toLowerCase().indexOf(name.toLowerCase()) === 0);
  }

  autocompleteFilterStatuses(name: string) {
    return this.statusesList.filter(status =>
      status.label_status.toLowerCase().indexOf(name.toLowerCase()) === 0);
  }

  autocompleteFilterElements(name: string) {
    return this.statusesList.filter(elem =>
      elem.idToDisplay.toLowerCase().indexOf(name.toLowerCase()) === 0);
  }

}