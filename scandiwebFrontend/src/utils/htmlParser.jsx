/**
 * Parse HTML string into React elements without dangerouslySetInnerHTML.
 * Supports: p, h1-h6, ul, ol, li, span, div, strong, em, br
 */
export function parseHtml(htmlString) {
  if (!htmlString) return null;

  const parser = new DOMParser();
  const doc = parser.parseFromString(htmlString, 'text/html');

  let keyCounter = 0;
  const nextKey = () => `node-${keyCounter++}`;

  function nodeToReact(node) {
    if (node.nodeType === Node.TEXT_NODE) {
      return node.textContent;
    }

    if (node.nodeType !== Node.ELEMENT_NODE) return null;

    const tag = node.tagName.toLowerCase();
    const children = Array.from(node.childNodes).map(nodeToReact).filter(Boolean);
    const key = nextKey();

    const allowedTags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
      'ul', 'ol', 'li', 'span', 'div', 'strong', 'em', 'br', 'a'];

    if (!allowedTags.includes(tag)) {
      return children.length ? children : null;
    }

    const props = { key };
    if (tag === 'a') {
      props.href = node.getAttribute('href') || '#';
      props.target = '_blank';
      props.rel = 'noopener noreferrer';
    }

    return { type: tag, props: { ...props, children: children.length === 1 ? children[0] : children } };
  }

  const elements = Array.from(doc.body.childNodes).map(nodeToReact).filter(Boolean);
  return elements;
}

import React from 'react';

export function HtmlContent({ html, className, testId }) {
  const elements = parseHtml(html);
  if (!elements) return null;

  return React.createElement('div', { className, 'data-testid': testId },
    ...elements.map(el => {
      if (typeof el === 'string') return el;
      return renderElement(el);
    })
  );
}

function renderElement(el) {
  if (!el || typeof el === 'string') return el;
  const { type, props } = el;
  const { children, ...rest } = props;

  const renderedChildren = Array.isArray(children)
    ? children.map(c => renderElement(c))
    : renderElement(children);

  return React.createElement(type, rest, renderedChildren);
}
