/**
 * Converts a string to kebab-case.
 * Handles spaces, ampersands, dots, slashes and other special chars.
 * Used for data-testid attributes.
 */
export function toKebabCase(str) {
  return str
    .toLowerCase()
    .replace(/&/g, 'and')       // "Tom & Jerry" → "tom-and-jerry"
    .replace(/\s+/g, '-')       // spaces → hyphens
    .replace(/[^a-z0-9-]/g, '') // remove everything else
    .replace(/-+/g, '-')        // collapse multiple hyphens
    .replace(/^-|-$/g, '');     // trim leading/trailing hyphens
}

export function formatPrice(amount, symbol) {
  return `${symbol}${Number(amount).toFixed(2)}`;
}
