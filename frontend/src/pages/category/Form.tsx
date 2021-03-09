import React from 'react';
import {Button, ButtonProps, Checkbox, TextField} from "@material-ui/core";
import {makeStyles, Theme} from "@material-ui/core/styles";
import {Box} from "@material-ui/core/index";
import {useForm} from "react-hook-form";
import httpCategory from "../../util/http/http-category";

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1),
        },
    };
});

export const Form = () => {
    const classes = useStyles();
    const buttonProps: ButtonProps = {
        variant: "contained", size: "medium",
        className: classes.submit, color: "secondary",
    };
    const { register, getValues, handleSubmit } = useForm({
        defaultValues: {
            is_active: true,
        },
    });
    function onSubmit(formData, event) {
        httpCategory.create(formData).then(console.log);
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField inputRef={register} name="name" label="Nome" fullWidth variant="outlined" />
            <TextField inputRef={register} name="description" label="Descrição" margin="normal" fullWidth multiline rows={5} />

            <Checkbox
                inputRef={register}
                color={"primary"}
                name="is_active"
                defaultChecked={true}
            />
            Ativa?
            <Box dir={"rtl"}>
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Salvar</Button>
                <Button {...buttonProps} type="submit">Salvar e continuar editando</Button>
            </Box>
        </form>
    );
};

export default Form;