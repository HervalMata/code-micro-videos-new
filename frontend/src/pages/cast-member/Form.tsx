import React, {useEffect} from 'react';
import {Button, ButtonProps, FormControl, FormControlLabel, FormLabel, RadioGroup, TextField, Radio} from "@material-ui/core";
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
        variant: "outlined", size: "medium", className: classes.submit,
    };
    const { register, getValues, handleSubmit, setValue } = useForm();

    useEffect(() => {
        register({ name: "type" });
    }, [register]);

    function onSubmit(formData, event) {
        httpCategory.create(formData).then(console.log);
    }

    // @ts-ignore
    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField inputRef={register} name="name" label="Nome" fullWidth variant="outlined" />
            <FormControl margin="normal">
                <FormLabel component="legend">Tipo</FormLabel>
                <RadioGroup name="type" onChange={(e) => {
                    setValue("type", parseInt(e.target.value));
                }}>
                    <FormControlLabel control={<Radio />} label="Diretor" value="1" />
                    <FormControlLabel control={<Radio />} label="Ator" value="2" />
                </RadioGroup>
            </FormControl>
            <Box dir={"rtl"}>
                <Button {...buttonProps} onClick={() => onSubmit(getValues(), null)}>Salvar</Button>
                <Button {...buttonProps} type="submit">Salvar e continuar editando</Button>
            </Box>
        </form>
    );
};

export default Form;