import * as React from 'react';
import {Chip, createMuiTheme, MuiThemeProvider} from '@material-ui/core';
import theme from '../theme';
import {DataObject} from '../interfaces/interfaces';

const badgeTheme = createMuiTheme({
  palette: {
    primary: theme.palette.success,
    secondary: theme.palette.error,
  },
});

export const Badge = (props: DataObject) => {
  return (
      <MuiThemeProvider theme={badgeTheme}>
        <Chip
            label={props?.value?.label || ''}
            color={props?.value?.color || 'primary'}
        />
      </MuiThemeProvider>
  );
};
