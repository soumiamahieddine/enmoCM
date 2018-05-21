import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { MediaMatcher } from '@angular/cdk/layout';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../translate.component';

declare function $j(selector: any): any;

declare var angularGlobals: any;


@Component({
    templateUrl: "../../../../Views/versions-update-administration.component.html"
})
export class VersionsUpdateAdministrationComponent implements OnInit {

    mobileQuery                     : MediaQueryList;
    private _mobileQueryListener    : () => void;

    coreUrl     : string;
    lang        : any = LANG;
    loading     : boolean = false;

    versions    : any = {};


    constructor(changeDetectorRef: ChangeDetectorRef, media: MediaMatcher, public http: HttpClient) {
        $j("link[href='merged_css.php']").remove();
        this.mobileQuery = media.matchMedia('(max-width: 768px)');
        this._mobileQueryListener = () => changeDetectorRef.detectChanges();
        this.mobileQuery.addListener(this._mobileQueryListener);
    }

    ngOnDestroy(): void {
        this.mobileQuery.removeListener(this._mobileQueryListener);
    }

    ngOnInit(): void {
        this.coreUrl = angularGlobals.coreUrl;

        this.loading = true;

        this.http.get(this.coreUrl + 'rest/versionsUpdate')
            .subscribe((data: any) => {
                this.versions = data;

                this.loading = false;
            });
    }

    updateVersionAccess() {
        location.href = this.coreUrl+'install/index.php?step=update_language';
    }
}
