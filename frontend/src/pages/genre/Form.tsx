import * as React from 'react';
import {useEffect, useState} from 'react';
import {
  Box,
  Button,
  ButtonProps,
  Checkbox,
  MenuItem,
  TextField,
} from '@material-ui/core';
import {makeStyles, Theme} from '@material-ui/core/styles';
import {SubmitHandler, useForm} from 'react-hook-form';
import genreResource from '../../resource/genre.resource';
import {Category} from '../../interfaces/category';
import categoryResource from '../../resource/category.resource';
import {ResponseEntity} from '../../interfaces/interfaces';

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
    color: 'secondary',
    variant: 'contained',
  };

  const [categories, setCategories] = useState<Category[]>([]);
  const {register, handleSubmit, getValues, setValue, watch} = useForm<any>({
    defaultValues: {
      categories_id: [],
      is_active: true,
    },
  });
  const onSubmit: SubmitHandler<any> = (data, event) => {
    genreResource.create(data)
        .then(response => console.log(response));
  };

  const handleChangeActive = () => {
    setValue('is_active', !getValues('is_active'));
  };

  /*useEffect(() => {
    register('categories_id');
  }, [register]);*/

  useEffect(() => {
    categoryResource
        .list<ResponseEntity<Category[]>>()
        .then(({data}) => setCategories(data.data));
  }, []);

  return (
      <form onSubmit={handleSubmit(onSubmit)}>
        <TextField
            {...register('name')}
            label={'Nome'}
            fullWidth
            variant={'outlined'}
        />
        <TextField
            select
            value={watch('categories_id')}
            label={'Categorias'}
            margin={'normal'}
            variant={'outlined'}
            fullWidth
            onChange={(e) => {
              setValue('categories_id', e.target.value);
            }}
            SelectProps={{multiple: true}}
        >
          <MenuItem value={''} disabled>Selecione as categorias</MenuItem>
          {
            categories.map(category => (
                <MenuItem key={category.id}
                          value={category.id}>
                  {category.name}
                </MenuItem>
            ))
          }
        </TextField>
        <Checkbox
            defaultChecked
            {...register('is_active')}
            onChange={handleChangeActive}
        />
        Ativo?
        <Box dir={'rtl'}>
          <Button{...buttonProps} onClick={() => onSubmit(getValues())}>
            Salvar
          </Button>
          <Button{...buttonProps} type={'submit'}>Salvar e continuar</Button>
        </Box>
      </form>
  );
};
export default Form;
