import { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { authApi } from '@/api/auth';
import { setAccessToken, setOnRefreshFailed } from '@/api/client';
import type { AdminUser } from '@/types/models';

interface AuthState {
  user: AdminUser | null;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AdminUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const handleAuthLost = useCallback(() => {
    setAccessToken(null);
    setUser(null);
  }, []);

  // On mount: attempt silent refresh from httpOnly cookie
  useEffect(() => {
    setOnRefreshFailed(handleAuthLost);

    authApi
      .refresh()
      .then((res) => {
        setAccessToken(res.data.access_token);
        setUser(res.data.user);
      })
      .catch(() => {
        setAccessToken(null);
        setUser(null);
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, [handleAuthLost]);

  const login = async (email: string, password: string): Promise<void> => {
    const res = await authApi.login({ email, password });
    setAccessToken(res.data.access_token);
    setUser(res.data.user);
  };

  const logout = async (): Promise<void> => {
    try {
      await authApi.logout();
    } catch {
      // Proceed with local cleanup even if server call fails
    }
    setAccessToken(null);
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, isLoading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthState {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return ctx;
}
