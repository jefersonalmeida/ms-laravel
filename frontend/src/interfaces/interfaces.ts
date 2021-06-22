export interface DataObject {
  label?: string;
  value: string | any | number;
  color?: 'default' | 'primary' | 'secondary';
}

export interface ResponseEntity<T> {
  data: T;
}
