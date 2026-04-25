// ── Domain types ──────────────────────────────────────────────────────────────

export interface Currency {
  label: string;
  symbol: string;
}

export interface Price {
  amount: number;
  currency: Currency;
}

export interface AttributeItem {
  id: string;
  displayValue: string;
  value: string;
}

export type AttributeType = 'text' | 'swatch';

export interface AttributeSet {
  id: string;
  name: string;
  type: AttributeType;
  items: AttributeItem[];
}

export interface Product {
  id: string;
  name: string;
  inStock: boolean;
  gallery: string[];
  description?: string;
  category: string;
  brand: string;
  prices: Price[];
  attributes: AttributeSet[];
}

export interface Category {
  id?: number;
  name: string;
}

// ── Cart types ────────────────────────────────────────────────────────────────

/** Selected attribute values: { "Color": "#000000", "Size": "M" } */
export type SelectedAttributes = Record<string, string>;

export interface CartItem {
  key: string;
  product: Product;
  selectedAttributes: SelectedAttributes;
  quantity: number;
}

// ── GraphQL types ─────────────────────────────────────────────────────────────

export interface OrderItemInput {
  productId: string;
  quantity: number;
  selectedAttributes: string;
}

export interface GraphQLResponse<T> {
  data: T;
  errors?: Array<{ message: string }>;
}

export interface CategoriesData {
  categories: Category[];
}

export interface ProductsData {
  products: Product[];
}

export interface ProductData {
  product: Product | null;
}
