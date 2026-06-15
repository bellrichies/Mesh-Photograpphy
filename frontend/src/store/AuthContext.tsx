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

// Non-sensitive flag — tells the client a session cookie may exist.
// Never stored: tokens, user data, or anything security-relevant.
const SESSION_HINT_KEY = 'mesh_has_session';

const setSessionHint = (value: boolean) => {
  if (value) localStorage.setItem(SESSION_HINT_KEY, '1');
  else localStorage.removeItem(SESSION_HINT_KEY);
};

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AdminUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const clearAuth = useCallback(() => {
    setSessionHint(false);
    setAccessToken(null);
    setUser(null);
  }, []);

  // On mount: only attempt silent refresh if a session hint exists.
  // This prevents a guaranteed 401 on every public page load for non-admin visitors.
  useEffect(() => {
    setOnRefreshFailed(clearAuth);

    if (!localStorage.getItem(SESSION_HINT_KEY)) {
      setIsLoading(false);
      return;
    }

    authApi
      .refresh()
      .then((res) => {
        setAccessToken(res.data.access_token);
        setUser(res.data.user);
      })
      .catch(() => {
        // Refresh cookie expired or invalid — clear the hint so we don't retry next load.
        clearAuth();
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, [clearAuth]);

  const login = async (email: string, password: string): Promise<void> => {
    const res = await authApi.login({ email, password });
    setSessionHint(true);
    setAccessToken(res.data.access_token);
    setUser(res.data.user);
  };

  const logout = async (): Promise<void> => {
    try {
      await authApi.logout();
    } catch {
      // Proceed with local cleanup even if server call fails
    }
    clearAuth();
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
