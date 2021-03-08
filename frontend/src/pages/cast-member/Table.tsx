import React, {useEffect, useState} from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";
import httpCastMember from "../../util/http/http-cast-member";

const CastMemberTypeMap = {
    1: "Diretor",
    2: "Ator",
}

const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "nome",
    },
    {
        name: "type",
        label: "Tipo",
        options: {
            customBodyRender: (value, tableMeta, updateValue) => {
                // @ts-ignore
                return CastMemberTypeMap[value];
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
        httpCastMember.list().then((response) => {
            setData(response.data);
            console.log(response.data);
        });
    }, []);

    return (
        <MUIDataTable data={data} title={"Listagem de Membros de Elenco"}
                      columns={columnsDefinition} />

    );
};

export default Table;