import { ReactElement, useState, useEffect } from 'react';
import type { Category, Product } from './types';
import { CartProvider, useCart } from './context/CartContext';
import Header from './components/Header/Header';
import ProductList from './components/ProductList/ProductList';
import ProductDetails from './components/ProductDetails/ProductDetails';
import CartOverlay from './components/CartOverlay/CartOverlay';
import { graphqlRequest } from './graphql/client';
import { GET_CATEGORIES, GET_PRODUCTS, GET_PRODUCT } from './graphql/queries';
import './index.css';

function AppContent(): ReactElement {
  const [categories, setCategories] = useState<Category[]>([]);
  const [activeCategory, setActiveCategory] = useState<string>('all');
  const [products, setProducts] = useState<Product[]>([]);
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const { setIsCartOpen } = useCart();

  useEffect(() => {
    graphqlRequest<{ categories: Category[] }>(GET_CATEGORIES)
      .then(data => {
        setCategories(data.categories);
        if (data.categories.length > 0) {
          setActiveCategory(data.categories[0].name);
        }
      })
      .catch(console.error);
  }, []);

  useEffect(() => {
    setLoading(true);
    const cat = activeCategory === 'all' ? undefined : activeCategory;
    graphqlRequest<{ products: Product[] }>(GET_PRODUCTS, { category: cat })
      .then(data => {
        setProducts(data.products);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, [activeCategory]);

  const handleProductClick = async (productId: string): Promise<void> => {
    try {
      const data = await graphqlRequest<{ product: Product | null }>(GET_PRODUCT, { id: productId });
      setSelectedProduct(data.product);
      setIsCartOpen(false);
    } catch (e) {
      console.error(e);
    }
  };

  const handleCategoryChange = (cat: string): void => {
    setActiveCategory(cat);
    setSelectedProduct(null);
    setIsCartOpen(false);
  };

  return (
    <div className="app">
      <Header
        categories={categories}
        activeCategory={activeCategory}
        onCategoryChange={handleCategoryChange}
      />
      <CartOverlay />
      <main>
        {selectedProduct ? (
          <ProductDetails product={selectedProduct} />
        ) : loading ? (
          <div className="loading">Loading...</div>
        ) : (
          <ProductList
            products={products}
            category={activeCategory}
            onProductClick={id => void handleProductClick(id)}
          />
        )}
      </main>
    </div>
  );
}

export default function App(): ReactElement {
  return (
    <CartProvider>
      <AppContent />
    </CartProvider>
  );
}
