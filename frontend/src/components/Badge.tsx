import React from 'react';
import {Chip, createMuiTheme, MuiThemeProvider} from "@material-ui/core";
import theme from "../theme";

const Badgetheme = createMuiTheme({
    palette: {
        primary: theme.palette.success,
        secondary: theme.palette.error,
    }
});

export const BadgeYes = () => {
    return (
        <MuiThemeProvider theme={Badgetheme}>
            <Chip label={"SIM"} color={"primary"} />
        </MuiThemeProvider>
    );
};

export const BadgeNo = () => {
    return (
        <MuiThemeProvider theme={Badgetheme}>
            <Chip label={"NÂO"} color={"secondary"} />
        </MuiThemeProvider>
    );
};