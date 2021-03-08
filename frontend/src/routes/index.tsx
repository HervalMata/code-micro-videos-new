import {RouteProps} from "react-router-dom"
import {Dashboard} from "../pages/Dashboard";
import {CategoryListPage} from "../pages/category/CategoryListPage";
import {CastMemberListPage} from "../pages/cast-member/CastMemberListPage";
import {GenreListPage} from "../pages/genre/GenreListPage";
import {CategoryFormPage} from "../pages/category/CategoryFormPage";
import {CastMemberFormPage} from "../pages/cast-member/CastMemberFormPage";
import {GenreFormPage} from "../pages/genre/GenreFormPage";

export interface MyRouteProps extends RouteProps {
    label: string;
    name: string;
}

const routes: Array<MyRouteProps> = [
    {
        name: "dashboard",
        label: "Dashboard",
        path: "/",
        component: Dashboard,
        exact: true,
    },
    {
        name: "categories.list",
        label: "Listar Categorias",
        path: "/categories",
        component: CategoryListPage,
        exact: true,
    },
    {
        name: "categories.create",
        label: "Criar Categorias",
        path: "/categories/create",
        component: CategoryFormPage,
        exact: true,
    },
    {
        name: "categories.edit",
        label: "Editar Categorias",
        path: "/categories/:id/edit",
        component: CategoryFormPage,
        exact: true,
    },
    {
        name: "cast-members.list",
        label: "Listar Membros do Elenco",
        path: "/cast-members",
        component: CastMemberListPage,
        exact: true,
    },
    {
        name: "cast-members.create",
        label: "Criar Membro do Elenco",
        path: "/cast-members/create",
        component: CastMemberFormPage,
        exact: true,
    },
    {
        name: "cast-members.edit",
        label: "Editar Membro do Elenco",
        path: "/cast-members/:id/edit",
        component: CastMemberFormPage,
        exact: true,
    },
    {
        name: "genres.list",
        label: "Listar Genero",
        path: "/genres",
        component: GenreListPage,
        exact: true,
    },
    {
        name: "genres.create",
        label: "Criar Genero",
        path: "/genres/create",
        component: GenreFormPage,
        exact: true,
    },
    {
        name: "genres.edit",
        label: "Editar Genero",
        path: "/genres/:id/edit",
        component: GenreFormPage,
        exact: true,
    }
];

// @ts-ignore
export default routes;
