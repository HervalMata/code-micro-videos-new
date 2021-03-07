import React, {useEffect, useState} from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";
import {httpVideo} from "../../util/http";
import {Chip} from "@material-ui/core";
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";

const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "nome",
    },
    {
        name: "is_active",
        label: "Ativa?",
        options: {
            customBodyRender: (value, tableMeta, updateValue) => {
                if (value === true) {
                    return <Chip color="primary" label="Sim" />;
                }
                // @ts-ignore
                return <Chip color="secundary" label="Não" />;
            },
        },
    },
    {
        name: "created_at",
        label: "Criado em",
        options: {
            customBodyRender: (value, tableMeta, updateValue) => {
                return <span>{format(parseISO(value), "dd/MM/yyyy")}</span>;
            },
        },
    },
];

const Table = () => {

    const [data, setData] = useState([]);

    useEffect(() => {
        httpVideo.get("categories").then((response) => {
            setData(response.data);
            console.log(response.data);
        });
    }, []);

    return (
        <MUIDataTable data={data} title={"Listagem de Categorias"}
                      columns={columnsDefinition} />

    );
};

export default Table;