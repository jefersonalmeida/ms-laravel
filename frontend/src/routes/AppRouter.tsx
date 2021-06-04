import * as React from 'react';
import {Route, Switch} from 'react-router';
import routes from './index';

const AppRouter = () => {
  return (
      <Switch>
        {
          routes.map((route, key) => (
              <Route
                  key={key}
                  path={route.path}
                  component={route.component}
                  exact={route.exact === true}
              />
          ))
        }
      </Switch>
  );
};
export default AppRouter;
