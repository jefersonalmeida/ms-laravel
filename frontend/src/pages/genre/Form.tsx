import * as React from 'react';
import { useEffect, useState } from 'react';
import { Checkbox, MenuItem, TextField } from '@material-ui/core';
import { useForm } from 'react-hook-form';
import genreResource from '../../resource/genre.resource';
import { Category } from '../../interfaces/category';
import categoryResource from '../../resource/category.resource';
import * as yup from 'yup';
import { useSnackbar } from 'notistack';
import { useHistory, useParams } from 'react-router';
import { yupResolver } from '@hookform/resolvers/yup';
import { Genre } from '../../interfaces/genre';
import SubmitActions from '../../components/SubmitActions';
import DefaultForm from '../../components/DefaultForm';

const validationSchema = yup.object().shape({
  name: yup.string()
      .label('Nome')
      .required()
      .max(255),
  categories_id: yup.array()
      .label('Categorias')
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
  } = useForm<Genre>({
    resolver: yupResolver(validationSchema),
    defaultValues: {
      categories_id: [],
      is_active: true,
    },
  });

  const snackbar = useSnackbar();
  const history = useHistory();
  const { id } = useParams<any>();
  const [entity, setEntity] = useState<Genre | null>(null);
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState<boolean>(false);

  useEffect(() => {
    let isSubscribed = true;
    (async function loadData() {
      setLoading(true);
      const promises = [categoryResource.list()];
      if (id) {
        promises.push(genreResource.get(id));
      }

      try {
        const [
          categoriesResponse,
          entityResponse,
        ] = await Promise.all(promises);

        if (isSubscribed) {
          setCategories(categoriesResponse.data.data);

          if (id) {
            setEntity(entityResponse.data.data);
            const categories_id = entityResponse.data.data.categories
                .map((c: { id: any; }) => c.id);
            reset({
              ...entityResponse.data.data,
              categories_id,
            });
          }
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
  }, [id, reset, snackbar]);

  async function onSubmit(formData: any, event: any) {
    setLoading(true);

    try {

      const { data } = !entity
          ? await genreResource.create(formData)
          : await genreResource.update(entity.id, formData);

      snackbar.enqueueSnackbar(
          `${ data.data.name } salvo com sucesso!`,
          { variant: 'success' },
      );

      setTimeout(() => {
        event
            ? (
                id ? history.replace(`/genres/${ data.data.id }/edit`)
                    : history.push(`/genres/${ data.data.id }/edit`)
            )
            : history.push('/genres');
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
            label={ 'Nome' }
            fullWidth
            variant={ 'outlined' }
            disabled={ loading }
            error={ errors?.name !== undefined }
            helperText={ errors?.name?.message }
            InputLabelProps={ { shrink: true } }
        />
        <TextField
            select
            value={ watch('categories_id') }
            label={ 'Categorias' }
            margin={ 'normal' }
            variant={ 'outlined' }
            fullWidth
            onChange={ (e) => {
              setValue('categories_id', e.target.value);
            } }
            SelectProps={ { multiple: true } }
            disabled={ loading }
            error={ errors?.categories_id !== undefined }
            helperText={ errors?.categories_id &&
            errors.categories_id?.message }
            InputLabelProps={ { shrink: true } }
        >
          <MenuItem value={ '' } disabled>Selecione as categorias</MenuItem>
          {
            categories.map(category => (
                <MenuItem key={ category.id }
                          value={ category.id }>
                  { category.name }
                </MenuItem>
            ))
          }
        </TextField>
        <Checkbox
            defaultChecked
            { ...register('is_active') }
            onChange={ () => setValue('is_active', !getValues('is_active')) }
        />
        Ativo?
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
