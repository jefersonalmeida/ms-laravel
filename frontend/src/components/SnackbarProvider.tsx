import * as React from 'react';
import {
  SnackbarProvider as SnackbarMainProvider,
  SnackbarProviderProps,
} from 'notistack';
import { IconButton } from '@material-ui/core';
import CloseIcon from '@material-ui/icons/Close';
import { makeStyles, Theme } from '@material-ui/core/styles';


const useStyles = makeStyles((theme: Theme) => {
  return {
    variantSuccess: {
      backgroundColor: theme.palette.success.main
    },
    variantError: {
      backgroundColor: theme.palette.error.main
    },
    variantInfo: {
      backgroundColor: theme.palette.primary.main
    },
  }
});

const SnackbarProvider: React.FC<SnackbarProviderProps> = (props) => {
  let snackbarProviderRef: SnackbarMainProvider | null;
  const classes = useStyles();
  const defaultProps: SnackbarProviderProps = {
    classes,
    children: undefined,
    autoHideDuration: 3000,
    maxSnack: 3,
    anchorOrigin: {
      horizontal: 'right',
      vertical: 'top',
    },
    ref: (el) => snackbarProviderRef = el,
    action: (key) => (
        <IconButton
            color={ 'inherit' }
            style={ { fontSize: 20 } }
            onClick={ () => snackbarProviderRef &&
                snackbarProviderRef.closeSnackbar(key) }
        >
          <CloseIcon/>
        </IconButton>
    )
  };

  const newProps = { ...props, ...defaultProps };

  return (
      <SnackbarMainProvider { ...newProps }>
        { props.children }
      </SnackbarMainProvider>
  );
};
export default SnackbarProvider;
