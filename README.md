# Scandiweb Full Stack Test

E-commerce SPA built with React + Vite (frontend) and PHP 8.1 + GraphQL (backend).

## Tech Stack

- **Frontend:** React 18, Vite, CSS Modules, plain fetch for GraphQL
- **Backend:** PHP 8.1, webonyx/graphql-php, PDO/MySQL, PSR-4 autoloading
- **Database:** MySQL 5.6+

## Project Structure

```
scandiweb/
├── backend/
│   ├── public/          # Web root (index.php + .htaccess)
│   ├── src/
│   │   ├── Database/    # PDO Connection singleton
│   │   ├── Models/
│   │   │   ├── Attribute/  # AbstractAttribute, TextAttribute, SwatchAttribute, Factory
│   │   │   ├── Category/   # AbstractCategory, GeneralCategory
│   │   │   └── Product/    # AbstractProduct, ClothesProduct, TechProduct, Factory
│   │   └── GraphQL/
│   │       ├── Types/      # CategoryType, ProductType, AttributeType
│   │       ├── Resolvers/  # CategoryResolver, ProductResolver
│   │       ├── Mutations/  # OrderMutation
│   │       └── SchemaBuilder.php
│   ├── database/
│   │   └── migrate.sql  # Schema + seed data
│   ├── composer.json
│   └── .env.example
└── frontend/
    ├── src/
    │   ├── components/
    │   │   ├── Header/
    │   │   ├── ProductList/
    │   │   ├── ProductCard/
    │   │   ├── ProductDetails/
    │   │   └── CartOverlay/
    │   ├── context/
    │   │   └── CartContext.jsx
    │   ├── graphql/
    │   │   ├── client.js
    │   │   └── queries.js
    │   ├── utils/
    │   │   ├── helpers.js
    │   │   └── htmlParser.jsx   # Parses HTML without dangerouslySetInnerHTML
    │   ├── App.jsx
    │   └── main.jsx
    ├── package.json
    └── .env.example
```

## Setup

### 1. Database

```bash
mysql -u root -p < backend/database/migrate.sql
```

### 2. Backend

```bash
cd backend
cp .env.example .env
# Edit .env with your DB credentials

composer install

# Start PHP dev server
php -S localhost:8000 -t public
```

### 3. Frontend

```bash
cd frontend
cp .env.example .env
# VITE_API_URL=http://localhost:8000/graphql

npm install
npm run dev
# Open http://localhost:3000
```

## OOP Design

### Polymorphism in Models

**Products** — `AbstractProduct` → `ClothesProduct` / `TechProduct`  
Products with different categories use separate subclasses. `ProductFactory` creates the right type.

**Attributes** — `AbstractAttribute` → `TextAttribute` / `SwatchAttribute`  
Each type handles `formatItems()` differently without any switch/if statements. `AttributeFactory` resolves the correct class from the `type` field.

**Categories** — `AbstractCategory` → `GeneralCategory`  
Extensible for future category types (e.g., `FeaturedCategory`).

### GraphQL Schema

```graphql
type Query {
  categories: [Category]
  category(name: String!): Category
  products(category: String): [Product]
  product(id: String!): Product
}

type Mutation {
  placeOrder(items: [OrderItemInput!]!): Boolean
}
```

## Features Implemented

- ✅ Category navigation with active state
- ✅ Product listing with quick-shop button on hover
- ✅ Out-of-stock products (greyed out, no quick shop)
- ✅ Product Details Page with image gallery + arrows
- ✅ Text & swatch attribute selectors
- ✅ Add to cart (requires all attributes selected)
- ✅ Cart overlay with item count badge
- ✅ Increase/decrease/remove cart items
- ✅ Cart total calculation
- ✅ Place Order GraphQL mutation (empties cart)
- ✅ Page greyed out when cart open
- ✅ HTML description parser (no `dangerouslySetInnerHTML`)
- ✅ All required `data-testid` attributes

## data-testid Reference

| Element | Attribute |
|---|---|
| Category link | `data-testid="category-link"` |
| Active category link | `data-testid="active-category-link"` |
| Cart button | `data-testid="cart-btn"` |
| Product card | `data-testid="product-{name-kebab-case}"` |
| Product gallery | `data-testid="product-gallery"` |
| Product attribute | `data-testid="product-attribute-{attr-kebab}"` |
| Add to cart button | `data-testid="add-to-cart"` |
| Product description | `data-testid="product-description"` |
| Cart item attribute container | `data-testid="cart-item-attribute-{attr-kebab}"` |
| Cart item attribute option | `data-testid="cart-item-attribute-{attr}-{item}"` |
| Selected option | `data-testid="cart-item-attribute-{attr}-{item}-selected"` |
| Quantity increase | `data-testid="cart-item-amount-increase"` |
| Quantity decrease | `data-testid="cart-item-amount-decrease"` |
| Quantity indicator | `data-testid="cart-item-amount"` |
| Cart total | `data-testid="cart-total"` |
# scandiweb-app
