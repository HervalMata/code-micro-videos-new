import * as React from 'react';
import {makeStyles} from "@material-ui/core/styles";
import {ButtonProps, Theme} from "@material-ui/core";
import {Box, Button} from "@material-ui/core/index";

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1),
        },
    };
});

interface SubmitActionProps {
    disableButtons: boolean;
    handleSave: () => {};
}

export const SubmitActions: React.FC<SubmitActionProps> = (props) => {
    const classes = useStyles();
    const buttonProps: ButtonProps = {
        variant: "contained", size: "medium",
        className: classes.submit, color: "secondary",
        disabled: props.disableButtons === undefined ? false : props.disableButtons,
    };
    return (
        <Box dir={"rtl"}>
            <Button {...buttonProps} onClick={props.handleSave}>
                Salvar
            </Button>
            <Button {...buttonProps} type="submit">
                Salvar e continuar editando
            </Button>
        </Box>
    );
};

export default SubmitActions;