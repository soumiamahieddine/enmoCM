import { ChangeDetectorRef, Component, OnInit, NgZone, ViewChild, QueryList, ViewChildren } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from './translate.component';
import { NotificationService } from './notification.service';
import { HeaderService }        from '../service/header.service';
import { MatDialog, MatDialogRef, MatSidenav, MatExpansionPanel } from '@angular/material';

import { SelectionModel } from '@angular/cdk/collections';
import { FormBuilder } from '@angular/forms';

declare var angularGlobals: any;
declare function $j(selector: any): any;
declare var angularGlobals: any;

declare function $j(selector: any): any;

declare var tinymce: any;
declare var angularGlobals: any;

@Component({
    templateUrl: "activate-user.component.html",
    providers: [NotificationService],
})

export class ActivateUserComponent implements OnInit {

    
    private _mobileQueryListener: () => void;
    mobileQuery: MediaQueryList;
    dialogRef: MatDialogRef<any>;
    mobileMode                      : boolean   = false;
    coreUrl: string;
    lang: any = LANG;

    user: any = {
        baskets: []
    };
    
    userAbsenceModel: any[] = [];
    basketsToRedirect: string[] = [];

    loading: boolean = false;
    selectedIndex: number = 0;

    @ViewChild('snav2') sidenavRight: MatSidenav;
    @ViewChild('snav') sidenavLeft: MatSidenav;

    //Redirect Baskets
    selectionBaskets = new SelectionModel<Element>(true, []);
    myBasketExpansionPanel: boolean = false;
    masterToggleBaskets(event: any) {
        if (event.checked) {  
            this.user.redirectedBaskets.forEach((basket: any) => {
                this.selectionBaskets.select(basket);   
            });
        } else {
            this.selectionBaskets.clear();
        }

        console.log(this.selectionBaskets);
    }

    @ViewChildren(MatExpansionPanel) viewPanels: QueryList<MatExpansionPanel>;

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private zone: NgZone, private notify: NotificationService, public dialog: MatDialog, private _formBuilder: FormBuilder, private headerService: HeaderService) {
        this.mobileMode = angularGlobals.mobileMode;
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.headerService.setHeader(this.lang.myProfile);
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get('../../rest/currentUser/profile')
            .subscribe((data: any) => {
                this.user = data;


                this.user.baskets.forEach((value: any, index: number) => {
                    this.user.baskets[index]['disabled'] = false;
                    this.user.redirectedBaskets.forEach((value2: any) => {
                        if (value.basket_id == value2.basket_id && value.basket_owner == value2.basket_owner) {
                            this.user.baskets[index]['disabled'] = true;
                        }
                    });
                });
                this.loading = false;
            });
    }   

    showActions(basket:any){
        $j('#'+basket.basket_id+'_'+basket.group_id).show();
    }

    hideActions(basket:any){
        $j('#'+basket.basket_id+'_'+basket.group_id).hide();
    }

    //action on user
    activateUser() : void {

        this.http.put(this.coreUrl + 'rest/users/' + angularGlobals.user.id + '/status', {'status' : 'OK'})
        .subscribe(() => {
        
            let basketsRedirectedIds:any = "";
            
            this.user.redirectedBaskets.forEach((elem: any) => {
                if (this.selectionBaskets.selected.map((e:any) => { return e.basket_id; }).indexOf(elem.basket_id) != -1 
                && this.selectionBaskets.selected.map((e:any) => { return e.group_id; }).indexOf(elem.group_id) != -1) {
                    if(basketsRedirectedIds != "") {
                        basketsRedirectedIds = basketsRedirectedIds + "&redirectedBasketIds[]=";
                    }
                    basketsRedirectedIds = basketsRedirectedIds + elem.id;
                }
            });

            if(basketsRedirectedIds != "") {
                this.http.delete(this.coreUrl + "rest/users/" + angularGlobals.user.id + "/redirectedBaskets?redirectedBasketIds[]=" + basketsRedirectedIds)
                .subscribe((data: any) => {
                    this.notify.success(this.lang.basketUpdated);
                    location.href = "index.php";
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
            }

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
