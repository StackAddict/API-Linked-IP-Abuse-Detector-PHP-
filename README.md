What it does

Resolves the real client IP using common server headers (HTTP_X_FORWARDED_FOR, HTTP_CLIENT_IP, REMOTE_ADDR, etc.).

Validates the IP format.

Calls the AbuseIPDB check API to get an abuse confidence score.

If the score is above a threshold (example in this script: > 60), it redirects the request (you can replace that with your ban/honeypot logic).

Caches the last check in $_SESSION to avoid repeated API calls during the same session.

Requirements

PHP with curl extension enabled.

PHP session_start() invoked before including the script (or the script can be wrapped to call it).

AbuseIPDB API key (set from environment or other secure storage — do not hardcode into public repos).

Installation

Place the PHP file in your project (e.g. ipshield.php).

Add session_start(); at the top of your application (before output).

Set your AbuseIPDB API key (recommended via environment variable or server config).

Include the file and call the check early in your request lifecycle.
