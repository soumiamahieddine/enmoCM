import { Pipe, PipeTransform } from "@angular/core";
import { LANG } from '../app/translate.component';

@Pipe({
	name: 'fullDate',
	pure: false
})
export class FullDatePipe implements PipeTransform {
	lang: any = LANG;
	constructor() { }
	transform(value: string) {
		const date = new Date(value);
		const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric' };
		return this.lang.onRange + ' ' + date.toLocaleDateString('fr-FR', options);
	}
}