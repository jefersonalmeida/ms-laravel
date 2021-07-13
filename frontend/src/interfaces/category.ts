import { Timestamps } from './interfaces';

export interface Category extends Timestamps {
  readonly id: string;
  is_active: boolean;
  name: string;
  description?: any;
  pivot?: PivotGenreCategory;
}

export interface PivotGenreCategory {
  genre_id: string;
  category_id: string;
}
