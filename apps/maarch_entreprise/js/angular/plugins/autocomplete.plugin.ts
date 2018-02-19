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
  filteredUsers: Observable<any[]>;
  filteredStatuses: Observable<any[]>;
  userList: any[] = [];
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
    } else {

    }

  }
  autocompleteFilterUser(name: string) {
    return this.userList.filter(user =>
      user.formattedUser.toLowerCase().indexOf(name.toLowerCase()) === 0);
  }

  autocompleteFilterStatuses(name: string) {
    console.log(this.statusesList.filter(status =>
      status.label_status.toLowerCase().indexOf(name.toLowerCase()) === 0));
    return this.statusesList.filter(status =>
      status.label_status.toLowerCase().indexOf(name.toLowerCase()) === 0);
  }

}