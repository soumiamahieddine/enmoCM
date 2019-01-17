import { Component, OnInit, ChangeDetectorRef} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { MediaMatcher } from '@angular/cdk/layout';
import { LANG } from './translate.component';
import { NotificationService } from './notification.service';
import { Router } from '@angular/router';

import { SelectionModel } from '@angular/cdk/collections';

declare var angularGlobals: any;
declare function $j(selector: any): any;

@Component({
    templateUrl: "activate-user.component.html",
    providers: [NotificationService],
})

export class ActivateUserComponent implements OnInit {
    private _mobileQueryListener: () => void;
    mobileQuery: MediaQueryList;
    coreUrl: string;
    lang: any = LANG;

    user: any = {
        baskets: []
    };
    
    userAbsenceModel: any[] = [];
    basketsToRedirect: string[] = [];

    loading: boolean = false;
    selectedIndex: number = 0;

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
    }

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService, private router: Router) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get('../../rest/currentUser/profile')
            .subscribe((data: any) => {
                this.user = data;

                this.user.baskets.forEach((value: any, index: number) => {
                    this.user.baskets[index]['disabled'] = false;
                    this.user.redirectedBaskets.forEach((redirectedBasket: any) => {
                        if (value.basket_id == redirectedBasket.basket_id && value.basket_owner == redirectedBasket.basket_owner) {
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
                    this.router.navigate(['/home']);
                    this.notify.success(this.lang.absOff);
                }, (err) => {
                    this.notify.error(err.error.errors);
                });
            } else {
                this.router.navigate(['/home']);
                this.notify.success(this.lang.absOff);
            }


        }, (err : any) => {
            this.notify.error(err.error.errors);
        });
        
    }

    logout() {
        location.href = "index.php?display=true&page=logout&logout=true";
    }
}
