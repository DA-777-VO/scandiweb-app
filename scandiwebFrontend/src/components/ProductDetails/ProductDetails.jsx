import { useState } from 'react';
import { useCart } from '../../context/CartContext';
import { toKebabCase, formatPrice } from '../../utils/helpers';
import { HtmlContent } from '../../utils/htmlParser';
import styles from './ProductDetails.module.css';

export default function ProductDetails({ product }) {
  const { addToCart } = useCart();
  const [selectedImage, setSelectedImage] = useState(0);
  const [selectedAttributes, setSelectedAttributes] = useState({});

  const allAttributesSelected = product.attributes.every(
    attr => selectedAttributes[attr.name] !== undefined
  );

  const canAddToCart = product.inStock && allAttributesSelected;

  const handleAddToCart = () => {
    if (!canAddToCart) return;
    addToCart(product, selectedAttributes);
  };

  const handlePrev = () => {
    setSelectedImage(prev => (prev === 0 ? product.gallery.length - 1 : prev - 1));
  };

  const handleNext = () => {
    setSelectedImage(prev => (prev === product.gallery.length - 1 ? 0 : prev + 1));
  };

  const price = product.prices[0];

  return (
    <div className={styles.wrapper}>
      <div className={styles.gallery} data-testid="product-gallery">
        <div className={styles.thumbnails}>
          {product.gallery.map((img, i) => (
            <button
              key={i}
              className={`${styles.thumb} ${i === selectedImage ? styles.thumbActive : ''}`}
              onClick={() => setSelectedImage(i)}
            >
              <img src={img} alt={`${product.name} ${i + 1}`} />
            </button>
          ))}
        </div>
        <div className={styles.mainImageWrapper}>
          <img
            src={product.gallery[selectedImage]}
            alt={product.name}
            className={styles.mainImage}
          />
          {product.gallery.length > 1 && (
            <>
              <button className={`${styles.arrow} ${styles.arrowLeft}`} onClick={handlePrev}>&#8249;</button>
              <button className={`${styles.arrow} ${styles.arrowRight}`} onClick={handleNext}>&#8250;</button>
            </>
          )}
        </div>
      </div>

      <div className={styles.details}>
        <h1 className={styles.brand}>{product.brand}</h1>
        <h2 className={styles.name}>{product.name}</h2>

        {product.attributes.map(attr => (
          <div
            key={attr.id}
            className={styles.attributeGroup}
            data-testid={`product-attribute-${toKebabCase(attr.name)}`}
          >
            <p className={styles.attributeLabel}>{attr.name.toUpperCase()}:</p>
            <div className={styles.attributeOptions}>
              {attr.items.map(item => (
                attr.type === 'swatch' ? (
                  <button
                    key={item.id}
                    className={`${styles.swatchOption} ${selectedAttributes[attr.name] === item.value ? styles.swatchSelected : ''}`}
                    style={{ background: item.value }}
                    onClick={() => setSelectedAttributes(prev => ({ ...prev, [attr.name]: item.value }))}
                    title={item.displayValue}
                  />
                ) : (
                  <button
                    key={item.id}
                    className={`${styles.textOption} ${selectedAttributes[attr.name] === item.value ? styles.textSelected : ''}`}
                    onClick={() => setSelectedAttributes(prev => ({ ...prev, [attr.name]: item.value }))}
                  >
                    {item.displayValue}
                  </button>
                )
              ))}
            </div>
          </div>
        ))}

        {price && (
          <div className={styles.priceSection}>
            <p className={styles.priceLabel}>PRICE:</p>
            <p className={styles.price}>{formatPrice(price.amount, price.currency.symbol)}</p>
          </div>
        )}

        <button
          className={`${styles.addToCart} ${!canAddToCart ? styles.disabled : ''}`}
          onClick={handleAddToCart}
          disabled={!canAddToCart}
          data-testid="add-to-cart"
        >
          {product.inStock ? 'ADD TO CART' : 'OUT OF STOCK'}
        </button>

        <HtmlContent
          html={product.description}
          className={styles.description}
          testId="product-description"
        />
      </div>
    </div>
  );
}
