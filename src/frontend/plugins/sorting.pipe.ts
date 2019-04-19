import { Pipe } from '@angular/core';
import { LatinisePipe } from "ngx-pipes";

@Pipe({name: "sortBy"})
export class SortPipe {

	constructor(private latinisePipe: LatinisePipe) {}


  transform(array: Array<string>, args: string): Array<string> {

    array.sort((a: any, b: any) => {
			const normA=this.latinisePipe.transform(a[args]).toLocaleLowerCase();
			const normB=this.latinisePipe.transform(b[args]).toLocaleLowerCase();

	    if ( normA < normB ){
	    	return -1;
	    }else if( normA > normB ){
	        return 1;
	    }else{
	    	return 0;	
	    }
    });
    return array;
	}
}