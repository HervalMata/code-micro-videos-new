import * as yup from "yup";

yup.setLocale({
    mixed: {
        required: (field) => {
            return `O Campo ${field.label} é Obrigatório`;
        },
        default: "Não é válido",
    },
    string: {
        max: ({ label, max }) => {
            return `${label} deve ter no máximo ${max} caracteres`;
        },
    },
    number: {
        max: ({ label, min }) => {
            return `${label} deve ser maior que ${min}`;
        },
    },
});

export default yup;