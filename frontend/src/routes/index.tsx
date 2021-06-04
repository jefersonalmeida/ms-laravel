import {RouteProps} from 'react-router';
import Dashboard from '../pages/Dashboard';
import CategoryList from '../pages/category/PageList';
import CategoryForm from '../pages/category/PageForm';
import CastMemberList from '../pages/cast-member/PageList';
import CastMemberForm from '../pages/cast-member/PageForm';
import GenreList from '../pages/genre/PageList';
import GenreForm from '../pages/genre/PageForm';

export interface MyRouteProps extends RouteProps {
  name: string;
  label: string;
}

const routes: MyRouteProps[] = [
  {
    name: 'dashboard',
    label: 'Dashboard',
    path: '/',
    component: Dashboard,
    exact: true,
  },
  {
    name: 'categories.list',
    label: 'Listar Categorias',
    path: '/categories',
    component: CategoryList,
    exact: true,
  },
  {
    name: 'categories.create',
    label: 'Criar Categoria',
    path: '/categories/create',
    component: CategoryForm,
    exact: true,
  },
  {
    name: 'cast-members.list',
    label: 'Listar Membros',
    path: '/cast-members',
    component: CastMemberList,
    exact: true,
  },
  {
    name: 'cast-members.create',
    label: 'Criar Membro',
    path: '/cast-members/create',
    component: CastMemberForm,
    exact: true,
  },
  {
    name: 'genres.list',
    label: 'Listar Gêneros',
    path: '/genres',
    component: GenreList,
    exact: true,
  },
  {
    name: 'genres.create',
    label: 'Criar Gênero',
    path: '/genres/create',
    component: GenreForm,
    exact: true,
  },
];

export default routes;
