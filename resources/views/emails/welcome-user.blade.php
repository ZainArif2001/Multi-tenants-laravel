<!DOCTYPE html>
<html>
<body style="font-family: -apple-system, 'Segoe UI', sans-serif; background: #f4f4f5; margin: 0; padding: 32px;">
    <div style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.08);">
        <div style="background: #4f46e5; padding: 24px 32px;">
            <h1 style="color: #fff; margin: 0; font-size: 20px;">{{ $tenantName }}</h1>
        </div>
        <div style="padding: 32px;">
            <h2 style="margin: 0 0 8px; font-size: 18px; color: #18181b;">Welcome aboard, {{ $user->name }}!</h2>
            <p style="color: #52525b; font-size: 14px; line-height: 1.6;">
                An account has been created for you on <strong>{{ $tenantName }}</strong>. Here are your sign-in details:
            </p>
            <div style="background: #f4f4f5; border-radius: 10px; padding: 16px 20px; margin: 20px 0; font-size: 14px;">
                <div><strong>Email:</strong> {{ $user->email }}</div>
                <div style="margin-top: 6px;"><strong>Password:</strong> {{ $password }}</div>
            </div>
            <a href="{{ $loginUrl }}" style="display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 10px; font-size: 14px; font-weight: 600;">
                Sign in to your account
            </a>
            <p style="color: #a1a1aa; font-size: 12px; margin-top: 28px;">
                For security, please change your password after your first sign-in.
            </p>
        </div>
    </div>
</body>
</html>
