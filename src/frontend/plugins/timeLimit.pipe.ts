import {Pipe, PipeTransform, NgZone, ChangeDetectorRef, OnDestroy} from "@angular/core";
import { LANG } from '../app/translate.component';

@Pipe({
	name:'timeLimit',
	pure:false
})
export class TimeLimitPipe implements PipeTransform, OnDestroy {
	private timer: number;
	lang: any = LANG;
	constructor(
		private changeDetectorRef: ChangeDetectorRef, 
		private ngZone: NgZone) {}
	transform(value:string, args: string = null) {

		this.removeTimer();
		let d = new Date(value);
		let dayNumber = ('0' + d.getDate()).slice(-2);
		const realMonth = d.getMonth()+1;
		let monthNumber = ('0' + realMonth).slice(-2);
		let hourNumber = ('0' + d.getHours()).slice(-2);
		let minuteNumber = ('0' + d.getMinutes()).slice(-2);
		let now = new Date();
		let month = [];
		month[0] = this.lang.januaryShort;
		month[1] = this.lang.februaryShort;
		month[2] = this.lang.marchShort;
		month[3] = this.lang.aprilShort;
		month[4] = this.lang.mayShort;
		month[5] = this.lang.juneShort;
		month[6] = this.lang.julyShort;
		month[7] = this.lang.augustShort;
		month[8] = this.lang.septemberShort;
		month[9] = this.lang.octoberShort;
		month[10] = this.lang.novemberShort;
		month[11] = this.lang.decemberShort;
		let seconds = Math.round(Math.abs((now.getTime() - d.getTime())/1000));
		let timeToUpdate = (Number.isNaN(seconds)) ? 1000 : this.getSecondsUntilUpdate(seconds) *1000;
		this.timer = this.ngZone.runOutsideAngular(() => {
			if (typeof window !== 'undefined') {
				return window.setTimeout(() => {
					this.ngZone.run(() => this.changeDetectorRef.markForCheck());
				}, timeToUpdate);
			}
			return null;
		});
		let minutes = Math.round(Math.abs(seconds / 60));
		let hours = Math.round(Math.abs(minutes / 60));
		let days = Math.round(Math.abs(hours / 24));
		let months = Math.round(Math.abs(days/30.416));
		let years = Math.round(Math.abs(days/365));
		if(value == null) {
			return '<span>' + this.lang.undefined + '</span>';
		} else if(now > d) {
			
			if (args === 'badge') {
				if (hours <= 24) {
					return this.getFormatedDate('', '<b>' + this.lang.outdated + ' ' + this.lang.fromRange.toLowerCase() + ' ' + hours + ' ' + this.lang.hours +' !</b>', 'warn', args);
				} else {
					return this.getFormatedDate('', '<b>' + this.lang.outdated + ' ' + this.lang.fromRange.toLowerCase() + ' ' + (days-1) + ' '+ this.lang.dayS +' !</b>', 'warn', args);
				}
			} else {
				return this.getFormatedDate('', '<b>' + this.lang.outdated + ' !</b>', 'warn', args);
			}	
		} else {
			if (Number.isNaN(seconds)){
				return '';
			} else if (minutes <= 59){
				return this.getFormatedDate(this.lang.in[0].toUpperCase() + this.lang.in.substr(1).toLowerCase(), minutes + ' ' + this.lang.minutes, 'warn', args);
			} else if (hours <= 23){
				return this.getFormatedDate(this.lang.in[0].toUpperCase() + this.lang.in.substr(1).toLowerCase(), hours + ' ' + this.lang.hours, 'warn', args);
			} else if (days <= 5) {
				return this.getFormatedDate(this.lang.in[0].toUpperCase() + this.lang.in.substr(1).toLowerCase(), days + ' ' + this.lang.dayS, 'secondary', args);
			} else if (days <= 345) {
				return this.getFormatedDate(this.lang.onRange[0].toUpperCase() + this.lang.onRange.substr(1).toLowerCase(), d.getDate()+' '+ month[d.getMonth()], 'accent', args);
			} else if (days <= 545) {
				return dayNumber + '/' + monthNumber + '/' + d.getFullYear();
			} else { // (days > 545)
				return dayNumber + '/' + monthNumber + '/' + d.getFullYear();
			}
		}
	}
	ngOnDestroy(): void {
		this.removeTimer();
	}
	private removeTimer() {
		if (this.timer) {
			window.clearTimeout(this.timer);
			this.timer = null;
		}
	}
	private getSecondsUntilUpdate(seconds:number) {
		let min = 60;
		let hr = min * 60;
		let day = hr * 24;
		if (seconds < min) { // less than 1 min, update every 2 secs
			return 2;
		} else if (seconds < hr) { // less than an hour, update every 30 secs
			return 30;
		} else if (seconds < day) { // less then a day, update every 5 mins
			return 300;
		} else { // update every hour
			return 3600;
		}
	}

	getFormatedDate(prefix: string,content: string, color: string, mode: string) {
		if (mode === 'badge') {
			return `${prefix} ${content}&nbsp;<i class="fas fa-circle badgePipe_${color}"></i>`;
		} else {
			return `<span color="${color}">${content}</span>`;
		}
	}
}