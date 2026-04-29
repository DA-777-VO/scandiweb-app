import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Header from '../../components/Header/Header';
import { Category } from '../../types';

vi.mock('../../context/CartContext', () => ({
  useCart: () => ({
    isCartOpen: false,
    setIsCartOpen: vi.fn(),
    totalItems: 3
  })
}));

const mockCategories: Category[] = [
  { name: 'all' },
  { name: 'clothes' },
  { name: 'tech' }
];

describe('Header', () => {
  it('renders all categories', () => {
    render(<Header categories={mockCategories} activeCategory="clothes" onCategoryChange={() => {}} />);
    
    expect(screen.getByText('All')).toBeInTheDocument();
    expect(screen.getByText('Clothes')).toBeInTheDocument();
    expect(screen.getByText('Tech')).toBeInTheDocument();
  });

  it('sets the correct testids based on active category', () => {
    render(<Header categories={mockCategories} activeCategory="clothes" onCategoryChange={() => {}} />);
    
    const activeLink = screen.getByTestId('active-category-link');
    expect(activeLink).toHaveTextContent('Clothes');
    
    const inactiveLinks = screen.getAllByTestId('category-link');
    expect(inactiveLinks).toHaveLength(2);
    expect(inactiveLinks[0]).toHaveTextContent('All');
    expect(inactiveLinks[1]).toHaveTextContent('Tech');
  });

  it('calls onCategoryChange when a category is clicked', () => {
    const handleChange = vi.fn();
    render(<Header categories={mockCategories} activeCategory="all" onCategoryChange={handleChange} />);
    
    fireEvent.click(screen.getByText('Tech'));
    expect(handleChange).toHaveBeenCalledWith('tech');
  });

  it('shows total items badge', () => {
    render(<Header categories={mockCategories} activeCategory="all" onCategoryChange={() => {}} />);
    expect(screen.getByText('3')).toBeInTheDocument();
  });
});
