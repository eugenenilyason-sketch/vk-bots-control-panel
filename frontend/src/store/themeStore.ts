import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import React from 'react';

interface ThemeState {
  mode: 'light' | 'dark';
  toggleTheme: () => void;
  setMode: (mode: 'light' | 'dark') => void;
}

export const useThemeStore = create<ThemeState>()(
  persist(
    (set) => ({
      mode: 'light',
      toggleTheme: () =>
        set((state) => {
          const newMode = state.mode === 'light' ? 'dark' : 'light';
          document.body.className = `${newMode}-theme`;
          return { mode: newMode };
        }),
      setMode: (mode) => {
        document.body.className = `${mode}-theme`;
        set({ mode });
      },
    }),
    {
      name: 'theme-storage',
    }
  )
);

export const ThemeProvider = ({ children }: { children: React.ReactNode }) => {
  const { mode } = useThemeStore();
  
  React.useEffect(() => {
    document.body.className = `${mode}-theme`;
  }, [mode]);

  return <>{children}</>;
};
