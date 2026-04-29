import { describe, it, expect } from 'vitest';
import { toKebabCase, formatPrice } from '../../utils/helpers';

describe('Helpers', () => {
  describe('toKebabCase', () => {
    it('converts normal string to kebab-case', () => {
      expect(toKebabCase('Hello World')).toBe('hello-world');
    });

    it('replaces ampersands with and', () => {
      expect(toKebabCase('AC & DC')).toBe('ac-and-dc');
    });

    it('removes special characters and extra spaces', () => {
      expect(toKebabCase('  spaces  @#$  ')).toBe('spaces');
    });
  });

  describe('formatPrice', () => {
    it('formats integer price with symbol', () => {
      expect(formatPrice(100, '$')).toBe('$100.00');
    });

    it('formats float price with symbol', () => {
      expect(formatPrice(9.99, '€')).toBe('€9.99');
    });
  });
});
