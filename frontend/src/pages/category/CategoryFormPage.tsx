// @ts-ignore
import React from 'react';
import {Page} from "../../components/Page";
import {Form} from "./Form";
import {useParams} from "react-router";

export const CategoryFormPage = () => {
    const { id } = useParams<{ id: string }>();
    return (
        <Page title={!id ? "Criar Categoria" : "Editar Categoria"}>
            <Form />
        </Page>
    );
};