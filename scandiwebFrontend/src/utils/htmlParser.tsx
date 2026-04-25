import { createElement, ReactElement, ReactNode } from 'react';

type ParseResult = string | ParsedElement | null;

interface ParsedElement {
  type: string;
  props: Record<string, unknown> & { children?: ReactNode | ReactNode[] };
}

const ALLOWED_TAGS = [
  'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
  'ul', 'ol', 'li', 'span', 'div', 'strong', 'em', 'br', 'a',
] as const;

let keyCounter = 0;

function nodeToReact(node: Node): ParseResult {
  if (node.nodeType === Node.TEXT_NODE) return node.textContent ?? '';
  if (node.nodeType !== Node.ELEMENT_NODE) return null;

  const el = node as Element;
  const tag = el.tagName.toLowerCase();
  const children = Array.from(el.childNodes)
    .map(nodeToReact)
    .filter((c): c is string | ParsedElement => c !== null && c !== '');

  const key = `node-${keyCounter++}`;

  if (!(ALLOWED_TAGS as readonly string[]).includes(tag)) {
    return { type: 'span', props: { key, children } };
  }

  const props: Record<string, unknown> = { key };
  if (tag === 'a') {
    props['href'] = el.getAttribute('href') ?? '#';
    props['target'] = '_blank';
    props['rel'] = 'noopener noreferrer';
  }

  return { type: tag, props: { ...props, children: children.length === 1 ? children[0] : children } };
}

function renderElement(node: ParseResult): ReactNode {
  if (node === null) return null;
  if (typeof node === 'string') return node;

  const { type, props } = node;
  const { children, ...rest } = props;

  const renderedChildren: ReactNode = Array.isArray(children)
    ? children.map(c => renderElement(c as ParseResult))
    : renderElement(children as ParseResult);

  return createElement(type, rest as Record<string, unknown>, renderedChildren);
}

interface HtmlContentProps {
  html: string;
  className?: string;
  testId?: string;
}

export function HtmlContent({ html, className, testId }: HtmlContentProps): ReactElement | null {
  if (!html) return null;

  keyCounter = 0;
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, 'text/html');

  const elements = Array.from(doc.body.childNodes)
    .map(nodeToReact)
    .filter((n): n is string | ParsedElement => n !== null)
    .map(n => renderElement(n));

  return createElement('div', { className, 'data-testid': testId }, ...elements);
}
