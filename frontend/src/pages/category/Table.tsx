import * as React from 'react';
import {useEffect, useState} from 'react';
import MUIDataTable, {MUIDataTableColumn} from 'mui-datatables';
import {httpVideo} from '../../util/http';
import {Chip} from '@material-ui/core';

const columnsDefinition: MUIDataTableColumn[] = [
  {
    name: 'name',
    label: 'Nome',
  },
  {
    name: 'is_active',
    label: 'Ativo?',
    options: {
      customBodyRender(value, tableMeta, updateValue) {
        return <Chip label={value ? 'Sim' : 'Não'}
                     color={value ? 'primary' : 'secondary'}
        />;
      },
    },
  },
  {
    name: 'created_at',
    label: 'Criado em',
  },
];

interface TableProps {

}

const Table = (props: TableProps) => {

  const [data, setData] = useState([]);

  useEffect(() => {
    httpVideo.get('categories').then(response => setData(response.data.data));
  }, []);

  return (
      <MUIDataTable
          title=""
          columns={columnsDefinition}
          data={data}
      />
  );
};
export default Table;
