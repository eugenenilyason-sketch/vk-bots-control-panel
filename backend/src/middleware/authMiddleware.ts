import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import { config } from '../config/index';
import { AppError } from './errorHandler';

export interface AuthRequest extends Request {
  user?: {
    id: string;
    vkId?: bigint;
    role: string;
  };
}

export const authMiddleware = async (
  req: AuthRequest,
  res: Response,
  next: NextFunction
) => {
  try {
    let token: string | undefined;
    
    // Пробуем получить токен из заголовка Authorization
    const authHeader = req.headers.authorization;
    if (authHeader && authHeader.startsWith('Bearer ')) {
      token = authHeader.split(' ')[1];
    }
    // Или из cookie
    else if (req.cookies && req.cookies.access_token) {
      token = req.cookies.access_token;
    }
    
    if (!token) {
      throw new AppError('Unauthorized', 401);
    }
    
    const decoded = jwt.verify(token, config.JWT_SECRET) as {
      id?: string;
      userId?: string;
      vkId?: string;
      role?: string;
    };

    console.log('🔑 Decoded JWT:', decoded);
    console.log('🔑 Setting user:', {
      id: decoded.userId || decoded.id || '',
      vkId: decoded.vkId ? BigInt(decoded.vkId) : undefined,
      role: decoded.role || 'user',
    });

    req.user = {
      id: decoded.userId || decoded.id || '',
      vkId: decoded.vkId ? BigInt(decoded.vkId) : undefined,
      role: decoded.role || 'user',
    };

    console.log('✅ User set:', req.user);

    next();
  } catch (error) {
    if (error instanceof jwt.JsonWebTokenError || error instanceof jwt.TokenExpiredError) {
      next(new AppError('Invalid or expired token', 401));
    } else {
      next(error);
    }
  }
};

export const adminMiddleware = async (
  req: AuthRequest,
  res: Response,
  next: NextFunction
) => {
  if (!req.user) {
    return next(new AppError('Unauthorized', 401));
  }

  if (!['admin', 'superadmin'].includes(req.user.role)) {
    return next(new AppError('Forbidden: Admin access required', 403));
  }

  next();
};

export const superAdminMiddleware = async (
  req: AuthRequest,
  res: Response,
  next: NextFunction
) => {
  if (!req.user) {
    return next(new AppError('Unauthorized', 401));
  }

  if (req.user.role !== 'superadmin') {
    return next(new AppError('Forbidden: Superadmin access required', 403));
  }

  next();
};
