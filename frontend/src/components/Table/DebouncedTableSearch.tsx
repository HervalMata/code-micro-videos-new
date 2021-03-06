import * as React from 'react';
import {makeStyles} from "@material-ui/core/styles";
import { Grow, TextField} from "@material-ui/core";
import {useEffect, useRef, useState} from "react";
import {IconButton} from "@material-ui/core/index";
import ClearIcon from "@material-ui/icons/Clear";
import SearchIcon from "@material-ui/icons/Search";
import {debounce} from "lodash";

const useStyles = makeStyles(
    (theme) => ({
        main: {
            display: "flex",
            flex: "1 0 auto",
        },
        searchIcon: {
            color: theme.palette.text.secondary,
            marginTop: "10px",
            marginRight: "8px",
        },
        searchText: {
            flex: "0.8 0",
        },
        clearIcon: {
            "&:hover": {
                color: theme.palette.error.main,
            },
        },
    }),
    { name: "MUIDataTableSearch" }
);

export const DebouncedTableSearch = ({ options, searchText, onSearch,
                                         onHide, debounceTime }) => {
    const classes = useStyles();
    const [text, setText] = useState(searchText);
    const firstValue = useRef(searchText);
    const debounced = useRef(
        debounce((text, onSearch) => {
            onSearch(text);
        }, debounceTime || 500)
    );

    const handleTextChange = (event) => {
        setText(event.target.value);
        // @ts-ignore
        debounced.current(event.target.value, onSearch);
    };

    useEffect(() => {
        if (!searchText || searchText === firstValue.current) {
            setText(searchText);
        }
        if (searchText && searchText.update) {
            setText(null);
            onHide();
        }
    }, [searchText, onHide]);

    const onKeyDown = (event) => {
        if (event.key === "Escape") {
            onHide();
        }
    };

    return (
        <Grow appear in={true} timeout={300}>
            <div className={classes.main}>
                {JSON.stringify(firstValue)}
                <SearchIcon className={classes.searchIcon} />
                <TextField
                    className={classes.searchText} autoFocus={true}
                    InputProps={{
                        "data-test-id": options.textLabels.toolbar.search,
                    }}
                    inputProps={{
                        "aria-label": options.textLabels.toolbar.search,
                    }}
                    value={text || ""} onKeyDown={onKeyDown}
                    onChange={handleTextChange} fullWidth={true}
                    placeholder={options.searchPlaceholder}
                    {...(options.searchProps ? options.searchProps : {})}
                    />
                <IconButton className={classes.clearIcon} onClick={onHide}>
                    <ClearIcon />
                </IconButton>
            </div>
        </Grow>
    );
};