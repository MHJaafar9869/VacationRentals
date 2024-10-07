<!DOCTYPE html>
<html>
<head>
    <title>{{ $details['mail_greeting'] }}</title>
</head>
<body>
    <h1>{{ $details['mail_greeting'] }}</h1>
    <p>{{ $details['mail_body'] }}</p>
    <a href="{{ $details['mail_action_url'] }}">{{ $details['mail_action_text'] }}</a>
    <p>{{ $details['mail_end_line'] }}</p>
</body>
</html>
