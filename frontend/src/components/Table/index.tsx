import * as React from 'react';
import MUIDataTable, {MUIDataTableColumn, MUIDataTableOptions, MUIDataTableProps} from "mui-datatables";
// @ts-ignore
import { merge, omit, cloneDeep } from "lodash";
import {MuiThemeProvider, Theme, useTheme} from "@material-ui/core/styles";
import {useMediaQuery} from "@material-ui/core";
import {DebouncedTableSearch} from "./DebouncedTableSearch";

export interface TableColumns extends MUIDataTableColumn {
    width?: string;
}

interface TableProps extends MUIDataTableProps {
    columns: TableColumns[];
    loading?: boolean;
    debouncedSearchTime?: number;
}

const defaultOptions = (debouncedSearchTime): MUIDataTableOptions => {
    return {
        print: false,
        download: false,
        textLabels: {
            body: {
                noMatch: "Nemhum registro encontrado",
                toolTip: "Classificar",
            },
            pagination: {
                next: "Próxima Página",
                previous: "Página Anterior",
                rowsPerPage: "Por Página",
                displayRows: "de",
            },
            toolbar: {
                search: "Pesquisar",
                downloadCsv: "Download CSV",
                print: "Imprimir",
                viewColumns: "Ver Colunas",
                filterTable: "Filtrar Tabelas",
            },
            filter: {
                all: "Todos",
                title: "Filtros",
                reset: "Limpar",
            },
            viewColumns: {
                title: "Ver Colunas",
                titleAria: "Ver/Esconder Colunas da Tabela",
            },
            selectedRows: {
                text: "registro(s) selecionado(S)",
                delete: "Excluir",
                deleteAria: "Excluir registros selecionados",
            },
        },
        customSearchRender: (searchText, handleSearch, hideSearch, options) => {
            return (
                <DebouncedTableSearch
                    searchText={searchText} onHide={hideSearch}
                    onSearch={handleSearch} options={options}
                    debounceTime={debouncedSearchTime}
                />
            )
        }
    }
};

const Table: React.FC<TableProps> = (props) => {
    function extranctMuiDataTableColumns(
        columns: TableColumns[]
    ) : MUIDataTableColumn[] {
        setColumnsWidth(columns);
        return columns.map((item) => {
            return omit(item, "width");
        });
    }
    function setColumnsWidth(columns: TableColumns[]) {
        columns.forEach((column, key) => {
            if (column.width) {
                const overrides = theme.overrides as any;
                overrides.MUIDataTableHeadCell.fixedHeader[`&:nth-child(${key + 2})`] = {
                    width: column.width,
                };
            }
        });
    }
    const theme = cloneDeep<Theme>(useTheme());
    const isSmOrDowm = useMediaQuery(theme.breakpoints.down("sm"));
    const newProps = merge(
        {options: defaultOptions(props.debouncedSearchTime) },
        props,
        {
            columns: extranctMuiDataTableColumns(props.columns),
        }
    );
    function getOriginalMuiDataTableProps() {
        return omit({...newProps}, "loading", "debouncedSearchTime");
    }
    function applyLoading() {
        const textLabels = (newProps.options as any).textLabels;
        textLabels.body.noMatch =
            newProps.loading === true ? "Carregando..." : textLabels.body.noMatch;
    }
    function applyResponsive() {
        newProps.options.responsive = isSmOrDowm ? "standard" : "simple";
    }
    applyLoading();
    applyResponsive();
    const originalProps = getOriginalMuiDataTableProps();
    return (
        <MuiThemeProvider theme={theme}>
            <MUIDataTable {...originalProps}></MUIDataTable>
        </MuiThemeProvider>

    );
}

export default Table;

