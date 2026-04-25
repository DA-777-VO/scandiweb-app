import { ReactElement } from 'react';
import type { Product } from '../../types';
import ProductCard from '../ProductCard/ProductCard';
import styles from './ProductList.module.css';

interface ProductListProps {
  products: Product[];
  category: string;
  onProductClick: (productId: string) => void;
}

export default function ProductList({ products, category, onProductClick }: ProductListProps): ReactElement {
  return (
    <div className={styles.wrapper}>
      <h1 className={styles.title}>
        {category.charAt(0).toUpperCase() + category.slice(1)}
      </h1>
      <div className={styles.grid}>
        {products.map(product => (
          <ProductCard
            key={product.id}
            product={product}
            onClick={() => onProductClick(product.id)}
          />
        ))}
      </div>
    </div>
  );
}
