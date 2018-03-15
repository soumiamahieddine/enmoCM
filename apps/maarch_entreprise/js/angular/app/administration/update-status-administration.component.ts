import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';
import { NotificationService } from '../notification.service';

import { AutoCompletePlugin } from '../../plugins/autocomplete.plugin';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "../../../../Views/update-status-administration.component.html",
    providers: [NotificationService]
})
export class UpdateStatusAdministrationComponent extends AutoCompletePlugin implements OnInit {
    mobileQuery: MediaQueryList;
    private _mobileQueryListener: () => void;
    coreUrl: string;
    lang: any = LANG;
    statuses: any[] = [];
    statusId: string = "";
    resId: string = "";
    chrono: string = "";

    loading: boolean = false;

    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient, private notify: NotificationService) {
        super(http, ['statuses']);
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    updateBreadcrumb(applicationName: string) {
        if ($j('#ariane')[0]) {
            $j('#ariane')[0].innerHTML = "<a href='index.php?reinit=true'>" + applicationName + "</a> > <a onclick='location.hash = \"/administration\"' style='cursor: pointer'>Administration</a> > Changement du statut";
        }
    }

    ngOnInit(): void {
        this.loading = true;
        this.updateBreadcrumb(angularGlobals.applicationName);
        this.coreUrl = angularGlobals.coreUrl;
        this.loading = false;
    }

    onSubmit() {

        var body = {
            "status": this.statusId
        };
        if (this.resId != "") {
            body["resId"] = this.resId;
        } else if (this.chrono != "") {
            body["chrono"] = this.chrono;
        }
        this.http.put(this.coreUrl + "rest/res/resource/status", body)
            .subscribe(() => {
                this.resId = "";
                this.chrono = "";
                this.statusId = "";
                this.notify.success(this.lang.modificationSaved);
            }, (err) => {
                this.notify.error(err.error.errors);
            });
    }

    resetInput(e: any) {
        if (e.index == 0) {
            this.resId = "";
        } else {
            this.chrono = "";
        }
    }
}
