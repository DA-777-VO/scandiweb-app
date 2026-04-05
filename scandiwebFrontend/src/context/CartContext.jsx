import { createContext, useContext, useState } from 'react';

const CartContext = createContext(null);

export function CartProvider({ children }) {
  const [cartItems, setCartItems] = useState([]);
  const [isCartOpen, setIsCartOpen] = useState(false);

  const generateCartKey = (productId, selectedAttributes) => {
    const attrStr = Object.entries(selectedAttributes)
      .sort(([a], [b]) => a.localeCompare(b))
      .map(([k, v]) => `${k}:${v}`)
      .join('|');
    return `${productId}__${attrStr}`;
  };

  const addToCart = (product, selectedAttributes) => {
    const key = generateCartKey(product.id, selectedAttributes);
    setCartItems(prev => {
      const existing = prev.find(item => item.key === key);
      if (existing) {
        return prev.map(item =>
          item.key === key ? { ...item, quantity: item.quantity + 1 } : item
        );
      }
      return [...prev, { key, product, selectedAttributes, quantity: 1 }];
    });
    setIsCartOpen(true);
  };

  const removeFromCart = (key) => {
    setCartItems(prev => prev.filter(item => item.key !== key));
  };

  const increaseQuantity = (key) => {
    setCartItems(prev =>
      prev.map(item => item.key === key ? { ...item, quantity: item.quantity + 1 } : item)
    );
  };

  const decreaseQuantity = (key) => {
    setCartItems(prev => {
      const item = prev.find(i => i.key === key);
      if (!item) return prev;
      if (item.quantity === 1) return prev.filter(i => i.key !== key);
      return prev.map(i => i.key === key ? { ...i, quantity: i.quantity - 1 } : i);
    });
  };

  const clearCart = () => setCartItems([]);

  const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);

  const totalPrice = cartItems.reduce((sum, item) => {
    const price = item.product.prices[0]?.amount ?? 0;
    return sum + price * item.quantity;
  }, 0);

  const currency = cartItems[0]?.product.prices[0]?.currency ?? { symbol: '$', label: 'USD' };

  return (
    <CartContext.Provider value={{
      cartItems,
      isCartOpen,
      setIsCartOpen,
      addToCart,
      removeFromCart,
      increaseQuantity,
      decreaseQuantity,
      clearCart,
      totalItems,
      totalPrice,
      currency,
    }}>
      {children}
    </CartContext.Provider>
  );
}

export const useCart = () => {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error('useCart must be used within CartProvider');
  return ctx;
};
