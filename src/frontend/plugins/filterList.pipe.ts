import { Pipe, PipeTransform } from "@angular/core";



@Pipe({
	name: 'filterList'
})
export class FilterListPipe implements PipeTransform {

	transform(value: any, args: string, id: string): any {
		if (id !== undefined) {
			let filter = args.toLocaleLowerCase();
			return filter ? value.filter((elem:any) => elem[id].toLocaleLowerCase().indexOf(filter) != -1) : value;
		} else {
			console.log('Init filter failed for values : ');
			console.log(value);
		}
		
	}
}

/*@Pipe({
	name: 'filterShortcut'
})
export class FilterShortcutPipe implements PipeTransform {

	transform(value: any, args: string): any {

		let filter = args.toLocaleLowerCase();
		return filter ? value.filter((shortcut:any) => shortcut.label.toLocaleLowerCase().indexOf(filter) != -1) : value;
	}
}*/
