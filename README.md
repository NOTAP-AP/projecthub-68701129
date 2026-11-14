# projecthub

## Project setup
```
npm install
```

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```

### Customize configuration
See [Configuration Reference](https://cli.vuejs.org/config/).

## PHP API and database

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open phpMyAdmin and import `php_api/Project_hub.sql`. The script creates the `project_hub` database and its HR tables.
3. Start the Vue app with `npm run serve`, then open the displayed local URL.
4. Demo administrator: username `admin`, password `admin123`. Change or remove this account before deploying publicly.

The PHP API is available under `http://localhost/projecthub/php_api/`:

- `POST register.php` — register a staff account with `first_name`, `last_name`, `email`, `username`, and `password` (minimum 8 characters).
- `POST login.php` and `POST logout.php` — start or end the PHP session.
- `GET/POST/PUT/DELETE employee.php` — employee CRUD; pass `?id=...` for one employee, updates, or deletes.
- `GET departments.php` — get departments and positions for form selectors.
- `GET dashboard.php` — get HR summary counts.
- `GET/POST attendance.php` — list attendance or send `{"action":"check_in","employee_id":1}` / `{"action":"check_out","employee_id":1}`.
- `GET/POST/PUT leave_requests.php` — list/submit leave requests or approve/reject with `?id=...` and `{"status":"Approved"}`.

The database defaults in `php_api/condb.php` match a standard local XAMPP install (MySQL user `root`, empty password). They can be overridden with `PROJECTHUB_DB_HOST`, `PROJECTHUB_DB_USER`, `PROJECTHUB_DB_PASSWORD`, and `PROJECTHUB_DB_NAME` environment variables.
