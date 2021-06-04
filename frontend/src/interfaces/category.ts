export interface Category {
  id: string;
  is_active: boolean;
  name: string;
  description?: any;
  created_at: Date;
  updated_at: Date;
  deleted_at?: any;
  pivot?: PivotGenreCategory;
}

export interface PivotGenreCategory {
  genre_id: string;
  category_id: string;
}
