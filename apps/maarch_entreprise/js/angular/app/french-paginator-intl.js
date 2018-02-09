"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var material_1 = require("@angular/material");
var frenchRangeLabel = function (page, pageSize, length) {
    if (length == 0 || pageSize == 0) {
        return "0 de " + length;
    }
    length = Math.max(length, 0);
    var startIndex = page * pageSize;
    // If the start index exceeds the list length, do not try and fix the end index to the end.
    var endIndex = startIndex < length ?
        Math.min(startIndex + pageSize, length) :
        startIndex + pageSize;
    var nbPage = Math.ceil(length / pageSize);
    //return `${startIndex + 1} - ${endIndex} / ${length} (${page})`;
    return "Page " + (page + 1) + " / " + nbPage;
};
function getFrenchPaginatorIntl() {
    var paginatorIntl = new material_1.MatPaginatorIntl();
    paginatorIntl.itemsPerPageLabel = 'Afficher:';
    paginatorIntl.nextPageLabel = 'Page suivante';
    paginatorIntl.previousPageLabel = 'Page précédente';
    paginatorIntl.getRangeLabel = frenchRangeLabel;
    return paginatorIntl;
}
exports.getFrenchPaginatorIntl = getFrenchPaginatorIntl;
