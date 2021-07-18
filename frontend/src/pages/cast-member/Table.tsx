import * as React from 'react';
import { useEffect, useState } from 'react';
import { MUIDataTableColumn } from 'mui-datatables';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import { CastMember } from '../../interfaces/cast-member';
import castMemberResource from '../../resource/cast-member.resource';
import { ResponseList } from '../../interfaces/interfaces';
import { Badge } from '../../components/Badge';
import { Mapper } from '../../util/mapper';
import DefaultTable from '../../components/Table';

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
        return <Badge value={ obj }/>;
      },
    },
  },
  {
    name: 'created_at',
    label: 'Criado em',
    options: {
      customBodyRender(value) {
        return <span>{ format(parseISO(value), 'dd/mm/yyyy') }</span>;
      },
    },
  },
];

const Table = () => {

  const [data, setData] = useState<CastMember[]>([]);

  useEffect(() => {
    let isSubscribed = true;
    (async function list() {
      const { data } = await castMemberResource.list<ResponseList<CastMember>>();
      if (isSubscribed) {
        setData(data.data);
      }
    })();

    return () => {
      isSubscribed = false;
    };
  }, []);

  return (
      <DefaultTable
          title=""
          columns={ columnsDefinition }
          data={ data }
      />
  );
};
export default Table;
