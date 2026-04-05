import { useCart } from '../../context/CartContext';
import { toKebabCase } from '../../utils/helpers';
import styles from './Header.module.css';

export default function Header({ categories, activeCategory, onCategoryChange }) {
  const { isCartOpen, setIsCartOpen, totalItems } = useCart();

  return (
    <header className={styles.header}>
      <nav className={styles.nav}>
        {categories.map(cat => (
          <button
            key={cat.name}
            className={`${styles.categoryLink} ${activeCategory === cat.name ? styles.active : ''}`}
            onClick={() => onCategoryChange(cat.name)}
            data-testid={activeCategory === cat.name ? 'active-category-link' : 'category-link'}
          >
            {cat.name.charAt(0).toUpperCase() + cat.name.slice(1)}
          </button>
        ))}
      </nav>

      <div className={styles.logo}>
        <svg width="41" height="41" viewBox="0 0 41 41" fill="none">
          <path d="M20.5 3.5C11.1 3.5 3.5 11.1 3.5 20.5S11.1 37.5 20.5 37.5 37.5 29.9 37.5 20.5 29.9 3.5 20.5 3.5z" fill="#5ECE7B"/>
          <path d="M26 16h-3v-2a2.5 2.5 0 00-5 0v2h-3l-1 13h13l-1-13zm-6-2a.5.5 0 011 0v2h-1v-2z" fill="white"/>
        </svg>
      </div>

      <div className={styles.actions}>
        <button
          className={styles.cartBtn}
          onClick={() => setIsCartOpen(!isCartOpen)}
          data-testid="cart-btn"
        >
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M1 1h3.2L6 12.6a2 2 0 002 1.4h7.4a2 2 0 002-1.4L19 5H4.4" stroke="#43464E" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
            <circle cx="8.5" cy="17.5" r="1" fill="#43464E"/>
            <circle cx="15.5" cy="17.5" r="1" fill="#43464E"/>
          </svg>
          {totalItems > 0 && (
            <span className={styles.badge}>{totalItems}</span>
          )}
        </button>
      </div>
    </header>
  );
}
