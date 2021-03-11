import * as React from 'react';
import {Page} from "../../components/Page";
import {Box, Fab} from "@material-ui/core/index";
import {Link} from "react-router-dom";
import AddIcon from "@material-ui/icons/Add";
import Table from "./Table";

export const GenreListPage = () => {
    return <Page title="Listagem de Gêneros">
        <Box dir={"rtl"} paddingBottom={2}>
            <Fab title={"Adicionar Gênero"}
                 color={"secondary"}
                 size={'small'} component={Link}
                 to="/genres/create">
                <AddIcon />
            </Fab>
        </Box>
        <Box>
            <Table />
        </Box>
    </Page>;
};