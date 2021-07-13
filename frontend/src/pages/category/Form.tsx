import * as React from 'react';
import { useEffect, useState } from 'react';
import { Checkbox, FormControlLabel, TextField } from '@material-ui/core';
import { useForm } from 'react-hook-form';
import categoryResource from '../../resource/category.resource';
import * as yup from 'yup';
import { yupResolver } from '@hookform/resolvers/yup';
import { useHistory, useParams } from 'react-router';
import { useSnackbar } from 'notistack';
import { Category } from '../../interfaces/category';
import SubmitActions from '../../components/SubmitActions';
import DefaultForm from '../../components/DefaultForm';

const validationSchema = yup.object().shape({
  name: yup.string()
      .required(),
});

const Form = () => {

  const {
    register,
    handleSubmit,
    getValues,
    setValue,
    formState: { errors },
    reset,
    watch,
    trigger,
  } = useForm<Category>({
    resolver: yupResolver(validationSchema),
    defaultValues: {
      is_active: true,
    },
  });

  const snackbar = useSnackbar();
  const history = useHistory();
  const { id } = useParams<any>();
  const [entity, setEntity] = useState<Category | null>(null);
  const [loading, setLoading] = useState<boolean>(false);

  useEffect(() => {
    if (!id) {
      return;
    }

    (async function getEntity() {
      setLoading(true);
      try {
        const { data } = await categoryResource.get(id);
        setEntity(data.data);
        reset(data.data);

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

  }, [id, reset, snackbar]);

  useEffect(() => {
    register('is_active');
  }, [register]);

  async function onSubmit(formData: any, event: any) {
    setLoading(true);

    try {

      const { data } = !entity
          ? await categoryResource.create(formData)
          : await categoryResource.update(entity.id, formData);

      snackbar.enqueueSnackbar(
          `${ data.data.name } salva com sucesso!`,
          { variant: 'success' },
      );

      setTimeout(() => {
        event
            ? (
                id ? history.replace(`/categories/${ data.data.id }/edit`)
                    : history.push(`/categories/${ data.data.id }/edit`)
            )
            : history.push('/categories');
      });
    } catch (e) {
      console.log(e);
      snackbar.enqueueSnackbar(
          `Erro ao processar a solicitação`,
          { variant: 'error' },
      );
    } finally {
      setLoading(false);
    }
  }

  return (
      <DefaultForm onSubmit={ handleSubmit(onSubmit) } GridItemProps={{xs: 12, md: 6}}>
        <TextField
            { ...register('name') }
            name={ 'name' }
            label={ 'Nome' }
            fullWidth
            variant={ 'outlined' }
            disabled={ loading }
            error={ errors?.name !== undefined }
            helperText={ errors?.name?.message }
            InputLabelProps={ { shrink: true } }
        />
        <TextField
            { ...register('description') }
            name={ 'description' }
            label={ 'Descrição' }
            fullWidth
            variant={ 'outlined' }
            margin={ 'normal' }
            multiline
            rows={ 3 }
            disabled={ loading }
            InputLabelProps={ { shrink: true } }
        />
        <FormControlLabel
            disabled={ loading }
            name={ 'is_active' }
            label={ 'Ativo?' }
            labelPlacement={ 'end' }
            control={
              <Checkbox
                  name={ 'is_active' }
                  color={ 'primary' }
                  checked={ watch('is_active') }
                  onChange={
                    () => setValue('is_active', !getValues('is_active'))
                  }
              />
            }
        />
        <SubmitActions
            disabledButtons={ loading }
            handleSave={ () =>
                trigger().then(isValid => {
                      isValid && onSubmit(getValues(), null);
                    },
                )
            }
        />
      </DefaultForm>
  );
};
export default Form;
