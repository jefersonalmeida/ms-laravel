import * as React from 'react';
import {useEffect, useState} from 'react';
import MUIDataTable, {MUIDataTableColumn} from 'mui-datatables';
import {Chip} from '@material-ui/core';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import {Mapper} from '../../util/mapper';
import {CastMember} from '../../interfaces/cast-member';
import castMemberResource from '../../resource/cast-member.resource';
import {ResponseEntity} from '../../interfaces/interfaces';

const columnsDefinition: MUIDataTableColumn[] = [
  {
    name: 'name',
    label: 'Nome',
  },
  {
    name: 'type',
    label: 'Tipo',
    options: {
      customBodyRender(value) {
        const obj = Mapper.members.find(r => r.value === value);
        return <Chip
            label={obj?.label || ''}
            color={obj?.color || 'primary'}
        />;
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

  const [data, setData] = useState<CastMember[]>([]);

  useEffect(() => {
    castMemberResource
        .list<ResponseEntity<CastMember[]>>()
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
