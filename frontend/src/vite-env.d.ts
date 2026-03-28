/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_URL: string
  readonly VITE.VK_CLIENT_ID: string
  readonly VITE.VK_REDIRECT_URI: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
