import {Pipe, PipeTransform, NgZone, ChangeDetectorRef, OnDestroy} from "@angular/core";
@Pipe({
	name:'timeLimit',
	pure:false
})
export class TimeLimitPipe implements PipeTransform, OnDestroy {
	private timer: number;
	constructor(private changeDetectorRef: ChangeDetectorRef, private ngZone: NgZone) {}
	transform(value:string) {
		this.removeTimer();
		let d = new Date(value);
		let dayNumber = ('0' + d.getDate()).slice(-2)
		let monthNumber = ('0' + d.getMonth()).slice(-2)
		let hourNumber = ('0' + d.getHours()).slice(-2)
		let minuteNumber = ('0' + d.getMinutes()).slice(-2)
		let now = new Date();
		let month = new Array();
		month[0] = "Jan.";
		month[1] = "Fév.";
		month[2] = "Mars";
		month[3] = "Avr.";
		month[4] = "Mai";
		month[5] = "Juin";
		month[6] = "Juil.";
		month[7] = "Août";
		month[8] = "Sept.";
		month[9] = "Oct.";
		month[10] = "Nov.";
		month[11] = "Déc.";
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
		if(now > d) {
			return '<span class="timeDanger" color="warn">dépassée !</span>';
		} else {
			if (Number.isNaN(seconds)){
				return '';
			} else if (days <= 3) {
				return '<span color="warn">'+days + ' jour(s)'+'</span>';
			} else if (days <= 7) {
				return '<span class="timeWarn">'+days + ' jour(s)'+'</span>';
			} else if (days <= 345) {
				return '<span color="accent">'+d.getDate()+' '+ month[d.getMonth()]+'</span>';
			} else if (days <= 545) {
				return dayNumber + '/' + monthNumber + '/' + d.getFullYear();
			} else { // (days > 545)
				return dayNumber + '/' + monthNumber + '/' + d.getFullYear();
			}
		}
		
		
		/*if (Number.isNaN(seconds)){
			return '';
		} else if (seconds <= 45) {
			return seconds + ' secondes';
		} else if (seconds <= 90) {
			//return 'une minute';
		} else if (minutes <= 45) {
			return minutes + ' minutes';
		} else if (minutes <= 90) {
			//return 'une heure';
		} else if (hours <= 22) {
			return hourNumber+':'+minuteNumber;
			//return hours + ' heures';
		} else if (hours <= 36) {
			return dayNumber+' '+ month[d.getMonth()];
			//return 'un jour';
		} else if (days <= 25) {
			return d.getDate()+' '+ month[d.getMonth()];
			//return days + ' jours';
		} else if (days <= 45) {
			return d.getDate()+' '+ month[d.getMonth()];
			//return 'un mois';
		} else if (days <= 345) {
			return d.getDate()+' '+ month[d.getMonth()];
			//return months + ' mois';
		} else if (days <= 545) {
			return dayNumber + '/' + monthNumber + '/' + d.getFullYear();
			//return 'un an';
		} else { // (days > 545)
			return dayNumber + '/' + monthNumber + '/' + d.getFullYear();
			//return years + ' ans';
		}*/
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
}