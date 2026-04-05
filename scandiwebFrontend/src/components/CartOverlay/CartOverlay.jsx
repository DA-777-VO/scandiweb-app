import { useCart } from '../../context/CartContext';
import { toKebabCase, formatPrice } from '../../utils/helpers';
import { graphqlRequest } from '../../graphql/client';
import { PLACE_ORDER } from '../../graphql/queries';
import styles from './CartOverlay.module.css';

export default function CartOverlay() {
  const {
    cartItems,
    isCartOpen,
    setIsCartOpen,
    increaseQuantity,
    decreaseQuantity,
    clearCart,
    totalItems,
    totalPrice,
    currency,
  } = useCart();

  if (!isCartOpen) return null;

  const handlePlaceOrder = async () => {
    if (cartItems.length === 0) return;
    try {
      const items = cartItems.map(item => ({
        productId: item.product.id,
        quantity: item.quantity,
        selectedAttributes: JSON.stringify(item.selectedAttributes),
      }));
      await graphqlRequest(PLACE_ORDER, { items });
      clearCart();
      setIsCartOpen(false);
    } catch (e) {
      console.error('Order failed:', e);
    }
  };

  return (
    <>
      <div
        className={styles.backdrop}
        onClick={() => setIsCartOpen(false)}
      />
      <div className={styles.overlay}>
        <div className={styles.header}>
          <span className={styles.title}>
            <strong>My Bag</strong>
            {totalItems > 0 && `, ${totalItems} ${totalItems === 1 ? 'item' : 'items'}`}
          </span>
        </div>

        <div className={styles.items}>
          {cartItems.map(item => (
            <CartItem
              key={item.key}
              item={item}
              onIncrease={() => increaseQuantity(item.key)}
              onDecrease={() => decreaseQuantity(item.key)}
            />
          ))}
        </div>

        <div className={styles.footer}>
          <div className={styles.total} data-testid="cart-total">
            <span>Total</span>
            <span>{formatPrice(totalPrice, currency.symbol)}</span>
          </div>
          <button
            className={`${styles.placeOrder} ${cartItems.length === 0 ? styles.disabled : ''}`}
            onClick={handlePlaceOrder}
            disabled={cartItems.length === 0}
          >
            PLACE ORDER
          </button>
        </div>
      </div>
    </>
  );
}

function CartItem({ item, onIncrease, onDecrease }) {
  const { product, selectedAttributes, quantity } = item;
  const price = product.prices[0];

  return (
    <div className={styles.cartItem}>
      <div className={styles.itemDetails}>
        <p className={styles.itemName}>{product.name}</p>
        {price && (
          <p className={styles.itemPrice}>
            {formatPrice(price.amount, price.currency.symbol)}
          </p>
        )}

        {product.attributes.map(attr => (
          <div
            key={attr.id}
            className={styles.attrGroup}
            data-testid={`cart-item-attribute-${toKebabCase(attr.name)}`}
          >
            <p className={styles.attrLabel}>{attr.name}:</p>
            <div className={styles.attrOptions}>
              {attr.items.map(attrItem => {
                const isSelected = selectedAttributes[attr.name] === attrItem.value;
                const kebabAttr = toKebabCase(attr.name);
                const kebabItem = toKebabCase(attrItem.id);
                const testId = isSelected
                  ? `cart-item-attribute-${kebabAttr}-${kebabItem}-selected`
                  : `cart-item-attribute-${kebabAttr}-${kebabItem}`;

                return attr.type === 'swatch' ? (
                  <div
                    key={attrItem.id}
                    className={`${styles.swatchOpt} ${isSelected ? styles.swatchOptSelected : ''}`}
                    style={{ background: attrItem.value }}
                    data-testid={testId}
                  />
                ) : (
                  <div
                    key={attrItem.id}
                    className={`${styles.textOpt} ${isSelected ? styles.textOptSelected : ''}`}
                    data-testid={testId}
                  >
                    {attrItem.displayValue}
                  </div>
                );
              })}
            </div>
          </div>
        ))}
      </div>

      <div className={styles.quantityControls}>
        <button
          className={styles.qtyBtn}
          onClick={onIncrease}
          data-testid="cart-item-amount-increase"
        >+</button>
        <span data-testid="cart-item-amount">{quantity}</span>
        <button
          className={styles.qtyBtn}
          onClick={onDecrease}
          data-testid="cart-item-amount-decrease"
        >−</button>
      </div>

      <div className={styles.itemImage}>
        <img src={product.gallery[0]} alt={product.name} />
      </div>
    </div>
  );
}
