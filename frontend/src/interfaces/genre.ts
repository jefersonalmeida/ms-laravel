import {Category} from './category';

export interface Genre {
  id: string;
  is_active: boolean;
  name: string;
  created_at: Date;
  updated_at: Date;
  deleted_at?: any;
  categories: Category[];
}
