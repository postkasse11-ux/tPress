# tPress

A simple, fast, and secure flat-file blog engine written in pure PHP. No database, no Composer, no third-party dependencies. Just one file (`index.php`) and a handful of text files.

## Features

- ✍️ **Built-in Markdown:** Custom, lightweight parser (no external libraries).
- 🌍 **Multilingual UI:** Choose between Norwegian (Norsk) and English during installation.
- 🏷️ **Tags & Search:** Organize and find posts easily.
- 📷 **Media Uploads:** Upload images, audio, and video directly within posts.
- 📺 **Embeds:** Native support for YouTube and Vimeo.
- 📊 **View Counter:** Tracks unique views (excludes logged-in admins and known bots).
- 💾 **Drafts:** Save posts as drafts and continue editing later.
- 🌙 **Dark Mode:** Automatic support based on system preferences.
- 🔒 **Secure Authentication:** Passwords are hashed using PHP's native `password_hash` (bcrypt).
- 🛡️ **CSRF Protection:** All forms are protected against Cross-Site Request Forgery.
- 📱 **Responsive Design:** Looks great on mobile and desktop.

## Requirements

- PHP 8.0 or higher
- PHP extensions: `mbstring` and `fileinfo` (enabled by default on most hosts)
- Write permissions in the directory where `index.php` is located

## Installation

1. Upload `index.php` to a directory on your web server (e.g., `/public_html/blog/`).
2. Open the directory in your web browser (e.g., `https://yoursite.com/blog/`).
3. You will be greeted by a first-time setup wizard. Enter:
   - Your desired site title.
   - Your preferred language (Norwegian or English).
   - An admin password (minimum 8 characters).
4. Click **Complete setup**.

The script will automatically generate a `config.php` file containing your site title, language preference, and a securely hashed version of your password. Your blog is now ready to use.

> **Note:** If the server directory is read-only, the wizard will safely fail and provide you with the exact PHP code to manually create the `config.php` file.

## File Structure

```text
blog/
├── index.php          ← The core application (the only file needed on GitHub)
├── config.php         ← Auto-generated during setup (DO NOT commit to Git)
├── posts/             ← Auto-generated (stores your .txt post files)
├── uploads/           ← Auto-generated (stores images, audio, video)
├── views/             ← Auto-generated (stores view count files)
└── php-error.log      ← Auto-generated (logs errors in production)
