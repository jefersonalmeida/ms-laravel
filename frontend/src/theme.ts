import {createMuiTheme, SimplePaletteColorOptions} from '@material-ui/core';
import {PaletteOptions} from '@material-ui/core/styles/createPalette';

const palette: PaletteOptions = {
  primary: {
    main: '#79AEC8',
    contrastText: '#FFFFFF',
  },
  secondary: {
    main: '#4DB5AB',
    contrastText: '#FFFFFF',
  },
  background: {
    default: '#FAFAFA',
  },
};
const theme = createMuiTheme({
  palette,
  overrides: {
    MUIDataTable: {
      paper: {
        boxShadow: 'none',
      },
    },
    MUIDataTableToolbar: {
      root: {
        minHeight: '58px',
        backgroundColor: palette!.background!.default,
      },
      icon: {
        color: (palette!.primary as SimplePaletteColorOptions).main,
        '&:hover, &:active, &:focus': {
          color: '#055A52',
        },
      },
      iconActive: {
        color: '#055A52',
        '&:hover, &:active, &:focus': {
          color: '#055A52',
        },
      },
    },
    MUIDataTableHeadCell: {
      fixedHeader: {
        paddingTop: 7,
        paddingBottom: 7,
        backgroundColor: (palette!.primary as SimplePaletteColorOptions).main,
        color: '#FFFFFF',
        '&[aria-sort]': {
          backgroundColor: '#459AC4',
        },
      },
      sortActive: {
        color: '#FFFFFF',
      },
      sortAction: {
        alignItems: 'center',
        color: '#FFFFFF',
      },
      sortLabelRoot: {
        '& svg': {
          color: '#FFFFFF !important',
        },
      },
    },
    MUIDataTableSelectCell: {
      headerCell: {
        backgroundColor: (palette!.primary as SimplePaletteColorOptions).main,
        '& span': {
          color: '#FFFFFF !important',
        },
      },
    },
    MUIDataTableBodyCell: {
      root: {
        color: (palette!.secondary as SimplePaletteColorOptions).main,
        '&:hover, &:active, &:focus': {
          color: (palette!.secondary as SimplePaletteColorOptions).main,
        },
      },
    },
    MUIDataTableToolbarSelect: {
      title: {
        color: (palette!.primary as SimplePaletteColorOptions).main,
      },
      iconButton: {
        color: (palette!.primary as SimplePaletteColorOptions).main,
      },
    },
    MUIDataTableBodyRow: {
      root: {
        // color: (palette!.secondary as SimplePaletteColorOptions).main,
        '&:nth-child(odd)': {
          backgroundColor: palette!.background!.default,
        },
      },
    },
    MUIDataTablePagination: {
      root: {
        color: (palette!.primary as SimplePaletteColorOptions).main,
      }
    }
  },
});

export default theme;
