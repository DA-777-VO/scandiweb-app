import { useCart } from '../../context/CartContext';
import { toKebabCase, formatPrice } from '../../utils/helpers';
import styles from './ProductCard.module.css';

export default function ProductCard({ product, onClick }) {
  const { addToCart } = useCart();

  const handleQuickShop = (e) => {
    e.stopPropagation();
    const defaultAttributes = {};
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
        <img
          src={product.gallery[0]}
          alt={product.name}
          className={styles.image}
        />
        {!product.inStock && (
          <div className={styles.outOfStockOverlay}>
            <span>OUT OF STOCK</span>
          </div>
        )}
        {product.inStock && (
          <button
            className={styles.quickShop}
            onClick={handleQuickShop}
            aria-label="Add to cart"
          >
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M1 1h3.2L6 12.6a2 2 0 002 1.4h7.4a2 2 0 002-1.4L19 5H4.4" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
              <circle cx="8.5" cy="17.5" r="1" fill="white"/>
              <circle cx="15.5" cy="17.5" r="1" fill="white"/>
            </svg>
          </button>
        )}
      </div>
      <div className={styles.info}>
        <p className={styles.name}>{product.name}</p>
        {price && (
          <p className={styles.price}>
            {formatPrice(price.amount, price.currency.symbol)}
          </p>
        )}
      </div>
    </div>
  );
}
