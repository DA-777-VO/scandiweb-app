import { useState, useEffect } from 'react';
import { CartProvider, useCart } from './context/CartContext';
import Header from './components/Header/Header';
import ProductList from './components/ProductList/ProductList';
import ProductDetails from './components/ProductDetails/ProductDetails';
import CartOverlay from './components/CartOverlay/CartOverlay';
import { graphqlRequest } from './graphql/client';
import { GET_CATEGORIES, GET_PRODUCTS, GET_PRODUCT } from './graphql/queries';
import './index.css';

function AppContent() {
  const [categories, setCategories] = useState([]);
  const [activeCategory, setActiveCategory] = useState('all');
  const [products, setProducts] = useState([]);
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const { isCartOpen, setIsCartOpen } = useCart();

  useEffect(() => {
    graphqlRequest(GET_CATEGORIES)
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
    graphqlRequest(GET_PRODUCTS, { category: cat })
      .then(data => {
        setProducts(data.products);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, [activeCategory]);

  const handleProductClick = async (productId) => {
    try {
      const data = await graphqlRequest(GET_PRODUCT, { id: productId });
      setSelectedProduct(data.product);
      setIsCartOpen(false);
    } catch (e) {
      console.error(e);
    }
  };

  const handleCategoryChange = (cat) => {
    setActiveCategory(cat);
    setSelectedProduct(null);
    setIsCartOpen(false);
  };

  return (
    <div className="app">
      {/* Header is always on top, not greyed out when cart opens */}
      <Header
        categories={categories}
        activeCategory={activeCategory}
        onCategoryChange={handleCategoryChange}
      />

      {/* CartOverlay renders backdrop + overlay panel */}
      <CartOverlay />

      {/* Main content — greyed out via backdrop when cart is open */}
      <main>
        {selectedProduct ? (
          <ProductDetails product={selectedProduct} />
        ) : loading ? (
          <div className="loading">Loading...</div>
        ) : (
          <ProductList
            products={products}
            category={activeCategory}
            onProductClick={handleProductClick}
          />
        )}
      </main>
    </div>
  );
}

export default function App() {
  return (
    <CartProvider>
      <AppContent />
    </CartProvider>
  );
}
