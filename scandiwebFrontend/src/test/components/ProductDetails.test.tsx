import { ReactElement } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import ProductDetails from '../../components/ProductDetails/ProductDetails';
import CartOverlay from '../../components/CartOverlay/CartOverlay';
import { CartProvider, useCart } from '../../context/CartContext';
import type { Product } from '../../types';

vi.mock('../../graphql/client', () => ({
  graphqlRequest: vi.fn(),
}));

const detailedProduct: Product = {
  id: 'apollo-jacket',
  name: 'Apollo Jacket',
  inStock: true,
  gallery: ['jacket-front.jpg', 'jacket-back.jpg'],
  description: '<p>Lightweight <strong>cotton</strong> jacket.</p>',
  category: 'clothes',
  brand: 'Apollo',
  prices: [{ amount: 88.5, currency: { label: 'USD', symbol: '$' } }],
  attributes: [
    {
      id: 'size',
      name: 'Size',
      type: 'text',
      items: [
        { id: 'small', displayValue: 'S', value: 'S' },
        { id: 'medium', displayValue: 'M', value: 'M' },
      ],
    },
    {
      id: 'color',
      name: 'Color',
      type: 'swatch',
      items: [
        { id: 'green', displayValue: 'Green', value: '#44ff03' },
        { id: 'black', displayValue: 'Black', value: '#000000' },
      ],
    },
  ],
};

function CartStateProbe(): ReactElement {
  const { totalItems, isCartOpen } = useCart();

  return (
    <div data-testid="cart-state">
      {totalItems}:{isCartOpen ? 'open' : 'closed'}
    </div>
  );
}

function renderProductDetails(product: Product = detailedProduct): void {
  render(
    <CartProvider>
      <ProductDetails product={product} />
      <CartStateProbe />
      <CartOverlay />
    </CartProvider>
  );
}

describe('ProductDetails', () => {
  beforeEach(() => {
    window.scrollTo = vi.fn();
  });

  it('requires all attributes before adding to cart and opens the cart overlay after a valid add', async () => {
    const user = userEvent.setup();
    renderProductDetails();

    const addToCart = screen.getByTestId('add-to-cart');

    expect(screen.getByTestId('product-attribute-size')).toBeInTheDocument();
    expect(screen.getByTestId('product-attribute-color')).toBeInTheDocument();
    expect(addToCart).toBeDisabled();
    expect(screen.getByTestId('cart-state')).toHaveTextContent('0:closed');

    await user.click(screen.getByRole('button', { name: 'M' }));
    expect(addToCart).toBeDisabled();

    await user.click(screen.getByTitle('Green'));
    expect(addToCart).toBeEnabled();

    await user.click(addToCart);

    expect(screen.getByTestId('cart-state')).toHaveTextContent('1:open');
    expect(screen.getByText('My Bag')).toBeInTheDocument();
    expect(screen.getByTestId('cart-total')).toHaveTextContent('Total$88.50');
    expect(screen.getByTestId('cart-item-attribute-size-medium-selected')).toHaveTextContent('M');
    expect(screen.getByTestId('cart-item-attribute-color-green-selected')).toBeInTheDocument();
  });

  it('renders parsed description HTML and switches the active gallery image', async () => {
    const user = userEvent.setup();
    renderProductDetails();

    expect(screen.getByTestId('product-gallery')).toBeInTheDocument();
    expect(screen.getByTestId('product-description')).toHaveTextContent('Lightweight cotton jacket.');
    expect(screen.getByText('cotton').tagName.toLowerCase()).toBe('strong');

    const mainImage = screen.getByAltText('Apollo Jacket') as HTMLImageElement;
    expect(mainImage.src).toContain('jacket-front.jpg');

    await user.click(screen.getByText('›'));
    expect(mainImage.src).toContain('jacket-back.jpg');

    await user.click(screen.getByRole('button', { name: 'Apollo Jacket 1' }));
    expect(mainImage.src).toContain('jacket-front.jpg');
  });

  it('keeps out-of-stock products impossible to add even after all options are selected', async () => {
    const user = userEvent.setup();
    renderProductDetails({ ...detailedProduct, inStock: false });

    const addToCart = screen.getByTestId('add-to-cart');

    await user.click(screen.getByRole('button', { name: 'M' }));
    await user.click(screen.getByTitle('Green'));

    expect(addToCart).toBeDisabled();
    expect(addToCart).toHaveTextContent('OUT OF STOCK');
    expect(screen.getByTestId('cart-state')).toHaveTextContent('0:closed');
  });
});
