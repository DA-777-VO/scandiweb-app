import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import ProductCard from '../../components/ProductCard/ProductCard';
import { Product } from '../../types';

const mockAddToCart = vi.fn();
vi.mock('../../context/CartContext', () => ({
  useCart: () => ({ addToCart: mockAddToCart })
}));

const mockProduct: Product = {
  id: 'test-product',
  name: 'Test Product',
  inStock: true,
  gallery: ['img.png'],
  description: 'Test',
  category: 'tech',
  attributes: [],
  prices: [{ amount: 50.0, currency: { label: 'USD', symbol: '$' } }],
  brand: 'Brand'
};

describe('ProductCard', () => {
  it('renders product details correctly', () => {
    render(<ProductCard product={mockProduct} onClick={() => {}} />);
    
    expect(screen.getByText('Test Product')).toBeInTheDocument();
    expect(screen.getByText('$50.00')).toBeInTheDocument();
  });

  it('handles click correctly', () => {
    const handleClick = vi.fn();
    render(<ProductCard product={mockProduct} onClick={handleClick} />);
    
    fireEvent.click(screen.getByTestId('product-test-product'));
    expect(handleClick).toHaveBeenCalled();
  });

  it('adds to cart when quick shop button is clicked', () => {
    render(<ProductCard product={mockProduct} onClick={() => {}} />);
    
    const cartBtn = screen.getByLabelText('Add to cart');
    fireEvent.click(cartBtn);
    
    expect(mockAddToCart).toHaveBeenCalledWith(mockProduct, {});
  });

  it('shows OUT OF STOCK when not in stock', () => {
    const outOfStockProduct = { ...mockProduct, inStock: false };
    render(<ProductCard product={outOfStockProduct} onClick={() => {}} />);
    
    expect(screen.getByText('OUT OF STOCK')).toBeInTheDocument();
    expect(screen.queryByLabelText('Add to cart')).not.toBeInTheDocument();
  });
});
