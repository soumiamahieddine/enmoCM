import { MatPaginatorIntl } from '@angular/material/paginator';


const frenchRangeLabel = (page: number, pageSize: number, length: number) => {
  if (length == 0 || pageSize == 0) { return `0 de ${length}`; }
  
  length = Math.max(length, 0);

  const startIndex = page * pageSize;

  // If the start index exceeds the list length, do not try and fix the end index to the end.
  const endIndex = startIndex < length ?
      Math.min(startIndex + pageSize, length) :
      startIndex + pageSize;

  const nbPage = Math.ceil(length / pageSize);  
      //return `${startIndex + 1} - ${endIndex} / ${length} (${page})`;
      return `Page ${page + 1} / ${nbPage}`;
};

const englishRangeLabel = (page: number, pageSize: number, length: number) => {
  if (length == 0 || pageSize == 0) { return `0 of ${length}`; }

  return frenchRangeLabel(page, pageSize, length);
};


export function getFrenchPaginatorIntl() {
  const paginatorIntl = new MatPaginatorIntl();
  paginatorIntl.nextPageLabel = 'Page suivante';
  paginatorIntl.previousPageLabel = 'Page précédente';
  paginatorIntl.getRangeLabel = frenchRangeLabel;
  paginatorIntl.itemsPerPageLabel = '';
  
  return paginatorIntl;
}


export function getEnglishSeparator() {
  const paginatorIntl = new MatPaginatorIntl();
  paginatorIntl.nextPageLabel = 'Next page';
  paginatorIntl.previousPageLabel = 'Previous page';
  paginatorIntl.getRangeLabel = englishRangeLabel;
  paginatorIntl.itemsPerPageLabel = '';

  return paginatorIntl;
}

export function getTranslatedPaginator(langIso: string) {
  if (langIso === 'fr-FR') {
    return getFrenchPaginatorIntl();
  }

  return getEnglishSeparator();
}
