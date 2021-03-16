import {Dispatch, Reducer, useReducer, useState} from "react";
import reducer, {Creators} from "../store/filter";
import {
    State as FilterState, Actions as FilterActions
} from "../store/filter/types";
import {useDebounce} from "use-debounce";
import { History } from "history";
import {useHistory} from "react-router";
import { isEqual } from "lodash";
import yup from "../util/vendor/yup";
import {MUIDataTableColumn} from "mui-datatables";

export interface FilterManagerOptions {
    rowsPerPage: number;
    rowsPerPageOptions: number[];
    debounceTime: number;
    history: History;
    columns: MUIDataTableColumn[];
    extraFilter?: ExtraFilter;
}

export interface ExtraFilter {
    getStateFromURL: (queryParams: URLSearchParams) => any;
    formatSearchParams: (debouncedStata: FilterState) => any;
    createValidationSchema: () => any;
}

export interface useFilterOptions extends Omit<FilterManagerOptions, "history">{}

export class FilterManager {
    schema;
    state: FilterState = null as any;
    debouncedFilterState: FilterState = null as any;
    dispatch: Dispatch<FilterActions> = null as any;
    rowsPerPage: number;
    rowsPerPageOptions: number[];
    debounceTime: number;
    history: History;
    columns: MUIDataTableColumn[];
    extraFilter?: ExtraFilter;

    constructor(options: FilterManagerOptions) {
        const { rowsPerPage, rowsPerPageOptions, debounceTime,
            history, columns, extraFilter, } = options;
        this.rowsPerPage = rowsPerPage;
        this.rowsPerPageOptions = rowsPerPageOptions;
        this.debounceTime = debounceTime;
        this.history = history;
        this.columns = columns;
        this.extraFilter = extraFilter;
        this.createValidationSchema();
    }

    private resetTablePagination() {
        this.changePage(0);
        this.changePerPage(this.rowsPerPage);
    }

    changeSearch(value) {
        this.dispatch(Creators.setSearch({ search: value || null }));
        this.resetTablePagination();
    }

    changePage(page) {
        this.dispatch(Creators.setPage({ page: page + 1 }));
    }

    changePerPage(perPage) {
        this.dispatch(Creators.setPerPage({ per_page: perPage }));
    }

    columnSortChange(changedColumn, direction) {
        this.dispatch(Creators.setOrder({
            sort: changedColumn,
            dir: direction.includes("desc") ? "desc" : "asc",
        }));
        this.resetTablePagination();
    }

    cleanFilter() {
        this.dispatch(Creators.cleanFilter());
        this.resetTablePagination();
    }

    getCorrectPage() {
        const page = this.debouncedFilterState.pagination.page;
        return page <= 0 ? page : page - 1;
    }

    replaceHistory() {
        this.history.replace({
            pathname: this.history.location.pathname,
            search: "?" + new URLSearchParams(this.formatSearchParams() as any),
            state: this.debouncedFilterState,
        });
    }

    pushHistory() {
        const newLocation = {
            pathname: this.history.location.pathname,
            search: "?" + new URLSearchParams(this.formatSearchParams() as any),
            state: {
                ...this.state,
                search: typeof this.debouncedFilterState.search === "string" ? this.debouncedFilterState.search : "",
            },
        };

        const oldState = this.history.location.state;
        const nextState = this.debouncedFilterState;

        if (!isEqual(oldState, nextState)) {
            this.history.push(newLocation);
        }
    }

    private formatSearchParams() {
        const search = typeof this.debouncedFilterState.search === "string" ? this.debouncedFilterState.search : "";
        return {
            ...(search && search !== "" && { search: search }),
            ...(this.debouncedFilterState.pagination.per_page !== 15 && {
                per_page: this.debouncedFilterState.pagination.per_page,
            }),
            ...(this.debouncedFilterState.pagination.page > 1 && {
                per_page: this.debouncedFilterState.pagination.page,
            }),
            ...(this.debouncedFilterState.order && this.debouncedFilterState.order.sort && this.debouncedFilterState.order.dir && {
                sort: this.debouncedFilterState.order.sort, dir: this.debouncedFilterState.order.dir,
            }),
            ...(this.extraFilter && this.extraFilter.formatSearchParams(this.debouncedFilterState))
        };
    }

    getStateFromUrl() {
        const queryParams = new URLSearchParams(this.history.location.search.substr(1));
        return this.schema.cast({
            search: queryParams.get("search"),
            pagination: {
                page: queryParams.get("page"),
                per_page: queryParams.get("per_page"),
            },
            order: {
                sort: queryParams.get("sort"),
                dir: queryParams.get("dir"),
            },
            ...(this.extraFilter && {extraFilter: this.extraFilter.getStateFromURL(queryParams),
            }),
        });
    }

    private createValidationSchema() {
        this.schema = yup.object().shape({
            search: yup.string().transform((value) => (!value ? undefined : value)).default(""),
            pagination: yup.object().shape({
                page: yup.number().transform((value) => isNaN(value) || parseInt(value) < 1 ? undefined : value).default(1),
                per_page: yup.number().transform((value) => (isNaN(value) ? undefined : value)).default(this.rowsPerPage),
            }),
            order: yup.object().shape({
                sort: yup.string().nullable().transform((value) => {
                    const columnsName = this.columns.filter((column) => {
                        return !column.options || column.options.sort !== false;
                    }).map((column) => column.name);
                    return columnsName.includes(value) ? value : undefined;
                }).default(null),
                dir: yup.string().nullable().transform((value) => {
                    return !value || ["asc", "desc"].includes(value.toLowerCase()) ? value : undefined;
                }).default(null),
            }),
            ...(this.extraFilter && {extraFilter: this.extraFilter.createValidationSchema(),
            }),
        });
    }

    changeExtraFilter (data) {
        this.dispatch(Creators.updateExtraFilter(data))
    }
}

const useFilter = (options: useFilterOptions) => {
    const history = useHistory();
    const filterManager = new FilterManager({ ...options, history });
    const INITIAL_STATE = filterManager.getStateFromUrl();
    const [totalRecords, setTotalRecords] = useState(0);
    const [filterState, dispatch] = useReducer<Reducer<FilterState, FilterActions>>(reducer, INITIAL_STATE);
    const [debouncedFilterState] = useDebounce(filterState, options.debounceTime);

    filterManager.debouncedFilterState = debouncedFilterState;
    filterManager.dispatch = dispatch as any;

    return {
        filterManager, filterState, debouncedFilterState, dispatch, totalRecords, setTotalRecords
    };
};

export default useFilter;