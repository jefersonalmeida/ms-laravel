import * as React from 'react';
import {useEffect, useState} from 'react';
import MUIDataTable, {MUIDataTableColumn} from 'mui-datatables';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import genreResource from '../../resource/genre.resource';
import {Genre} from '../../interfaces/genre';
import {ResponseEntity} from '../../interfaces/interfaces';
import {Badge} from '../../components/Badge';
import {Mapper} from '../../util/mapper';

const columnsDefinition: MUIDataTableColumn[] = [
  {
    name: 'name',
    label: 'Nome',
  },
  {
    name: 'categories',
    label: 'Categorias',
    options: {
      customBodyRender(value) {
        return value.map((r: any) => r.name).join(', ');
      },
    },
  },
  {
    name: 'is_active',
    label: 'Ativo?',
    options: {
      customBodyRender(value) {
        const obj = Mapper.actives.find(r => r.value === value);
        return <Badge value={obj}/>;
      },
    },
  },
  {
    name: 'created_at',
    label: 'Criado em',
    options: {
      customBodyRender(value) {
        return <span>{format(parseISO(value), 'dd/mm/yyyy')}</span>;
      },
    },
  },
];

interface TableProps {

}

const Table = (props: TableProps) => {

  const [data, setData] = useState<Genre[]>([]);

  useEffect(() => {
    genreResource
        .list<ResponseEntity<Genre[]>>()
        .then(({data}) => setData(data.data));
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
