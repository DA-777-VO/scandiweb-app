export function toKebabCase(str) {
  return str
    .toLowerCase()
    .replace(/\s+/g, '-')
    .replace(/[^a-z0-9-]/g, '');
}

export function formatPrice(amount, symbol) {
  return `${symbol}${amount.toFixed(2)}`;
}
