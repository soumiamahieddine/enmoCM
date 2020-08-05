import { Injectable } from '@angular/core';
import { LocalStorageService } from '../../service/local-storage.service';
import { HeaderService } from '../../service/header.service';
import { FunctionsService } from '../../service/functions.service';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { merge } from 'rxjs/internal/observable/merge';
import { startWith } from 'rxjs/internal/operators/startWith';
import { tap } from 'rxjs/internal/operators/tap';
import { catchError } from 'rxjs/internal/operators/catchError';
import { of } from 'rxjs/internal/observable/of';
import { NotificationService } from '../../service/notification/notification.service';

@Injectable()
export class AdministrationService {
    filters: any = {};
    defaultFilters: any = {
        admin_users: {
            sort: 'user_id',
            sortDirection: 'asc',
            page: 0,
            field : ''
        },
    };
    dataSource: MatTableDataSource<any>;
    filterColumns: string[];

    constructor(
        private notify: NotificationService,
        public headerService: HeaderService,
        public functionsService: FunctionsService,
        private localStorage: LocalStorageService,
    ) {
        if (this.localStorage.get(`filtersAdmin_${this.headerService.user.id}`) !== null) {
            this.filters = JSON.parse(this.localStorage.get(`filtersAdmin_${this.headerService.user.id}`));
        }
    }

    setFilter(id: string, filter: any) {
        this.filters[id] = filter;
        this.localStorage.save(`filtersAdmin_${this.headerService.user.id}`, JSON.stringify(this.filters));
    }

    getDataSource() {
        return this.dataSource;
    }

    setDataSource(adminId: string, data: any, sort: MatSort, paginator: MatPaginator, filterColumns: string[]) {

        this.filterColumns = filterColumns;
        this.dataSource = new MatTableDataSource(data);

        this.dataSource.paginator = paginator;
        this.dataSource.sortingDataAccessor = this.functionsService.listSortingDataAccessor;

        if (this.functionsService.empty(this.getFilter(adminId))) {
            this.setFilter(
                adminId,
                this.defaultFilters[adminId]
            );
        }

        sort.active = this.getFilter(adminId, 'sort');
        sort.direction = this.getFilter(adminId, 'sortDirection');
        paginator.pageIndex = this.getFilter(adminId, 'page');

        this.dataSource.sort = sort;

        this.applyFilter(adminId, this.getFilter(adminId, 'field'));

        merge(sort.sortChange, paginator.page)
            .pipe(
                startWith({}),
                tap(() => {
                    this.setFilter(
                        adminId,
                        {
                            sort: sort.active,
                            sortDirection: sort.direction,
                            page: paginator.pageIndex,
                            field: this.getFilter(adminId, 'field')
                        }
                    );
                }),
                catchError((err: any) => {
                    this.notify.handleSoftErrors(err);
                    return of(false);
                })
            ).subscribe();
    }

    getFilter(id: string, idFilter: string = null) {
        if (!this.functionsService.empty(this.filters[id])) {
            if (!this.functionsService.empty(idFilter)) {
                return !this.functionsService.empty(this.filters[id][idFilter]) ? this.filters[id][idFilter] : '';
            } else {
                return !this.functionsService.empty(this.filters[id]) ? this.filters[id] : '';
            }
        } else {
            return null;
        }
    }

    applyFilter(adminId: string, filterValue: string) {
        this.filters[adminId]['field'] = filterValue;
        this.setFilter(adminId, this.filters[adminId]);
        filterValue = filterValue.trim(); // Remove whitespace
        filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
        this.dataSource.filter = filterValue;
        this.dataSource.filterPredicate = (template, filter: string) => {
            return this.functionsService.filterUnSensitive(template, filter, this.filterColumns);
        };
    }
}
