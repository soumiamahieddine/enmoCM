import { Component, OnInit, ViewChild, AfterViewInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { HeaderService } from '../../service/header.service';
import { NotificationService } from '../notification.service';
import { STEPPER_GLOBAL_OPTIONS } from '@angular/cdk/stepper';
import { MatStepper } from '@angular/material/stepper';
import { AppService } from '../../service/app.service';
import { LANG } from '../translate.component';

@Component({
    templateUrl: './installer.component.html',
    styleUrls: ['./installer.component.scss'],
    providers: [{
        provide: STEPPER_GLOBAL_OPTIONS, useValue: { showError: true }
    }]
})
export class InstallerComponent implements OnInit, AfterViewInit {

    lang: any = LANG;

    @ViewChild('stepper', { static: true }) stepper: MatStepper;

    constructor(
        private http: HttpClient,
        private router: Router,
        private headerService: HeaderService,
        private notify: NotificationService,
        public appService: AppService,
    ) { }

    ngOnInit(): void {
        this.headerService.hideSideBar = true;
    }

    ngAfterViewInit(): void {
        $('.mat-horizontal-stepper-header-container').insertBefore('.bg-head-content');
        $('.mat-step-icon').css('background-color', 'white');
        $('.mat-step-icon').css('color', '#135f7f');
        $('.mat-step-label').css('color', 'white');
        /*$('.mat-step-label').css('opacity', '0.5');
        $('.mat-step-label-active').css('opacity', '1');*/
        /*$('.mat-step-label-selected').css('font-size', '160%');
        $('.mat-step-label-selected').css('transition', 'all 0.2s');
        $('.mat-step-label').css('transition', 'all 0.2s');*/
    }

    isValidStep() {
        return false;
    }

    initStep(ev: any) {
        // console.log(ev.selectedStep.content);
    }

    endInstall() {
        this.stepper.next();
    }

}
