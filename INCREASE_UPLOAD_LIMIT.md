# How to Increase PHP Upload Limit

## Current Limits
- **upload_max_filesize:** 2M
- **post_max_size:** 8M

## Issue
The current PHP configuration only allows 2MB file uploads, but you may need to upload larger PDF files for class routines.

---

## Solution: Increase Upload Limits

### For XAMPP/LAMPP

**1. Edit php.ini file:**
```bash
sudo nano /opt/lampp/etc/php.ini
```

**2. Find and update these lines:**
```ini
; Change from:
upload_max_filesize = 2M
post_max_size = 8M
max_execution_time = 30

; Change to:
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
```

**3. Save and restart Apache:**
```bash
sudo /opt/lampp/lampp restart
```

---

### For Nginx + PHP-FPM

**1. Edit php.ini:**
```bash
sudo nano /etc/php/8.1/fpm/php.ini
```

**2. Update the same settings above**

**3. Edit nginx.conf:**
```bash
sudo nano /etc/nginx/nginx.conf
```

Add this inside `http` block:
```nginx
client_max_body_size 20M;
```

**4. Restart services:**
```bash
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

---

### For Apache + PHP Module

**1. Edit php.ini:**
```bash
sudo nano /etc/php/8.1/apache2/php.ini
```

**2. Update the same settings above**

**3. Restart Apache:**
```bash
sudo systemctl restart apache2
```

---

## Verify Changes

**1. Create a test file:**
```bash
echo "<?php phpinfo(); ?>" > /opt/lampp/htdocs/info.php
```

**2. Visit:**
```
http://localhost/info.php
```

**3. Search for:**
- `upload_max_filesize` (should be 20M)
- `post_max_size` (should be 25M)

**4. Delete the test file:**
```bash
rm /opt/lampp/htdocs/info.php
```

---

## Alternative: Keep 2MB Limit

If you don't want to change PHP settings, you can keep the 2MB limit. Just ensure your PDF files are:
- Compressed
- Not scanned images (use text-based PDFs)
- Optimized using tools like Adobe Acrobat or online PDF compressors

---

## Update Controller After Increasing Limit

If you increase PHP upload limit to 20MB, update the controller:

**File:** `app/Http/Controllers/Backend/ClassRoutineController.php`

**Change:**
```php
// From:
'pdf_file' => ($routine ? 'nullable' : 'required') . '|file|mimes:pdf|max:2048',

// To:
'pdf_file' => ($routine ? 'nullable' : 'required') . '|file|mimes:pdf|max:20480',
```

**And update the view:** `resources/views/backend/class-routine/create-edit.blade.php`

**Change:**
```html
<!-- From: -->
<small class="text-muted">Upload PDF file (Max: 2MB)</small>

<!-- To: -->
<small class="text-muted">Upload PDF file (Max: 20MB)</small>
```

---

## Recommended Settings

For production:
```ini
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
memory_limit = 256M
```

This allows comfortable upload of class routine PDFs while preventing abuse.

