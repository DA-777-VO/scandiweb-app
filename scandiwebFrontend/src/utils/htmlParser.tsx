import { createElement, ReactElement, ReactNode } from 'react';

interface ParsedElement {
  type: string;
  props: Record<string, unknown>;
}

type ParsedChild = string | ParsedElement;
type ParseResult = ParsedChild | null;

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
  const parsedChildren = Array.from(el.childNodes)
    .map(nodeToReact)
    .filter((c): c is ParsedChild => c !== null && c !== '');

  const key = `node-${keyCounter++}`;
  const props: Record<string, unknown> = { key };

  if (!(ALLOWED_TAGS as readonly string[]).includes(tag)) {
    props.children = parsedChildren.length === 1 ? parsedChildren[0] : parsedChildren;
    return { type: 'span', props };
  }

  if (tag === 'a') {
    props.href = el.getAttribute('href') ?? '#';
    props.target = '_blank';
    props.rel = 'noopener noreferrer';
  }

  props.children = parsedChildren.length === 1 ? parsedChildren[0] : parsedChildren;
  return { type: tag, props };
}

function renderElement(node: ParseResult): ReactNode {
  if (node === null) return null;
  if (typeof node === 'string') return node;

  const { type, props } = node;
  const { children, ...rest } = props;

  let renderedChildren: ReactNode;
  if (Array.isArray(children)) {
    renderedChildren = children.map((c) => renderElement(c as ParseResult));
  } else if (children !== undefined) {
    renderedChildren = renderElement(children as ParseResult);
  } else {
    renderedChildren = null;
  }

  return createElement(type, rest, renderedChildren);
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
    .filter((n): n is ParsedChild => n !== null)
    .map(n => renderElement(n));

  return createElement('div', { className, 'data-testid': testId }, ...elements);
}
