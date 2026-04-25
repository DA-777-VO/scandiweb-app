/// <reference types="vite/client" />

// CSS Modules — tells TypeScript that *.module.css files export a string map
declare module '*.module.css' {
  const classes: Record<string, string>
  export default classes
}

interface ImportMetaEnv {
  readonly VITE_API_URL?: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
