import { describe, it, expect } from 'vitest';
import { renderHook, act } from '@testing-library/react';
import { CartProvider, useCart } from '../../context/CartContext';
import { Product } from '../../types';
import { ReactNode } from 'react';

const mockProduct: Product = {
  id: 'test-product',
  name: 'Test Product',
  inStock: true,
  gallery: ['img1.png'],
  description: 'A test product',
  category: 'tech',
  attributes: [
    {
      id: 'color',
      name: 'Color',
      type: 'swatch',
      items: [{ id: 'blue', value: 'blue', displayValue: 'Blue' }]
    }
  ],
  prices: [
    { amount: 50.0, currency: { label: 'USD', symbol: '$' } }
  ],
  brand: 'Test Brand'
};

const wrapper = ({ children }: { children: ReactNode }) => (
  <CartProvider>{children}</CartProvider>
);

describe('CartContext', () => {
  it('adds item to cart and opens it', () => {
    const { result } = renderHook(() => useCart(), { wrapper });
    
    act(() => {
      result.current.addToCart(mockProduct, { Color: 'blue' });
    });

    expect(result.current.cartItems).toHaveLength(1);
    expect(result.current.cartItems[0].product.id).toBe('test-product');
    expect(result.current.isCartOpen).toBe(true);
    expect(result.current.totalItems).toBe(1);
    expect(result.current.totalPrice).toBe(50);
  });

  it('increases quantity for same item attributes', () => {
    const { result } = renderHook(() => useCart(), { wrapper });
    
    act(() => {
      result.current.addToCart(mockProduct, { Color: 'blue' });
      result.current.addToCart(mockProduct, { Color: 'blue' });
    });

    expect(result.current.cartItems).toHaveLength(1);
    expect(result.current.cartItems[0].quantity).toBe(2);
    expect(result.current.totalItems).toBe(2);
    expect(result.current.totalPrice).toBe(100);
  });

  it('treats same product with different attributes as different cart items', () => {
    const { result } = renderHook(() => useCart(), { wrapper });
    
    act(() => {
      result.current.addToCart(mockProduct, { Color: 'blue' });
      result.current.addToCart(mockProduct, { Color: 'red' });
    });

    expect(result.current.cartItems).toHaveLength(2);
    expect(result.current.totalItems).toBe(2);
  });

  it('can increase and decrease quantity manually', () => {
    const { result } = renderHook(() => useCart(), { wrapper });
    
    act(() => {
      result.current.addToCart(mockProduct, { Color: 'blue' });
    });

    const key = result.current.cartItems[0].key;

    act(() => {
      result.current.increaseQuantity(key);
    });
    expect(result.current.cartItems[0].quantity).toBe(2);

    act(() => {
      result.current.decreaseQuantity(key);
    });
    expect(result.current.cartItems[0].quantity).toBe(1);

    act(() => {
      result.current.decreaseQuantity(key);
    });
    expect(result.current.cartItems).toHaveLength(0);
  });

  it('can clear cart', () => {
    const { result } = renderHook(() => useCart(), { wrapper });
    
    act(() => {
      result.current.addToCart(mockProduct, { Color: 'blue' });
      result.current.clearCart();
    });

    expect(result.current.cartItems).toHaveLength(0);
  });
});
