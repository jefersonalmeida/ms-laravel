import {setLocale} from 'yup';

const ptBR: any = {
  mixed: {
    required: '${path} é requerido',
    notType: '${path} é inválido',
  },
  string: {
    max: '${path} precisa ter no máximo ${max} caracteres',
  },
  number: {
    min: '${path} precisa ser no mínimo ${min}',
  },
};

setLocale(ptBR);

export * from 'yup';
