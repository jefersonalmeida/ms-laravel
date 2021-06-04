import {DataObject} from './interfaces';

export class Mapper {
  public static actives = (): DataObject[] => ([
    {value: true, label: 'Sim', color: 'primary'},
    {value: false, label: 'Não', color: 'secondary'},
  ]);

  public static members = (): DataObject[] => ([
    {value: 1, label: 'Diretor', color: 'primary'},
    {value: 2, label: 'Ator', color: 'secondary'},
  ]);
}
