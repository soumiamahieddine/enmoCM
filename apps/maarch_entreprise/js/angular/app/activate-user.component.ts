import { Component, OnInit, ChangeDetectorRef} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { MediaMatcher } from '@angular/cdk/layout';
import { NotificationService } from './notification.service';
import { LANG } from './translate.component';

declare var angularGlobals: any;

@Component({
    templateUrl: "../../../Views/activate-user.component.html",
    providers: [NotificationService],
})

export class ActivateUserComponent implements OnInit {

    private _mobileQueryListener    : () => void;
    mobileQuery                     : MediaQueryList;

    coreUrl     : string;
    lang        : any       = LANG;
    loading     : boolean   = false;


    constructor( media: MediaMatcher, changeDetectorRef: ChangeDetectorRef, public http: HttpClient, private notify: NotificationService){
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit() : void {
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = false;
    }

    activateUser() : void {
        this.http.put(this.coreUrl + 'rest/users/' + angularGlobals.user.id + '/status', {'status' : 'OK'})
            .subscribe(() => {
                this.notify.success(this.lang.absOff);
                location.href = "index.php";
            }, (err : any) => {
                this.notify.error(err.error.errors);
            });
    }

    logout() {
        location.href = "index.php?display=true&page=logout&logout=true";
    }
}
