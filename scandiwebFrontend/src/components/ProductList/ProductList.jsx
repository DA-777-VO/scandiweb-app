import ProductCard from '../ProductCard/ProductCard';
import styles from './ProductList.module.css';

export default function ProductList({ products, category, onProductClick }) {
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
