import * as React from 'react';
import MenuIcon from '@material-ui/icons/Menu';
import {IconButton, Menu as MuiMenu, MenuItem} from '@material-ui/core';
import routes, {MyRouteProps} from '../../routes';
import {Link} from 'react-router-dom';

const listRoutes: any = {
  'dashboard': 'Dashboard',
  'categories.list': 'Categorias',
  'cast-members.list': 'Membros de elenco',
  'genres.list': 'Gêneros',
};
const menuRoutes = routes.filter(r => Object.keys(listRoutes).includes(r.name));

const Menu = () => {

  const [anchorEl, setAnchorEl] = React.useState(null);
  const open = Boolean(anchorEl);

  const handleOpen = (event: any) => setAnchorEl(event.currentTarget);
  const handleClose = () => setAnchorEl(null);

  return (
      <React.Fragment>
        <IconButton
            edge="start"
            color="inherit"
            aria-label="open drawer"
            aria-controls="menu-appbar"
            aria-haspopup="true"
            onClick={handleOpen}
        >
          <MenuIcon/>
        </IconButton>
        <MuiMenu
            id="menu-appbar"
            open={open}
            anchorEl={anchorEl}
            onClose={handleClose}
            anchorOrigin={{vertical: 'bottom', horizontal: 'center'}}
            transformOrigin={{vertical: 'top', horizontal: 'center'}}
            getContentAnchorEl={null}
        >
          {
            Object.keys(listRoutes).map((routeName, key) => {
              const route = menuRoutes.find(
                  route => route.name === routeName) as MyRouteProps;
              return (
                  <MenuItem key={key}
                            component={Link}
                            to={route.path as string}
                            onClick={handleClose}
                  >
                    {listRoutes[routeName]}
                  </MenuItem>
              );
            })
          }
        </MuiMenu>
      </React.Fragment>
  );
};

export default Menu;
