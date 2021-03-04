// @ts-ignore
import React, { useState} from "react";
import {IconButton, Menu as MuiMenu, MenuItem} from "@material-ui/core/index";
import { Menu as MenuIcon } from '@material-ui/icons'

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
                <MenuItem>Categorias</MenuItem>
                <MenuItem>Membros</MenuItem>
                <MenuItem>Generos</MenuItem>
            </MuiMenu>
        </React.Fragment>
    );
};