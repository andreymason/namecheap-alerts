**README**

This PHP script is designed to fetch domain information from Namecheap and send notifications via Telegram for domains that are either blocked or about to expire within 3 days.

### Usage
1. Replace placeholders in the code with actual values:
   - `api_token`: Your Namecheap API token.
   - `username`: Your Namecheap username.
   - `client_ip`: Your server's IP address.
   - `telegram_token`: Your Telegram bot token.
   - `telegram_chat_id`: Your Telegram chat ID.

2. Run the script by executing it manually or setting it up as a cron job.

### Description
The script works as follows:
1. It queries the Namecheap API to retrieve a list of domains associated with the provided Namecheap account.
2. For each domain, it checks if the domain is locked or if its expiration date is within 3 days.
3. If a domain is locked or its expiration date is within 3 days, a notification message is sent via Telegram.
4. The script also keeps track of blocked domains by storing them in a file named `blocked.txt`, to prevent duplicate notifications.
5. After processing all domains, the script sends a single message to Telegram containing information about all domains that require notification.

### Setting up a cron job
To automate the script execution, set up a cron job to run the script periodically. For example, to run the script once a day, add the following line to your crontab:

```
0 0 * * * /usr/bin/php /path/to/your/script.php >/dev/null 2>&1
```

Replace `/usr/bin/php` with the path to your PHP binary, and `/path/to/your/script.php` with the actual path to your script. This cron job will execute the script every day at midnight. Adjust the timing as needed.