import { ReactElement, useState, useEffect } from 'react';
import type { Category, Product } from './types';
import { CartProvider, useCart } from './context/CartContext';
import Header from './components/Header/Header';
import ProductList from './components/ProductList/ProductList';
import ProductDetails from './components/ProductDetails/ProductDetails';
import CartOverlay from './components/CartOverlay/CartOverlay';
import WindowsXPError from './components/WindowsXPError/WindowsXPError';
import { graphqlRequest } from './graphql/client';
import { GET_CATEGORIES, GET_PRODUCTS, GET_PRODUCT } from './graphql/queries';
import './index.css';

function AppContent(): ReactElement {
  const [categories, setCategories] = useState<Category[]>([]);
  const [activeCategory, setActiveCategory] = useState<string>('all');
  const [products, setProducts] = useState<Product[]>([]);
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<boolean>(false);
  const [productDetailsError, setProductDetailsError] = useState<boolean>(false);
  const { setIsCartOpen } = useCart();

  useEffect(() => {
    graphqlRequest<{ categories: Category[] }>(GET_CATEGORIES)
      .then(data => {
        if (!data || !data.categories) {
          setLoading(false);
          setError(true);
          return;
        }
        setCategories(data.categories);
        // Keep activeCategory as 'all' (default state)
        setError(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
        setError(true);
      });
  }, []);

  useEffect(() => {
    setLoading(true);
    const cat = activeCategory === 'all' ? undefined : activeCategory;
    graphqlRequest<{ products: Product[] }>(GET_PRODUCTS, { category: cat })
        .then(data => {
            if (!data || !data.products || data.products.length === 0) {
                setLoading(false);
                setError(true);
                return;
            }
            setProducts(data.products);
            setError(false);
            setLoading(false);
        })
      .catch(err => {
        console.error(err);
        setLoading(false);
        setError(true);
      });
  }, [activeCategory]);

  const handleRetry = (): void => {
    setError(false);
    setLoading(true);
    setCategories([]);
    setProducts([]);

    graphqlRequest<{ categories: Category[] }>(GET_CATEGORIES)
      .then(categoriesData => {
        if (!categoriesData || !categoriesData.categories) {
          setLoading(false);
          setError(true);
          return;
        }

         const cats = categoriesData.categories;
         setCategories(cats);
         setActiveCategory('all');

         const cat = undefined;
        graphqlRequest<{ products: Product[] }>(GET_PRODUCTS, { category: cat })
          .then(productsData => {
            if (!productsData || !productsData.products || productsData.products.length === 0) {
              setLoading(false);
              setError(true);
              return;
            }
            setProducts(productsData.products);
            setError(false);
            setLoading(false);
          })
          .catch(err => {
            console.error(err);
            setLoading(false);
            setError(true);
          });
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
        setError(true);
      });
  };

  const handleProductClick = async (productId: string): Promise<void> => {
    try {
      const data = await graphqlRequest<{ product: Product | null }>(GET_PRODUCT, { id: productId });
      setSelectedProduct(data.product);
      setProductDetailsError(false);
      setIsCartOpen(false);
    } catch (e) {
      console.error(e);
      setProductDetailsError(true);
    }
  };

  const handleCategoryChange = (cat: string): void => {
    setActiveCategory(cat);
    setSelectedProduct(null);
    setProductDetailsError(false);
    setIsCartOpen(false);
  };

  if (error) {
    return (
      <WindowsXPError
        message="Failed to load products. Please check your connection and try again."
        onRetry={handleRetry}
      />
    );
  }

  return (
    <div className="app">
      <Header
        categories={categories}
        activeCategory={activeCategory}
        onCategoryChange={handleCategoryChange}
      />
      <CartOverlay />
      <main>
        {productDetailsError ? (
          <WindowsXPError
            message="Failed to load product details. Please check your connection and try again."
            onRetry={() => setProductDetailsError(false)}
          />
        ) : selectedProduct ? (
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
