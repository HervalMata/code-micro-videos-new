import {
    SnackbarProvider as NotistackProvider,
    SnackbarProviderProps
} from "notistack";
import {makeStyles} from "@material-ui/core/styles";
import {Theme} from "@material-ui/core";
import {IconButton} from "@material-ui/core/index";
import CloseIcon from "@material-ui/icons/Close";

const useStyles = makeStyles((theme: Theme) => {
    return {
        variantSuccess: {
            backgroundColor: theme.palette.success.main,
        },
        variantError: {
            backgroundColor: theme.palette.error.main,
        },
        variantInfo: {
            backgroundColor: theme.palette.info.main,
        },
    };
});

export const SnackbarProvider: React.FC<SnackbarProviderProps> = (props) => {
    let snackbarProviderRef;
    const classes = useStyles();
    const defaultProps: SnackbarProviderProps = {
        classes, autoHideDuration:3000, maxSnack: 3,
        children: props.children,
        anchorOrigin: {
            horizontal: "right", vertical: "top",
        },
        ref: (el) => {
            snackbarProviderRef = el;
        },
        action: (key) => {
            return (
                <IconButton
                    onClick={() => {
                        snackbarProviderRef.closeSnackbar(key);
                    }}
                    color={"inherit"}
                    style={{ fontSize: 20 }}
                    >
                    <CloseIcon />
                </IconButton>
            );
        },
    };
    const newProps = { ...defaultProps, ...props };
    return <NotistackProvider { ...newProps }>{props.children}</NotistackProvider>
};