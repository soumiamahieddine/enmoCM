import { Pipe } from '@angular/core';
import { LatinisePipe } from "ngx-pipes";

@Pipe({ name: "sortBy" })
export class SortPipe {

	constructor(private latinisePipe: LatinisePipe) { }


	transform(array: Array<string>, args: string): Array<string> {
		let normA = '';
		let normB = '';
		if (array !== undefined) {
			array.sort((a: any, b: any) => {
				if (args === undefined) {
					normA = this.latinisePipe.transform(a).toLocaleLowerCase();
					normB = this.latinisePipe.transform(b).toLocaleLowerCase();
				} else {
					normA = this.latinisePipe.transform(a[args]).toLocaleLowerCase();
					normB = this.latinisePipe.transform(b[args]).toLocaleLowerCase();
				}
				if (normA < normB) {
					return -1;
				} else if (normA > normB) {
					return 1;
				} else {
					return 0;
				}
			});
			return array;
		} else {
			return [];
		}
	}
}