import { createContext, useContext, useState, ReactNode, ReactElement } from 'react';
import type { Product, CartItem, Currency, SelectedAttributes } from '../types';

interface CartContextValue {
  cartItems: CartItem[];
  isCartOpen: boolean;
  setIsCartOpen: (open: boolean) => void;
  addToCart: (product: Product, selectedAttributes: SelectedAttributes) => void;
  removeFromCart: (key: string) => void;
  increaseQuantity: (key: string) => void;
  decreaseQuantity: (key: string) => void;
  clearCart: () => void;
  totalItems: number;
  totalPrice: number;
  currency: Currency;
}

const CartContext = createContext<CartContextValue | null>(null);

function generateCartKey(productId: string, selectedAttributes: SelectedAttributes): string {
  const attrStr = Object.entries(selectedAttributes)
    .sort(([a], [b]) => a.localeCompare(b))
    .map(([k, v]) => `${k}:${v}`)
    .join('|');
  return `${productId}__${attrStr}`;
}

interface CartProviderProps {
  children: ReactNode;
}

export function CartProvider({ children }: CartProviderProps): ReactElement {
  const [cartItems, setCartItems] = useState<CartItem[]>([]);
  const [isCartOpen, setIsCartOpen] = useState<boolean>(false);

  const addToCart = (product: Product, selectedAttributes: SelectedAttributes): void => {
    const key = generateCartKey(product.id, selectedAttributes);
    setCartItems(prev => {
      const existing = prev.find(item => item.key === key);
      if (existing) {
        return prev.map(item => item.key === key ? { ...item, quantity: item.quantity + 1 } : item);
      }
      return [...prev, { key, product, selectedAttributes, quantity: 1 }];
    });
    setIsCartOpen(true);
  };

  const removeFromCart = (key: string): void => {
    setCartItems(prev => prev.filter(item => item.key !== key));
  };

  const increaseQuantity = (key: string): void => {
    setCartItems(prev => prev.map(item => item.key === key ? { ...item, quantity: item.quantity + 1 } : item));
  };

  const decreaseQuantity = (key: string): void => {
    setCartItems(prev => {
      const item = prev.find(i => i.key === key);
      if (!item) return prev;
      if (item.quantity === 1) return prev.filter(i => i.key !== key);
      return prev.map(i => i.key === key ? { ...i, quantity: i.quantity - 1 } : i);
    });
  };

  const clearCart = (): void => setCartItems([]);

  const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
  const totalPrice = cartItems.reduce((sum, item) => sum + (item.product.prices[0]?.amount ?? 0) * item.quantity, 0);
  const currency: Currency = cartItems[0]?.product.prices[0]?.currency ?? { symbol: '$', label: 'USD' };

  const value: CartContextValue = {
    cartItems, isCartOpen, setIsCartOpen,
    addToCart, removeFromCart, increaseQuantity, decreaseQuantity, clearCart,
    totalItems, totalPrice, currency,
  };

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart(): CartContextValue {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error('useCart must be used within CartProvider');
  return ctx;
}
