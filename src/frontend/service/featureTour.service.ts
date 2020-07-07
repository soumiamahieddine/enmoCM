import { Injectable } from '@angular/core';
import { LANG } from '../app/translate.component';
import { JoyrideService } from 'ngx-joyride';
import { LocalStorageService } from './local-storage.service';
import { HeaderService } from './header.service';
import { FunctionsService } from './functions.service';
import { Router } from '@angular/router';

@Injectable({
    providedIn: 'root'
})
export class FeatureTourService {

    lang: any = LANG;

    currentStepType: string = '';

    tour: any[] = [
        {
            type: 'notification',
            stepId: 'admin_notif',
        },
        {
            type: 'notification',
            stepId: 'notifTour@administration/notifications',
        },
        {
            type: 'notification',
            stepId: 'notifTour2@administration/notifications',
        },
        {
            type: 'notification',
            stepId: 'notifTour3@administration/notifications',
        },
        {
            type: 'notification',
            stepId: 'notifTour4@administration/notifications',
        },
        {
            type: 'email',
            stepId: 'admin_email_server',
        },
    ];

    featureTourEnd: any[] = [];

    constructor(
        private readonly joyrideService: JoyrideService,
        private localStorage: LocalStorageService,
        private headerService: HeaderService,
        private functionService: FunctionsService,
        private router: Router
    ) {
        this.getCurrentStepType();
    }

    init() {
        if (!this.functionService.empty(this.currentStepType)) {
            setTimeout(() => {
                const steps = this.tour.filter(step => step.type === this.currentStepType).map(step => step.stepId);
                this.joyrideService.startTour(
                    { steps: steps }
                ).subscribe(
                    step => {
                        /*Do something*/
                    },
                    error => {
                        /*handle error*/
                    },
                    () => {
                        console.log('end tour');
                        this.router.navigate(['/administration']);
                        this.endTour();
                    }
                );
                /*this.joyrideService.startTour(
                    { steps: ['admin_email_server', 'admin_notif', 'notifTour@administration/notifications', 'notifTour2@administration/notifications', 'notifTour3@administration/notifications', 'notifTour4@administration/notifications', 'admin_users'] }
                );*/
            }, 500);
        }
    }

    getCurrentStepType() {
        if (this.localStorage.get(`featureTourEnd_${this.headerService.user.id}`) !== null) {
            this.featureTourEnd = JSON.parse(this.localStorage.get(`featureTourEnd_${this.headerService.user.id}`));
        }
        const unique = [...new Set(this.tour.map(item => item.type))];

        this.currentStepType = unique.filter(stepType => this.featureTourEnd.indexOf(stepType) === -1)[0];
    }

    endTour() {
        this.featureTourEnd.push(this.currentStepType);
        this.localStorage.save(`featureTourEnd_${this.headerService.user.id}`, JSON.stringify(this.featureTourEnd));
        this.getCurrentStepType();
    }

}
