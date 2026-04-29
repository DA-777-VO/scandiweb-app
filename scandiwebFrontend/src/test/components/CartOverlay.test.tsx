import { ReactElement } from 'react';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import CartOverlay from '../../components/CartOverlay/CartOverlay';
import { CartProvider, useCart } from '../../context/CartContext';
import { PLACE_ORDER } from '../../graphql/queries';
import type { Product, SelectedAttributes } from '../../types';

const mocks = vi.hoisted(() => ({
  graphqlRequest: vi.fn(),
}));

vi.mock('../../graphql/client', () => ({
  graphqlRequest: mocks.graphqlRequest,
}));

const selectedAttributes: SelectedAttributes = {
  Size: 'M',
  Color: '#44ff03',
};

const cartProduct: Product = {
  id: 'apollo-shirt',
  name: 'Apollo Shirt',
  inStock: true,
  gallery: ['apollo-main.jpg'],
  description: 'Apollo product',
  category: 'clothes',
  brand: 'Apollo',
  prices: [{ amount: 120, currency: { label: 'USD', symbol: '$' } }],
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

function CartOverlayHarness(): ReactElement {
  const { addToCart, setIsCartOpen, totalItems, isCartOpen } = useCart();

  return (
    <>
      <button onClick={() => setIsCartOpen(true)}>Open cart</button>
      <button onClick={() => addToCart(cartProduct, selectedAttributes)}>Add configured product</button>
      <div data-testid="cart-state">
        {totalItems}:{isCartOpen ? 'open' : 'closed'}
      </div>
      <CartOverlay />
    </>
  );
}

function renderCartOverlay(): void {
  render(
    <CartProvider>
      <CartOverlayHarness />
    </CartProvider>
  );
}

describe('CartOverlay', () => {
  beforeEach(() => {
    mocks.graphqlRequest.mockReset();
  });

  it('keeps checkout disabled but still shows a zero total for an empty open cart', async () => {
    const user = userEvent.setup();
    renderCartOverlay();

    await user.click(screen.getByText('Open cart'));

    expect(screen.getByTestId('cart-total')).toHaveTextContent('Total$0.00');
    expect(screen.getByRole('button', { name: 'PLACE ORDER' })).toBeDisabled();
    expect(screen.getByTestId('cart-state')).toHaveTextContent('0:open');
    expect(mocks.graphqlRequest).not.toHaveBeenCalled();
  });

  it('renders selected and available attributes, then updates quantities and totals', async () => {
    const user = userEvent.setup();
    renderCartOverlay();

    await user.click(screen.getByText('Add configured product'));

    expect(screen.getByText('Apollo Shirt')).toBeInTheDocument();
    expect(screen.getByTestId('cart-item-attribute-size')).toBeInTheDocument();
    expect(screen.getByTestId('cart-item-attribute-size-medium-selected')).toHaveTextContent('M');
    expect(screen.getByTestId('cart-item-attribute-size-small')).toHaveTextContent('S');
    expect(screen.getByTestId('cart-item-attribute-color-green-selected')).toBeInTheDocument();
    expect(screen.getByTestId('cart-item-attribute-color-black')).toBeInTheDocument();
    expect(screen.getByTestId('cart-item-amount')).toHaveTextContent('1');
    expect(screen.getByTestId('cart-total')).toHaveTextContent('Total$120.00');

    await user.click(screen.getByTestId('cart-item-amount-increase'));
    expect(screen.getByTestId('cart-item-amount')).toHaveTextContent('2');
    expect(screen.getByTestId('cart-total')).toHaveTextContent('Total$240.00');

    await user.click(screen.getByTestId('cart-item-amount-decrease'));
    await user.click(screen.getByTestId('cart-item-amount-decrease'));

    expect(screen.queryByText('Apollo Shirt')).not.toBeInTheDocument();
    expect(screen.getByTestId('cart-total')).toHaveTextContent('Total$0.00');
    expect(screen.getByRole('button', { name: 'PLACE ORDER' })).toBeDisabled();
  });

  it('places an order through GraphQL, serializes selected attributes, clears cart, and closes overlay', async () => {
    const user = userEvent.setup();
    mocks.graphqlRequest.mockResolvedValue({ placeOrder: true });
    renderCartOverlay();

    await user.click(screen.getByText('Add configured product'));
    await user.click(screen.getByRole('button', { name: 'PLACE ORDER' }));

    await waitFor(() => {
      expect(mocks.graphqlRequest).toHaveBeenCalledTimes(1);
    });

    const [, variables] = mocks.graphqlRequest.mock.calls[0] as [
      string,
      { items: Array<{ productId: string; quantity: number; selectedAttributes: string }> },
    ];

    expect(mocks.graphqlRequest).toHaveBeenCalledWith(PLACE_ORDER, expect.any(Object));
    expect(variables.items).toHaveLength(1);
    expect(variables.items[0].productId).toBe('apollo-shirt');
    expect(variables.items[0].quantity).toBe(1);
    expect(JSON.parse(variables.items[0].selectedAttributes) as SelectedAttributes).toEqual(selectedAttributes);
    expect(screen.getByTestId('cart-state')).toHaveTextContent('0:closed');
    expect(screen.queryByRole('button', { name: 'PLACE ORDER' })).not.toBeInTheDocument();
  });
});
