import * as React from 'react';
import { useEffect, useState } from 'react';
import {
  FormControl,
  FormControlLabel,
  FormHelperText,
  FormLabel,
  Radio,
  RadioGroup,
  TextField,
} from '@material-ui/core';
import { useForm } from 'react-hook-form';
import castMemberResource from '../../resource/cast-member.resource';
import * as yup from 'yup';
import { yupResolver } from '@hookform/resolvers/yup';
import { useSnackbar } from 'notistack';
import { useHistory, useParams } from 'react-router';
import SubmitActions from '../../components/SubmitActions';
import DefaultForm from '../../components/DefaultForm';

const validationSchema = yup.object().shape({
  name: yup.string()
      .label('Nome')
      .required()
      .max(255),
  type: yup.number()
      .label('Tipo')
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
  } = useForm<any>({
    resolver: yupResolver(validationSchema),
  });

  const snackbar = useSnackbar();
  const history = useHistory();
  const { id } = useParams<any>();
  const [entity, setEntity] = useState<{ id: string } | null>(null);
  const [loading, setLoading] = useState<boolean>(false);

  useEffect(() => {
    if (!id) {
      return;
    }

    (async function getEntity() {
      setLoading(true);
      try {
        const { data } = await castMemberResource.get(id);
        setEntity(data.data);
        reset(data.data);

      } catch (e) {
        console.log(e);
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
    register('type');
  }, [register]);

  async function onSubmit(formData: any, event: any) {
    setLoading(true);

    try {

      const request = !entity
          ? castMemberResource.create(formData)
          : castMemberResource.update(entity.id, formData);

      const { data } = await request;

      snackbar.enqueueSnackbar(
          `${ data.data.name } salvo com sucesso!`,
          { variant: 'success' },
      );

      setTimeout(() => {
        event
            ? (
                id ? history.replace(`/cast-members/${ data.data.id }/edit`)
                    : history.push(`/cast-members/${ data.data.id }/edit`)
            )
            : history.push('/cast-members');
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
            value={ watch('name') }
            name={ 'name' }
            label={ 'Nome' }
            fullWidth
            variant={ 'outlined' }
            disabled={ loading }
            error={ errors?.name !== undefined }
            helperText={ errors?.name?.message }
            InputLabelProps={ { shrink: true } }
        />

        <FormControl
            margin={ 'normal' }
            error={ errors?.name !== undefined }
            disabled={ loading }
        >
          <FormLabel component={ 'legend' }>Tipo</FormLabel>
          <RadioGroup
              name={ 'type' }
              onChange={
                (e) => {
                  setValue('type', parseInt(e.target.value));
                }
              }
              value={ watch('type') + '' }>
            <FormControlLabel
                label="Diretor"
                value={ '1' }
                control={
                  <Radio color={ 'primary' }/>
                }
            />
            <FormControlLabel
                label="Ator"
                value={ '2' }
                control={
                  <Radio color={ 'primary' }/>
                }
            />
          </RadioGroup>
          {
            errors.type &&
            <FormHelperText>{ errors.type.message }</FormHelperText>
          }
        </FormControl>
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
