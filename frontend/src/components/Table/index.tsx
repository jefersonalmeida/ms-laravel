import * as React from 'react';
import MUIDataTable, {
    MUIDataTableColumn,
    MUIDataTableOptions,
    MUIDataTableProps,
} from 'mui-datatables';
import * as _ from 'lodash';
import { MuiThemeProvider, useMediaQuery, useTheme } from '@material-ui/core';
import { Theme } from '@material-ui/core/styles';

const defaultOptions: MUIDataTableOptions = {
    print: false,
    download: false,
    textLabels: {
        body: {
            noMatch: 'Nenhum registro encontrado',
            toolTip: 'Classificar',
        },
        pagination: {
            next: 'Próxima página',
            previous: 'Página anterior',
            rowsPerPage: 'Por página:',
            displayRows: 'de',
        },
        toolbar: {
            search: 'Busca',
            downloadCsv: 'Download CSV',
            print: 'Imprimir',
            viewColumns: 'Ver Colunas',
            filterTable: 'Filtrar Tabelas',
        },
        filter: {
            all: 'Todos',
            title: 'FILTROS',
            reset: 'LIMPAR',
        },
        viewColumns: {
            title: 'Ver Colunas',
            titleAria: 'Ver/Esconder Colunas da Tabela',
        },
        selectedRows: {
            text: 'registros(s) selecionados',
            delete: 'Excluir',
            deleteAria: 'Excluir registros selecionados',
        },
    },
};

export interface TableColumn extends MUIDataTableColumn {
    width?: string;
}
interface TableProps extends MUIDataTableProps {
    columns: TableColumn[];
    loading?: boolean;
}
const Table: React.FC<TableProps> = (props) => {

    function extractMuiDataTableColumns(columns: TableColumn[]): MUIDataTableColumn[] {
        setColumnsWidth(columns);
        return columns.map(column => _.omit(column, 'width'));
    }

    function setColumnsWidth(columns: TableColumn[]) {
        columns.forEach((column, key) => {
            if (column.width) {
                const overrides = theme.overrides as any;
                overrides.MUIDataTableHeadCell.fixedHeader[`&:nth-child(${ key +
                2 })`] = {
                    width: column.width,
                };
            }
        });
    }

    function applyLoading() {
        const textLabels = (newProps.options as any).textLabels as any;
        textLabels.body.noMatch = newProps.loading === true
            ? 'Carregando...'
            : textLabels.body.noMatch;
    }

    function applyResponsive() {
        newProps.options.responsive = isSmOrDown ? 'vertical' : 'standard';
    }

    function getOriginalMuiDataTableProps() {
        return _.omit(newProps, 'loading');
    }

    const theme = _.cloneDeep<Theme>(useTheme());
    const isSmOrDown = useMediaQuery(theme.breakpoints.down('sm'));
    console.log(isSmOrDown);

    const newProps = _.merge(
        { options: _.cloneDeep(defaultOptions) },
        props,
        { columns: extractMuiDataTableColumns(props.columns) },
    );

    applyLoading();
    applyResponsive();

    const originalProps = getOriginalMuiDataTableProps();

    return (
        <MuiThemeProvider theme={ theme }>
            <MUIDataTable { ...originalProps }/>
        </MuiThemeProvider>
    );
};

export default Table;
