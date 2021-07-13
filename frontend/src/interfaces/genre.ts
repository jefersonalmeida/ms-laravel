import { Category } from './category';
import { Timestamps } from './interfaces';

export interface Genre extends Timestamps {
  readonly id: string;
  is_active: boolean;
  name: string;
  categories: Category[];
  categories_id: string | any[] | undefined;
}
