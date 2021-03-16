import React, {useCallback, useEffect, useRef, useState} from 'react';
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";
import httpCastMember from "../../util/http/http-cast-member";
import {CastMember, CastMemberTypeMap, ListResponse} from "../../util/dto";
import DefaultTable, {TableColumns} from "../../components/Table";
import {useSnackbar} from "notistack";
import {IconButton, Theme, ThemeProvider} from "@material-ui/core";
import {Link} from "react-router-dom";
import EditIcon from "@material-ui/icons/Edit";
import {cloneDeep, invert} from "lodash";
import useFilter from "../../hooks/useFilter";
import {FilterResetButton} from "../../components/Table/FilterResetButton";
import yup from "../../util/vendor/yup";

const debounceTime = 300;
const debounceTimeSearchText = 300;
const rowsPerPage = 15;
const rowsPerPageOptions = [15, 25, 50];

const castMemberNames = Object.values(CastMemberTypeMap);

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
        width: "24%",
        options: {
            filter: false,
        }
    },
    {
        name: "type",
        label: "Tipo",
        width: "20%",
        options: {
            filterOptions: {
                names: castMemberNames,
            },
            customFilterListOptions: {
                render: (v) => {
                    return ["Tipo: " + v];
                },
            },
            customBodyRender: (value, tableMeta, updateValue) => {
                // @ts-ignore
                return CastMemberTypeMap[value];
            },
        },
    },
    {
        name: "created_at",
        label: "Criado em",
        width: "10%",
        options: {
            filter: false,
            customBodyRender: (value, tableMeta, updateValue) => {
                return <span>{format(parseISO(value), "dd/MM/yyyy")}</span>;
            },
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
                            to={`cast_members/${tableMeta.rowData[0]}/edit`}
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
    const [castMembers, setCastMembers] = useState<CastMember[]>([]);
    const [loading, setLoading] = useState(false);
    const {filterState, debouncedFilterState, filterManager, totalRecords, setTotalRecords} = useFilter({
        debounceTime: debounceTime, rowsPerPage: rowsPerPage, rowsPerPageOptions: rowsPerPageOptions, columns: columnsDefinition,
        extraFilter: {
            createValidationSchema: () => {
                return yup.object().shape({
                    type: yup.string()
                        .nullable()
                        .oneOf(castMemberNames)
                        .transform((value) => {
                            return !value || !castMemberNames.includes(value) ? undefined : value;
                        }).default(null),
                });
            },
            formatSearchParams: (debouncedState) => {
                return debouncedState.extraFilter
                    ? {
                        ...(debouncedState.extraFilter && debouncedState.extraFilter.type && {
                            type: debouncedState.extraFilter.type,
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

    if (filterManager.debouncedFilterState.extraFilter) {
        const column = columnsDefinition.find((c) => c.name === "type");
        if (column && column.options) {
            column.options.filterList = filterManager.debouncedFilterState.extraFilter.type
            ? [filterManager.debouncedFilterState.extraFilter.type] : [];
        }
    }

    const getData = useCallback(async () => {
        setLoading(true);
        try {
            const {data} = await httpCastMember.list<ListResponse<CastMember>>({
                queryParams: {
                    search: typeof debouncedFilterState.search === "string" ? debouncedFilterState.search : "",
                    page: debouncedFilterState.pagination.page,
                    per_page: debouncedFilterState.pagination.per_page,
                    sort: debouncedFilterState.order.sort,
                    dir: debouncedFilterState.order.dir,
                    ...(debouncedFilterState.extraFilter &&
                        debouncedFilterState.extraFilter.type && {
                        type: invert(CastMemberTypeMap)[debouncedFilterState.extraFilter.type],
                        }),
                },
            });
            if (canLoad.current) {
                // @ts-ignore
                setCastMembers(data.data);
                console.log(data);
                setTotalRecords(data.meta.total);
            }
        } catch (error) {
            console.log(error);
            if (httpCastMember.isCancelledRequest(error)) {
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
            <DefaultTable data={castMembers} loading={loading}
                        title={"Listagem de Membros de Elenco"}
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
                              onFilterChange: (changedColumn, filterList, type, index) => {
                                  filterManager.changeExtraFilter({
                                      [changedColumn as string]: filterList[index].length ? filterList[index][0] : null,
                                  });
                              },
                              onSearchChange: (value) => {
                                  filterManager.changeSearch( value );
                              },
                              onChangePage: (page) => {
                                  filterManager.changePage( page );
                              },
                              onChangeRowsPerPage: (perPage) => {
                                  filterManager.changePerPage( perPage );
                              },
                              onColumnSortChange: (changedColumn, direction) => {
                                  filterManager.columnSortChange(
                                      changedColumn,
                                      direction,
                                  );
                              },
                      }}
            />
        </ThemeProvider>

    );
};

export default Table;