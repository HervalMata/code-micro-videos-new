// @ts-ignore
import React, { useState} from "react";
import {IconButton, Menu as MuiMenu, MenuItem} from "@material-ui/core/index";
import { Menu as MenuIcon } from '@material-ui/icons'
import routes, { MyRouteProps} from "../../routes";
import {Link} from "react-router-dom";

const listRoutes = {
    dashboard: "Dashboard",
    "categories.list": "Categorias",
    "cast-members.list": "Membros do Elenco",
    "genres.list": "Generos",
};
const menuRoutes = routes.filter((route) => Object.keys(listRoutes).includes(route.name));
// @ts-ignore
export const Menu = () => {

    const [anchorEl, setAnchorEl] = useState(null);
    const open = Boolean(anchorEl);

    const handleOpen = (event: any) => {
        setAnchorEl(event.currentTarget);
    };
    const handleOnClose = () => {
        setAnchorEl(null);
    };

    return (
        // @ts-ignore
        <React.Fragment>
            <IconButton edge="start" color="inherit"
                        aria-label="open-drawer" aria-controls="menu-appbar"
                        aria-haspopup={true} onClick={handleOpen}>
                <MenuIcon />
            </IconButton>
            <MuiMenu id="menu-appbar" open={open} anchorEl={anchorEl} onClose={handleOnClose}
                  anchorOrigin={{ vertical: "bottom", horizontal: "center"}}
                  transformOrigin={{ vertical: "top", horizontal: "center"}}
                  getContentAnchorEl={null}>
                {Object.keys(listRoutes).map((routeName, key) => {
                    const route = menuRoutes.find((route) => route.name === routeName) as MyRouteProps;
                    return (
                        <MenuItem
                            key={key} to={route.path as string}
                            component={Link} onClick={handleOnClose}
                        >
                            {listRoutes[routeName]}
                        </MenuItem>
                    );
                })}
            </MuiMenu>
        </React.Fragment>
    );
};