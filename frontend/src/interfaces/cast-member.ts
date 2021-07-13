import { Timestamps } from './interfaces';

export interface CastMember extends Timestamps {
  readonly id: string;
  name: string;
  type: number;
}
