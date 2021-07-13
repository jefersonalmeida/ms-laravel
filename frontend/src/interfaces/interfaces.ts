export interface DataObject {
  label?: string;
  value: string | any | number;
  color?: 'default' | 'primary' | 'secondary';
}

export interface Timestamps {
  readonly created_at: Date;
  readonly updated_at: Date;
  readonly deleted_at: Date | null;
}

export interface ResponseEntity<T> {
  data: T;
}

export interface ResponseList<T> {
  data: T[];
  links: Links;
  meta: Meta;
}

export interface Links {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
}

export interface Link {
  url: string;
  label: string;
  active: boolean;
}

export interface Meta {
  current_page: number;
  from: number;
  last_page: number;
  links: Link[];
  path: string;
  per_page: number;
  to: number;
  total: number;
}
