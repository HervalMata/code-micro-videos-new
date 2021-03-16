// @ts-ignore
import React, {useCallback, useEffect, useRef, useState} from 'react';
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";
import httpGenre from "../../util/http/http-genre";
import {BadgeNo, BadgeYes} from "../../components/Badge";
import {Genre, ListResponse} from "../../util/dto";
import DefaultTable, {TableColumns} from "../../components/Table";
import {useSnackbar} from "notistack";
import {IconButton, Theme, ThemeProvider} from "@material-ui/core";
import {Link} from "react-router-dom";
import EditIcon from "@material-ui/icons/Edit";
import {cloneDeep} from "lodash";
import useFilter from "../../hooks/useFilter";
import {FilterResetButton} from "../../components/Table/FilterResetButton";
import yup from "../../util/vendor/yup";
import httpCategory from "../../util/http/http-category";

const debounceTime = 300;
const debounceTimeSearchText = 300;
const rowsPerPage = 15;
const rowsPerPageOptions = [15, 25, 50];

const columnsDefinition: TableColumns[] = [
    {
        name: "id",
        label: "ID",
        width: "30%",
        options: {
            sort: false,
            filter: false,
        }
    },
    {
        name: "name",
        label: "Nome",
        width: "40%",
        options: {
            filter: false,
        }
    },
    {
        name: "cstegories",
        label: "Categorias",
        width: "30%",
        options: {
            customBodyRender: (value, tableMeta, updateValue) => {
                return value.map((value: any) => value.name).join(",");
            },
            filterType: "multiselect",
            filterOptions: {
                names: [],
            },
        },
    },
    {
        name: "is_active",
        label: "Ativa?",
        width: "4%",
        options: {
            filter: false,
            customBodyRender: (value, tableMeta, updateValue) => {
                if (value === true) {
                    return <BadgeYes />;
                }
                return <BadgeNo />;
            },
        },
    },
    {
        name: "created_at",
        label: "Criado em",
        width: "10%",
        options: {
            customBodyRender: (value, tableMeta, updateValue) => {
                return <span>{format(parseISO(value), "dd/MM/yyyy")}</span>;
            },
            filter: false,
        },
    },
    {
        name: "actions",
        label: "Ações",
        width: "16%",
        options: {
            sort: false,
            filter: false,
            customBodyRender: (value, tableMeta, updateValue) => {
                return (
                    <span>
                        <IconButton
                            color={"secondary"} component={Link}
                            to={`genres/${tableMeta.rowData[0]}/edit`}
                        >
                            <EditIcon fontSize={"inherit"}/>
                        </IconButton>
                    </span>
                );
            },
        }
    }
];

function localTheme(theme: Theme) {
    const copyTheme = cloneDeep(theme);
    const selector = `&[data-testid^="MuiDataTableBodyCell-${
        columnsDefinition.length - 1
    }"]`;
    (copyTheme.overrides as any).MUIDataTableBodyCell.root[selector] = {
        paddingTop: "0px",
        paddingBottom: "0px",
    };
    return copyTheme;
}

const Table = () => {
    const canLoad = useRef(true);
    const [genres, setGenres] = useState<Genre[]>([]);
    const [loading, setLoading] = useState(false);
    const {filterState, debouncedFilterState, filterManager, totalRecords, setTotalRecords} = useFilter({
        debounceTime: debounceTime, rowsPerPage: rowsPerPage, rowsPerPageOptions: rowsPerPageOptions, columns: columnsDefinition,
        extraFilter: {
            createValidationSchema: () => {
                return yup.object().shape({
                    categories: yup.mixed()
                        .nullable()
                        .transform((value) => {
                            return !value || value === "" ? undefined : value.split(",");
                        }).default(null),
                });
            },
            formatSearchParams: (debouncedState) => {
                return debouncedState.extraFilter
                    ? {
                        ...(debouncedState.extraFilter && debouncedState.extraFilter.categories && {
                            categories: debouncedState.extraFilter.categories.join(","),
                        }),
                    } : undefined;
            },
            getStateFromURL: (queryParams) => {
                return {
                    type: queryParams.get("type"),
                };
            },
        },
    });
    const snackbar = useSnackbar();

    const getData = useCallback(async () => {
        setLoading(true);
            try {
                const {data} = await httpGenre.list<ListResponse<Genre>>({
                    queryParams: {
                        search: typeof debouncedFilterState.search === "string" ? debouncedFilterState.search: "",
                        page: debouncedFilterState.pagination.page,
                        per_page: debouncedFilterState.pagination.per_page,
                        sort: debouncedFilterState.order.sort,
                        dir: debouncedFilterState.order.dir,
                        ...(debouncedFilterState.extraFilter && debouncedFilterState.extraFilter.categories && {
                            categories: debouncedFilterState.extraFilter.categories.join(","),
                        }),
                    },
                });
                if (canLoad.current) {
                    // @ts-ignore
                    setGenres(data.data);
                    console.log(data);
                    setTotalRecords(data.meta.total);
                }
            } catch (error) {
                console.log(error);
                if (httpGenre.isCancelledRequest(error)) {
                    return;
                }
                snackbar.enqueueSnackbar("Nao foi possível carregar as informações", {
                    variant: "error",
                });
            } finally {
                setLoading(false);
            }
    }, [
        snackbar,
        debouncedFilterState.search,
        debouncedFilterState.pagination.page,
        debouncedFilterState.pagination.per_page,
        debouncedFilterState.order,
        debouncedFilterState.extraFilter,
        setTotalRecords,
    ]);

    useEffect(() => {
        filterManager.replaceHistory();
        // eslint-disable-next-line
    }, []);

    const columnCategory = columnsDefinition.find((c) => c.name === "categories");
    const categoriesFilterValue = filterState.extraFilter && filterState.extraFilter.categories;
    if (columnCategory && columnCategory.options) {
        columnCategory.options.filterList = categoriesFilterValue ? [...categoriesFilterValue] : [];
    }

    useEffect(() => {
        let canLoad = true;
        (async () => {
            try {
                const { data } = await httpCategory.list({ queryParams: { all: ""}});
                if (canLoad && columnCategory && columnCategory.options && columnCategory.options.filterOptions) {
                    columnCategory.options.filterOptions.names = data.data.map(
                        (category) => category.name
                    );
                }
            } catch (error) {
                snackbar.enqueueSnackbar("Nao foi possível carregar as informações", {
                    variant: "error",
                });
            }
        })();
        return () => {
            canLoad = false;
        };
    }, []);

    useEffect(() => {
        canLoad.current = true;
        getData();
        filterManager.pushHistory();
        return () => {
            canLoad.current = false;
        };
        // eslint-disable-next-line
    }, [getData]);

    return (
        <ThemeProvider theme={localTheme}>
            <DefaultTable
                debouncedSearchTime={debounceTimeSearchText}
                data={genres} loading={loading}
                        title={"Listagem de Generos"}
                        columns={columnsDefinition}
                options={{
                    serverSide: true,
                    searchText: filterState.search as any,
                    page: filterManager.getCorrectPage(),
                    rowsPerPage: filterState.pagination.per_page,
                    rowsPerPageOptions,
                    count: totalRecords,
                    sortOrder: {
                        name: filterState.order.sort || "NONE",
                        direction: (filterState.order.dir as any) || "asc",
                    },
                    customToolbar: () => {
                        return (
                            <FilterResetButton handleClick={() => {
                                filterManager.cleanFilter();
                            }}
                            />
                        );
                    },
                    onSearchChange: (value) => {
                        filterManager.changeSearch(value);
                    },
                    onChangePage: (page) => {
                        filterManager.changePage(page);
                    },
                    onChangeRowsPerPage: (perPage) => {
                        filterManager.changePerPage(perPage);
                    },
                    onColumnSortChange: (changedColumn, direction) => {
                        filterManager.columnSortChange(
                            changedColumn,
                            direction,
                        );
                    },
                    onFilterChange: (changedColumn, filterList, type, index) => {
                        filterManager.changeExtraFilter({
                            [changedColumn as string]: filterList[index].length ? filterList[index] : null,
                        });
                    },
                }
              }
            />
        </ThemeProvider>

    );
};

export default Table;