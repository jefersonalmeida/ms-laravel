import * as React from 'react';
import { useEffect, useState } from 'react';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import genreResource from '../../resource/genre.resource';
import { Genre } from '../../interfaces/genre';
import { ResponseList } from '../../interfaces/interfaces';
import { Badge } from '../../components/Badge';
import { Mapper } from '../../util/mapper';
import DefaultTable, { makeActionsStyles, TableColumn } from '../../components/Table';
import { useSnackbar } from 'notistack';
import { IconButton } from '@material-ui/core';
import { Link } from 'react-router-dom';
import EditIcon from '@material-ui/icons/Edit';
import { MuiThemeProvider } from '@material-ui/core/styles';

const columnsDefinition: TableColumn[] = [
  {
    name: 'id',
    label: 'ID',
    options: {
      sort: false,
    },
    width: '30%',
  },
    {
        name: 'name',
        label: 'Nome',
        width: '20%',
    },
    {
        name: 'categories',
        label: 'Categorias',
        options: {
            customBodyRender(value) {
                return value.map((r: any) => r.name).join(', ');
            },
        },
        width: '20%',
    },
    {
        name: 'is_active',
        label: 'Ativo?',
        options: {
            customBodyRender(value) {
                const obj = Mapper.actives.find(r => r.value === value);
                return <Badge value={ obj }/>;
            },
        },
        width: '7%',
    },
    {
        name: 'created_at',
        label: 'Criado em',
        options: {
            customBodyRender(value) {
                return <span>{ format(parseISO(value), 'dd/mm/yyyy') }</span>;
            },
        },
        width: '10%',
    },
  {
    name: 'actions',
    label: 'Ações',
    width: '13%',
    options: {
      sort: false,
      customBodyRender(value, tableMeta) {
        return (
            <IconButton
                color={ 'secondary' }
                component={ Link }
                to={ `genres/${ tableMeta.rowData[0] }/edit` }
            
            >
              <EditIcon/>
            </IconButton>
        );
      },
    },
  },
];

const Table = () => {
    const snackbar = useSnackbar();
    const [data, setData] = useState<Genre[]>([]);
    const [loading, setLoading] = useState<boolean>(false);

    useEffect(() => {
        let isSubscribed = true;
        (async () => {
            setLoading(true);
            try {
                const { data } = await genreResource.list<ResponseList<Genre>>();
                if (isSubscribed) {
                    setData(data.data);
                }
            } catch (e) {
                console.error(e);
                snackbar.enqueueSnackbar(
                    `não foi possível carregar as informações`,
                    { variant: 'error' },
                );
            } finally {
                setLoading(false);
            }
        })();

        return () => {
            isSubscribed = false;
        };
    }, [snackbar]);

    return (
        <MuiThemeProvider theme={ makeActionsStyles(columnsDefinition.length - 1) }>
          <DefaultTable
              title=""
              columns={ columnsDefinition }
              data={ data }
              loading={ loading }
          />
        </MuiThemeProvider>
    );
};
export default Table;
