import React from 'react';
import logo from './logo.svg';
import './App.css';
import {Navbar} from "./components/Navbar";
import {Box} from "@material-ui/core/index";
import {Page} from "./components/Page";

function App() {
  return (
      <React.Fragment>
        <Navbar />
        <Box paddingTop={'70px'}>
          <Page title={"Categorias"}>
            Conteúdo
          </Page>
        </Box>
      </React.Fragment>

  );
}

export default App;
