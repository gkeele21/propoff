<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PropOff</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .magic-link-box {
            background: white;
            border: 2px dashed #3B82F6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }
        .magic-link {
            display: inline-block;
            background: #3B82F6;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 15px 0;
        }
        .magic-link:hover {
            background: #2563EB;
        }
        .footer {
            background: #f3f4f6;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-radius: 0 0 8px 8px;
        }
        .warning {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">Welcome to PropOff!</h1>
        <p style="margin: 10px 0 0 0;">You're now a Captain</p>
    </div>

    <div class="content">
        <p>Hi {{ $user->name }},</p>

        <p>Congratulations! You've successfully created <strong>{{ $group->name }}</strong> and you're now a captain!</p>

        <div class="magic-link-box">
            <h2 style="margin-top: 0; color: #3B82F6;">🔑 Your Personal Access Link</h2>
            <p>Since you don't have a password, use this magic link to return to your account anytime:</p>
            <a href="{{ $magicLink }}" class="magic-link">Access Your Account</a>
            <p style="font-size: 12px; color: #6b7280; margin-top: 15px;">
                Or copy this link:<br>
                <code style="background: #f3f4f6; padding: 5px 10px; border-radius: 4px; word-break: break-all;">{{ $magicLink }}</code>
            </p>
        </div>

        <div class="warning">
            <strong>⚠️ Important:</strong> Save this email or bookmark the link above. You'll need it to access your account in the future!
        </div>

        <p><strong>What's next?</strong></p>
        <ul>
            <li>Customize your group's questions</li>
            <li>Invite members to join your group</li>
            <li>Set correct answers (if using captain grading)</li>
            <li>View your group's leaderboard</li>
        </ul>

        <p>If you have any questions, just reply to this email.</p>

        <p>Good luck!<br>
        The PropOff Team</p>
    </div>

    <div class="footer">
        <p>This email was sent because you created a group on PropOff.<br>
        PropOff - Event Prediction Platform</p>
    </div>
</body>
</html>
