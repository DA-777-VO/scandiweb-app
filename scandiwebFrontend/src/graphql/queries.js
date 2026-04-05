export const GET_CATEGORIES = `
  query GetCategories {
    categories {
      id
      name
    }
  }
`;

export const GET_PRODUCTS = `
  query GetProducts($category: String) {
    products(category: $category) {
      id
      name
      inStock
      gallery
      category
      brand
      prices {
        amount
        currency {
          label
          symbol
        }
      }
      attributes {
        id
        name
        type
        items {
          id
          displayValue
          value
        }
      }
    }
  }
`;

export const GET_PRODUCT = `
  query GetProduct($id: String!) {
    product(id: $id) {
      id
      name
      inStock
      gallery
      description
      category
      brand
      prices {
        amount
        currency {
          label
          symbol
        }
      }
      attributes {
        id
        name
        type
        items {
          id
          displayValue
          value
        }
      }
    }
  }
`;

export const PLACE_ORDER = `
  mutation PlaceOrder($items: [OrderItemInput!]!) {
    placeOrder(items: $items)
  }
`;
