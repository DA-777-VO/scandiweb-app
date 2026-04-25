import { ReactElement, MouseEvent } from 'react';
import type { Product, SelectedAttributes } from '../../types';
import { useCart } from '../../context/CartContext';
import { toKebabCase, formatPrice } from '../../utils/helpers';
import styles from './ProductCard.module.css';

interface ProductCardProps {
  product: Product;
  onClick: () => void;
}

export default function ProductCard({ product, onClick }: ProductCardProps): ReactElement {
  const { addToCart } = useCart();

  const handleQuickShop = (e: MouseEvent<HTMLButtonElement>): void => {
    e.stopPropagation();
    if (!product.inStock) return;
    const defaultAttributes: SelectedAttributes = {};
    product.attributes.forEach(attr => {
      if (attr.items.length > 0) {
        defaultAttributes[attr.name] = attr.items[0].value;
      }
    });
    addToCart(product, defaultAttributes);
  };

  const price = product.prices[0];

  return (
    <div
      className={`${styles.card} ${!product.inStock ? styles.outOfStock : ''}`}
      onClick={onClick}
      data-testid={`product-${toKebabCase(product.name)}`}
    >
      <div className={styles.imageWrapper}>
        <img src={product.gallery[0]} alt={product.name} className={styles.image} />
        {!product.inStock && (
          <div className={styles.outOfStockOverlay}>
            <span>OUT OF STOCK</span>
          </div>
        )}
        {product.inStock && (
          <button className={styles.quickShop} onClick={handleQuickShop} aria-label="Add to cart">
            <svg width="24" height="24" viewBox="0 0 20 20" fill="none">
              <path d="M19.5613 4.87359C19.1822 4.41031 18.5924 4.12873 17.9821 4.12873H5.15889L4.75914 2.63901C4.52718 1.77302 3.72769 1.16895 2.80069 1.16895H0.653099C0.295301 1.16895 0 1.45052 0 1.79347C0 2.13562 0.294459 2.418 0.653099 2.418H2.80069C3.11654 2.418 3.39045 2.61936 3.47434 2.92139L6.04306 12.7077C6.27502 13.5737 7.07451 14.1778 8.00152 14.1778H16.4028C17.3289 14.1778 18.1507 13.5737 18.3612 12.7077L19.9405 6.50575C20.0877 5.941 19.9619 5.33693 19.5613 4.87365Z" fill="white"/>
            </svg>
          </button>
        )}
      </div>
      <div className={styles.info}>
        <p className={styles.name}>{product.name}</p>
        {price && <p className={styles.price}>{formatPrice(price.amount, price.currency.symbol)}</p>}
      </div>
    </div>
  );
}
