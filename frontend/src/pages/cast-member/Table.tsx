import * as React from 'react';
import { useEffect, useState } from 'react';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import { CastMember } from '../../interfaces/cast-member';
import castMemberResource from '../../resource/cast-member.resource';
import { ResponseList } from '../../interfaces/interfaces';
import { Badge } from '../../components/Badge';
import { Mapper } from '../../util/mapper';
import DefaultTable, { TableColumn } from '../../components/Table';
import { useSnackbar } from 'notistack';

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
        width: '37%',
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
        width: '10%',
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
    },
];

const Table = () => {
    const snackbar = useSnackbar();
    const [data, setData] = useState<CastMember[]>([]);
    const [loading, setLoading] = useState<boolean>(false);

    useEffect(() => {
        let isSubscribed = true;
        (async () => {
            setLoading(true);
            try {
                const { data } = await castMemberResource.list<ResponseList<CastMember>>();
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
        <DefaultTable
            title=""
            columns={ columnsDefinition }
            data={ data }
            loading={ loading }
        />
    );
};
export default Table;
