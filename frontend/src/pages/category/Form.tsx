import * as React from 'react';
import {
  Box,
  Button,
  ButtonProps,
  Checkbox,
  TextField,
} from '@material-ui/core';
import {makeStyles, Theme} from '@material-ui/core/styles';
import {SubmitHandler, useForm} from 'react-hook-form';
import categoryResource from '../../resource/category.resource';

const useStyles = makeStyles((theme: Theme) => {
  return {
    submit: {
      margin: theme.spacing(1),
    },
  };
});

const Form = () => {

  const classes = useStyles();

  const buttonProps: ButtonProps = {
    className: classes.submit,
    variant: 'outlined',
    size: 'medium',
  };

  const {register, handleSubmit, setValue, getValues} = useForm<any>({
    defaultValues: {
      is_active: true,
    },
  });

  const handleChange = () => setValue('is_active', !getValues('is_active'));

  const onSubmit: SubmitHandler<any> = (data, event) => {
    categoryResource.create(data)
        .then(response => console.log(response));
  };

  return (
      <form onSubmit={handleSubmit(onSubmit)}>
        <TextField
            {...register('name')}
            label={'Nome'}
            fullWidth
            variant={'outlined'}
        />
        <TextField
            {...register('description')}
            label={'Descrição'}
            multiline
            rows={3}
            fullWidth
            variant={'outlined'}
            margin={'normal'}
        />
        <Checkbox
            defaultChecked
            {...register('is_active')}
            onChange={handleChange}
        />
        Ativo?
        <Box dir={'rtl'}>
          <Button
              {...buttonProps}
              onClick={() => onSubmit(getValues())}
          >
            Salvar
          </Button>
          <Button
              {...buttonProps}
              type={'submit'}
          >
            Salvar e continuar
          </Button>
        </Box>
      </form>
  );
};
export default Form;
