import * as React from 'react';
import {useEffect} from 'react';
import {
  Box,
  Button,
  ButtonProps,
  FormControl,
  FormControlLabel,
  FormLabel,
  Radio,
  RadioGroup,
  TextField,
} from '@material-ui/core';
import {makeStyles, Theme} from '@material-ui/core/styles';
import {SubmitHandler, useForm} from 'react-hook-form';
import castMemberResource from '../../resource/cast-member.resource';

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

  const {register, handleSubmit, getValues, setValue} = useForm<any>();
  const onSubmit: SubmitHandler<any> = (data, event) => {
    castMemberResource.create(data)
        .then(response => console.log(response));
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setValue('type', parseInt(e.target.value));
  };

  useEffect(() => {
    register('type');
  }, [register]);

  return (
      <form onSubmit={handleSubmit(onSubmit)}>
        <TextField
            {...register('name')}
            label={'Nome'}
            fullWidth
            variant={'outlined'}
        />
        <FormControl margin={'normal'}>
          <FormLabel component={'legend'}>Tipo</FormLabel>
          <RadioGroup name={'type'} onChange={handleChange}>
            <FormControlLabel value={'1'} control={<Radio/>} label="Diretor"/>
            <FormControlLabel value={'2'} control={<Radio/>} label="Ator"/>
          </RadioGroup>
        </FormControl>
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
