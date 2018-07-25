import { Component, OnInit, ChangeDetectorRef, ViewChild} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { MediaMatcher } from '@angular/cdk/layout';
import { NotificationService } from './notification.service';
import { LANG } from './translate.component';
import { MatDialog, MatDialogRef, MatSidenav, MatExpansionPanel } from '@angular/material';

declare var angularGlobals: any;
declare function $j(selector: any): any;

@Component({
    templateUrl: "../../../Views/activate-user.component.html",
    providers: [NotificationService],
})

export class ActivateUserComponent implements OnInit {
    private _mobileQueryListener: () => void;
    coreUrl     : string;
    mobileQuery : MediaQueryList;
    lang        : any       = LANG;
    loading     : boolean   = false;
    user        : any       = {
        
    }

    constructor( media: MediaMatcher, changeDetectorRef: ChangeDetectorRef, public http: HttpClient, private notify: NotificationService){
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }


    ngOnInit() : void {
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = true;
        this.http.get('../../rest/currentUser/profile')
            .subscribe((data: any) => {
                this.user = data;                
                this.loading = false;
            });
    }

    deleteAbsence() : void {
        this.http.put('../../rest/users/'+this.user.id+'/status', {'id' : this.user.id, 'status' : 'OK'})
            .subscribe(() => {
                this.notify.success(this.lang.statusUpdated);
                location.href = "index.php";
            },
            (err : any) => {
                this.notify.error(err.error.errors);
            });
    }

    redirect() {
        location.href = "index.php";
    }

    logout() {
        location.href = "index.php?display=true&page=logout&logout=true";
    }
}