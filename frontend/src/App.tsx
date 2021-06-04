import React from 'react';
import './App.css';
import {Navbar} from './components/Navbar';
import {Box} from '@material-ui/core';
import {BrowserRouter} from 'react-router-dom';
import AppRouter from './routes/AppRouter';
import Breadcrumbs from './components/Breadcrumbs';

const App = () => (
    <React.Fragment>
      <BrowserRouter>
        <Navbar/>
        <Box paddingTop={'90px'}>
          <Breadcrumbs/>
          <AppRouter/>
        </Box>
      </BrowserRouter>
    </React.Fragment>
);
export default App;
