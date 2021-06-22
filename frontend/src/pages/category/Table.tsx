import * as React from 'react';
import {useEffect, useState} from 'react';
import MUIDataTable, {MUIDataTableColumn} from 'mui-datatables';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import {Mapper} from '../../util/mapper';
import categoryResource from '../../resource/category.resource';
import {Category} from '../../interfaces/category';
import {ResponseEntity} from '../../interfaces/interfaces';
import {BadgeActive} from '../../components/Badge';

const columnsDefinition: MUIDataTableColumn[] = [
  {
    name: 'name',
    label: 'Nome',
  },
  {
    name: 'is_active',
    label: 'Ativo?',
    options: {
      customBodyRender(value) {
        const obj = Mapper.actives.find(r => r.value === value);
        return <BadgeActive obj={obj}/>;
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

  const [data, setData] = useState<Category[]>([]);

  useEffect(() => {
    categoryResource
        .list<ResponseEntity<Category[]>>()
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
