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
  filteredUsers: Observable<any[]>;
  userList: any[] = [];

  constructor(public http: HttpClient, target:any) {
    this.coreUrl = angularGlobals.coreUrl;
    this.userCtrl = new FormControl();

    if (target == 'users') {
      this.http.get(this.coreUrl + 'rest/users/autocompleter')
            .subscribe((data: any) => {
                this.userList = data;

            }, () => {
                location.href = "index.php";
            });
    } else {

    }
    
    this.filteredUsers = this.userCtrl.valueChanges
    .pipe(
    startWith(''),
    map(user => user ? this.autocompleteFilter(user) : this.userList.slice())
    );
  }
  autocompleteFilter(name: string) {
    return this.userList.filter(user =>
        user.formattedUser.toLowerCase().indexOf(name.toLowerCase()) === 0);
}

}