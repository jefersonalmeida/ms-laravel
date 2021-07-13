import * as React from 'react';
import { Page } from '../../components/Page';
import Form from './Form';
import { useParams } from 'react-router';

const PageForm = () => {
  const { id } = useParams<any>();
  return (
      <Page title={ !id ? 'Criar membro' : 'Editar membro' }>
        <Form/>
      </Page>
  );
};

export default PageForm;
