import * as React from 'react';
import {Mapper} from '../util/mapper';
import {Chip, createMuiTheme, MuiThemeProvider} from '@material-ui/core';
import theme from '../theme';

const badgeTheme = createMuiTheme({
  palette: {
    primary: theme.palette.success,
    secondary: theme.palette.error,
  },
});

interface BadgeProps {
  obj: any;
}

export const BadgeActive = (props: BadgeProps) => {
  console.log(props);
  const obj = Mapper.actives.find(r => r.value === props.obj.value);
  return (
      <MuiThemeProvider theme={badgeTheme}>
        <Chip
            label={obj?.label || ''}
            color={obj?.color || 'primary'}
        />
      </MuiThemeProvider>
  );
};

export const BadgeMember = (props: BadgeProps) => {
  console.log(props);
  const obj = Mapper.members.find(r => r.value === props.obj.value);
  return (
      <MuiThemeProvider theme={badgeTheme}>
        <Chip
            label={obj?.label || ''}
            color={obj?.color || 'primary'}
        />
      </MuiThemeProvider>
  );
};
