import * as React from 'react';
import {makeStyles} from "@material-ui/core/styles";
import {Tooltip} from "@material-ui/core";
import {IconButton} from "@material-ui/core/index";
import ClearAllIcon from "@material-ui/icons/ClearAll";

interface FilterResetButtonProps {
    handleClick: () => void;
}

const useStyles = makeStyles((theme) => {
    return {
        iconButton: (theme as any).overrides.MUIDataTableToolbar.icon,
    };
});

export const FilterResetButton: React.FC<FilterResetButtonProps> = (props) => {
    const classes = useStyles();
    return (
        <Tooltip title="Limpar Pesquisa">
            <IconButton className={classes.iconButton} onClick={props.handleClick}>
                <ClearAllIcon />
            </IconButton>
        </Tooltip>
    );
};