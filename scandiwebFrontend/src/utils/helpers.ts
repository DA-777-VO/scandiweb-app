export function toKebabCase(str: string): string {
  return str
    .toLowerCase()
    .replace(/&/g, 'and')
    .replace(/\s+/g, '-')
    .replace(/[^a-z0-9-]/g, '')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
}

export function formatPrice(amount: number, symbol: string): string {
  return `${symbol}${Number(amount).toFixed(2)}`;
}
